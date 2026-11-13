#!/usr/bin/env python3
"""Crawl same-origin HTML navigation and audit header links.

Usage: python3 tools/live-navigation-audit.py [base_url]
The script is intentionally read-only and does not submit forms or call mutation APIs.
"""
from __future__ import annotations

import collections
import html
import re
import sys
import urllib.error
import urllib.parse
import urllib.request
from html.parser import HTMLParser

BASE = (sys.argv[1] if len(sys.argv) > 1 else "https://adopt.petcenterco.com").rstrip("/")
BASE_PARTS = urllib.parse.urlsplit(BASE)
HOST = BASE_PARTS.netloc

SEEDS = ["/", "/rehoming/", "/rehoming/list-a-pet/", "/merchant/", "/merchant/post-listing/", "/signin/"]
SKIP_PREFIXES = ("/admin/", "/api/", "/assets/", "/rehoming/merchant-", "/merchant-publish-listing.php")
SKIP_EXTENSIONS = (".css", ".js", ".json", ".xml", ".txt", ".ico", ".png", ".jpg", ".jpeg", ".webp", ".gif", ".svg", ".pdf", ".zip")

class LinkParser(HTMLParser):
    def __init__(self) -> None:
        super().__init__(convert_charrefs=True)
        self.links: list[str] = []
        self.header_links: list[str] = []
        self.in_header = False
        self.title_parts: list[str] = []
        self.in_title = False
        self.header_depth = 0

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if tag.lower() == "header":
            self.in_header = True
            self.header_depth = 1
        elif self.in_header and tag.lower() not in ("a", "script"):
            self.header_depth += 1
        if tag.lower() == "title":
            self.in_title = True
        if tag.lower() == "a" and attrs.get("href"):
            href = html.unescape(attrs["href"].strip())
            self.links.append(href)
            if self.in_header:
                self.header_links.append(href)

    def handle_endtag(self, tag):
        if tag.lower() == "title":
            self.in_title = False
        if self.in_header and tag.lower() != "a":
            self.header_depth -= 1
            if self.header_depth <= 0:
                self.in_header = False
                self.header_depth = 0

    def handle_data(self, data):
        if self.in_title:
            self.title_parts.append(data)

def canonical(raw: str, page_url: str) -> str | None:
    if not raw or raw.startswith(("#", "mailto:", "tel:", "javascript:", "data:")):
        return None
    absolute = urllib.parse.urljoin(page_url, raw)
    parts = urllib.parse.urlsplit(absolute)
    if parts.scheme not in ("http", "https") or parts.netloc != HOST:
        return None
    path = parts.path or "/"
    if not path.startswith("/"):
        path = "/" + path
    if path != "/" and not path.endswith("/") and "." not in path.rsplit("/", 1)[-1]:
        path += "/"
    return urllib.parse.urlunsplit((BASE_PARTS.scheme, HOST, path, parts.query, ""))

def fetch(url: str):
    req = urllib.request.Request(url, headers={"User-Agent": "PetCenter-live-navigation-audit/1.0"})
    try:
        with urllib.request.urlopen(req, timeout=20) as response:
            body = response.read(2_000_000)
            return response.status, response.geturl(), response.headers.get_content_type(), body, ""
    except urllib.error.HTTPError as exc:
        body = exc.read(200_000)
        return exc.code, exc.geturl(), exc.headers.get_content_type(), body, str(exc)
    except Exception as exc:
        return 0, url, "", b"", repr(exc)

def should_crawl(path: str) -> bool:
    lower = path.lower()
    return not lower.endswith(SKIP_EXTENSIONS) and not any(lower.startswith(p) for p in SKIP_PREFIXES)

def main() -> int:
    queue = collections.deque(canonical(p, BASE) for p in SEEDS)
    queue = collections.deque(x for x in queue if x)
    seen: set[str] = set()
    results: dict[str, tuple[int, str, str, str]] = {}
    discovered_from: dict[str, str] = {}
    header_targets: dict[str, set[str]] = collections.defaultdict(set)
    pages_without_title: list[str] = []
    max_pages = 500

    while queue and len(seen) < max_pages:
        url = queue.popleft()
        if url in seen:
            continue
        seen.add(url)
        status, final, content_type, body, error = fetch(url)
        title = ""
        parser = LinkParser()
        if "html" in content_type and body:
            try:
                parser.feed(body.decode("utf-8", errors="replace"))
                title = re.sub(r"\s+", " ", "".join(parser.title_parts)).strip()
            except Exception as exc:
                error = f"parse error: {exc}"
            if not title:
                pages_without_title.append(url)
            for raw in parser.links:
                target = canonical(raw, final)
                if not target:
                    continue
                discovered_from.setdefault(target, url)
                if target not in seen and should_crawl(urllib.parse.urlsplit(target).path):
                    queue.append(target)
            for raw in parser.header_links:
                target = canonical(raw, final)
                if target:
                    header_targets[url].add(target)
        results[url] = (status, final, content_type, title or error)

    broken = []
    redirects = []
    non_html = []
    for url, (status, final, content_type, label) in results.items():
        if status >= 400 or status == 0:
            broken.append((url, status, label, discovered_from.get(url, "seed")))
        if final.rstrip("/") != url.rstrip("/"):
            redirects.append((url, status, final))
        if status == 200 and content_type and "html" not in content_type and should_crawl(urllib.parse.urlsplit(url).path):
            non_html.append((url, status, content_type))

    header_failures = []
    for page, targets in sorted(header_targets.items()):
        for target in sorted(targets):
            if target not in results:
                status, final, content_type, body, error = fetch(target)
                results[target] = (status, final, content_type, error)
            status, final, content_type, label = results[target]
            if status >= 400 or status == 0:
                header_failures.append((page, target, status, label))

    print(f"BASE {BASE}")
    print(f"PAGES_CRAWLED {len(seen)}")
    print(f"LINK_TARGETS_TESTED {len(results)}")
    print(f"BROKEN_LINKS {len(broken)}")
    for url, status, label, source in broken:
        print(f"BROKEN\t{status}\t{url}\tfrom={source}\t{label[:160]}")
    print(f"HEADER_LINK_FAILURES {len(header_failures)}")
    for page, target, status, label in header_failures:
        print(f"HEADER_BROKEN\t{status}\t{target}\tpage={page}\t{label[:160]}")
    print(f"REDIRECTS {len(redirects)}")
    for url, status, final in redirects:
        print(f"REDIRECT\t{status}\t{url}\tto={final}")
    print(f"PAGES_WITHOUT_TITLE {len(pages_without_title)}")
    for url in pages_without_title:
        print(f"NO_TITLE\t{url}")
    print(f"NON_HTML_NAV_TARGETS {len(non_html)}")
    for url, status, content_type in non_html:
        print(f"NON_HTML\t{status}\t{content_type}\t{url}")
    return 1 if broken or header_failures else 0

if __name__ == "__main__":
    raise SystemExit(main())
