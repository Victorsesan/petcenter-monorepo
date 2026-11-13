#!/usr/bin/env python3
"""Browser smoke test for the visitor-profile loading race; all API calls are mocked."""
from __future__ import annotations

import json
import threading
import time
from http.server import SimpleHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path
from urllib.parse import parse_qs, urlparse

from playwright.sync_api import sync_playwright

REPO = Path(__file__).resolve().parents[2]
WEB_ROOT = REPO / "adopt"
profile_get_count = 0
saved_payload: dict | None = None


class QuietHandler(SimpleHTTPRequestHandler):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, directory=str(WEB_ROOT), **kwargs)

    def log_message(self, *_args):
        pass


def fulfill_json(route, status: int, payload: dict):
    route.fulfill(status=status, content_type="application/json", body=json.dumps(payload))


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

        def mock_auth_api(route):
            global profile_get_count, saved_payload
            request = route.request
            parsed = urlparse(request.url)
            action = parse_qs(parsed.query).get("action", [""])[0]
            if action == "csrf" and request.method == "GET":
                fulfill_json(route, 200, {"success": True, "csrf_token": "a" * 64})
            elif action == "status" and request.method == "GET":
                fulfill_json(route, 200, {"success": True, "auth_configured": True, "google_configured": False, "authenticated": True, "user": {"account_type": "visitor"}})
            elif action == "profile" and request.method == "GET":
                profile_get_count += 1
                if profile_get_count == 1:
                    time.sleep(0.35)
                    fulfill_json(route, 503, {"success": False, "message": "Synthetic profile-load failure"})
                else:
                    fulfill_json(route, 200, {"success": True, "profile": {
                        "householdType": "apartment",
                        "experienceLevel": "some",
                        "otherPets": "yes",
                        "otherPetsSpecies": "one cat",
                        "kidsInHome": "no",
                        "whyThisPet": "Synthetic saved reason"
                    }})
            elif action == "profile" and request.method == "POST":
                saved_payload = request.post_data_json
                fulfill_json(route, 200, {"success": True, "profile": saved_payload, "message": "Saved"})
            else:
                fulfill_json(route, 404, {"success": False, "message": "Unexpected mocked request"})

        page.route("**/rehoming/auth.php*", mock_auth_api)
        page.route("https://**/*", lambda route: route.abort())
        page.goto(base + "/account/settings/index.html", wait_until="domcontentloaded")

        save_button = page.locator('#adopterProfileForm button[type="submit"]')
        trigger = page.locator('[data-dropdown] .dropdown-trigger').first
        assert save_button.is_disabled(), "Save must stay disabled while the profile is loading"
        assert page.locator("#whyThisPet").is_disabled(), "Text fields must stay disabled while loading"
        assert trigger.get_attribute("aria-disabled") == "true", "Custom dropdown must start disabled"
        trigger.click(force=True)
        assert trigger.get_attribute("aria-expanded") == "false", "Disabled dropdown must ignore pointer activation"

        page.locator("#profileError").wait_for(state="visible", timeout=5000)
        assert save_button.is_disabled(), "Failed profile load must not enable Save"
        assert page.locator("#retryProfileLoad").is_visible(), "Failed load must expose retry"
        page.locator("#retryProfileLoad").click()

        save_button.wait_for(state="visible", timeout=5000)
        page.wait_for_function("document.querySelector('#adopterProfileForm button[type=submit]').disabled === false")
        household = page.locator('[data-dropdown][data-field-name="householdType"]')
        assert household.get_attribute("data-value") == "apartment", "Saved custom value must load"
        assert household.locator(".dropdown-trigger").get_attribute("aria-disabled") == "false"
        assert page.locator("#otherPetsSpecies").input_value() == "one cat"
        assert page.locator("#otherPetsSpeciesField").is_visible(), "Conditional species field must reflect loaded profile"
        assert page.locator("#whyThisPet").input_value() == "Synthetic saved reason"

        trigger = household.locator(".dropdown-trigger")
        trigger.focus()
        trigger.press("Enter")
        page.wait_for_function("document.querySelector('[data-field-name=householdType] .dropdown-panel').classList.contains('open')")
        page.wait_for_function("document.activeElement && document.activeElement.id === 'profile-householdType-listbox-option-0'")
        page.keyboard.press("ArrowDown")
        page.keyboard.press("Enter")
        assert household.get_attribute("data-value") == "house_yard", "Dropdown selection must update the active form value"
        save_button.click()
        page.wait_for_function("document.querySelector('#saveToast').classList.contains('show')", timeout=5000)
        assert saved_payload is not None and saved_payload.get("householdType") == "house_yard", "Save payload mismatch: " + repr(saved_payload)
        assert saved_payload.get("otherPetsSpecies") == "one cat"
        assert not page_errors, "Page should not raise JavaScript errors: " + repr(page_errors)
        print("PASS: profile form remains locked through failed load, retries, populates saved data, and submits the edited payload")
        browser.close()
finally:
    server.shutdown()
    server.server_close()
    thread.join(timeout=2)
