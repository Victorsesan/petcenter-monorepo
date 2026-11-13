#!/usr/bin/env python3
"""Browser smoke test for WEB-04 sign-in/account-creation UI; all auth APIs are mocked."""
from __future__ import annotations

import json
import threading
from http.server import SimpleHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path
from urllib.parse import parse_qs, urlparse

from playwright.sync_api import sync_playwright

REPO = Path(__file__).resolve().parents[2]
WEB_ROOT = REPO / "adopt"
state = {"auth_configured": True, "google_configured": False, "test_auth_enabled": False, "api_error": False}
oauth_requests: list[str] = []


class QuietHandler(SimpleHTTPRequestHandler):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, directory=str(WEB_ROOT), **kwargs)

    def log_message(self, *_args):
        pass


def fulfill_json(route, status: int, payload: dict):
    route.fulfill(status=status, content_type="application/json", body=json.dumps(payload))


def mock_auth_api(route):
    request = route.request
    parsed = urlparse(request.url)
    action = parse_qs(parsed.query).get("action", [""])[0]
    if action == "status" and request.method == "GET":
        if state["api_error"]:
            fulfill_json(route, 503, {"success": False, "message": "Synthetic unavailable"})
        else:
            fulfill_json(route, 200, {
                "success": True,
                "auth_configured": state["auth_configured"],
                "google_configured": state["google_configured"],
                "test_auth_enabled": state["test_auth_enabled"],
                "authenticated": False,
                "user": None,
            })
    else:
        fulfill_json(route, 404, {"success": False, "message": "Unexpected mocked request"})


def mock_oauth_start(route):
    oauth_requests.append(route.request.url)
    route.fulfill(status=200, content_type="text/html", body="Synthetic OAuth navigation intercepted")


server = ThreadingHTTPServer(("127.0.0.1", 0), QuietHandler)
thread = threading.Thread(target=server.serve_forever, daemon=True)
thread.start()
base = f"http://127.0.0.1:{server.server_port}"

try:
    with sync_playwright() as playwright:
        browser = playwright.chromium.launch(headless=True, executable_path="/usr/bin/chromium", args=["--no-sandbox"])
        page = browser.new_page(viewport={"width": 390, "height": 844})
        page_errors: list[str] = []
        page.on("pageerror", lambda error: page_errors.append(str(error)))
        page.route("**/rehoming/auth.php*", mock_auth_api)
        page.route("**/rehoming/google-oauth.php*", mock_oauth_start)
        page.route("https://**/*", lambda route: route.abort())

        page.goto(base + "/signin/?mode=signup", wait_until="domcontentloaded")
        page.get_by_text("Google sign-in is not configured for this environment.").wait_for(state="visible", timeout=5000)
        login_tab = page.get_by_role("tab", name="Sign in")
        signup_tab = page.get_by_role("tab", name="Create account")
        assert signup_tab.get_attribute("aria-selected") == "true", "mode=signup must open the account-creation tab"
        assert page.get_by_role("button", name="Create account with Google").is_disabled(), "Google must remain disabled without configuration"
        assert page.get_by_role("link", name="Privacy Policy and account-data notice").get_attribute("href") == "/privacy-policy/"
        assert page.locator(".web04-legal-note").get_by_role("link", name="Terms of Service").get_attribute("href") == "/terms-of-service/"
        assert page.locator("#googleAuthStatus").get_attribute("aria-live") == "polite"
        assert page.locator("#authMessage").get_attribute("role") == "alert"
        assert page.evaluate("document.documentElement.scrollWidth <= window.innerWidth + 1"), "gateway should not overflow the mobile viewport horizontally"

        login_tab.focus()
        page.keyboard.press("ArrowRight")
        assert signup_tab.get_attribute("aria-selected") == "true", "ArrowRight should select Create account"
        page.keyboard.press("Home")
        assert login_tab.get_attribute("aria-selected") == "true", "Home should select Sign in"
        page.keyboard.press("End")
        assert signup_tab.get_attribute("aria-selected") == "true", "End should select Create account"

        page.goto(base + "/signin/?mode=login&oauth_error=not_configured", wait_until="domcontentloaded")
        assert "No sign-in was attempted" in page.locator("#authMessage").inner_text(), "not-configured OAuth error should be explicit and truthful"
        assert page.get_by_role("button", name="Continue with Google").is_disabled()

        state["api_error"] = True
        page.goto(base + "/signin/?mode=login", wait_until="domcontentloaded")
        page.get_by_text("The authentication service is temporarily unavailable.").wait_for(state="visible", timeout=5000)
        assert page.get_by_role("button", name="Continue with Google").is_disabled(), "API failure must not enable an unverified sign-in action"

        state["api_error"] = False
        state["google_configured"] = True
        page.goto(base + "/signin/?mode=signup", wait_until="domcontentloaded")
        create_button = page.get_by_role("button", name="Create account with Google")
        create_button.wait_for(state="visible", timeout=5000)
        page.wait_for_function("document.querySelector('#googleSignupButton').disabled === false")
        create_button.click()
        page.wait_for_url("**/rehoming/google-oauth.php*")
        assert oauth_requests, "Google action must start the server-side OAuth endpoint"
        oauth_query = parse_qs(urlparse(oauth_requests[-1]).query)
        assert oauth_query.get("return_to") == ["/rehoming/"], "Google action should preserve the gateway's safe default return route"
        assert "Signed in" not in page.locator("body").inner_text(), "intercepted OAuth navigation must not fabricate successful authentication"
        assert not page_errors, "gateway should not raise JavaScript errors: " + repr(page_errors)
        print("PASS: signup mode, keyboard tabs, mobile fit, legal/status/error states, and server-side OAuth navigation")
        browser.close()
finally:
    server.shutdown()
    server.server_close()
    thread.join(timeout=2)
