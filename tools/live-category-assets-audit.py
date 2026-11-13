#!/usr/bin/env python3
"""Verify assets referenced by the live Pet Center category pages."""
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
CATEGORY_PATHS = [
    "/pets/dogs/",
    "/pets/cats/",
    "/pets/birds/",
    "/pets/reptiles/",
    "/pets/small-mammals/",
    "/pets/aquariums/",
    "/pets/livestocks/",
]
BASE_HOST = urllib.parse.urlsplit(BASE).netloc
ASSET_ATTRS = {"src", "href", "poster", "data-src", "data-lazy-src"}
CSS_URL_RE = re.compile(r"url\(\s*(['\"]?)(.*?)\1\s*\)", re.I)

class AssetParser(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.refs: list[tuple[str, str]] = []
        self.css_chunks: list[str] = []
        self.in_style = False

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        tag = tag.lower()
        asset_attrs = {"src", "poster", "data-src", "data-lazy-src"}
        if tag == "link":
            asset_attrs.add("href")
        for attr in asset_attrs:
            value = attrs.get(attr)
            if value:
                self.refs.append((f"{tag}[{attr}]", html.unescape(value.strip())))
        srcset = attrs.get("srcset")
        if srcset:
            for item in srcset.split(","):
                value = item.strip().split()[0]
                if value:
                    self.refs.append((f"{tag}[srcset]", html.unescape(value)))
        if tag.lower() == "style":
            self.in_style = True

    def handle_endtag(self, tag):
        if tag.lower() == "style":
            self.in_style = False

    def handle_data(self, data):
        if self.in_style:
            self.css_chunks.append(data)

def fetch(url: str):
    req = urllib.request.Request(url, headers={"User-Agent": "PetCenter-live-category-assets-audit/1.0"})
    try:
        with urllib.request.urlopen(req, timeout=25) as response:
            return response.status, response.geturl(), response.headers.get_content_type(), ""
    except urllib.error.HTTPError as exc:
        return exc.code, exc.geturl(), exc.headers.get_content_type(), str(exc)
    except Exception as exc:
        return 0, url, "", repr(exc)

def resolve(raw: str, page_url: str):
    if not raw or raw.startswith(("#", "data:", "mailto:", "tel:", "javascript:")):
        return None
    absolute = urllib.parse.urljoin(page_url, raw)
    parts = urllib.parse.urlsplit(absolute)
    if parts.scheme not in ("http", "https"):
        return None
    return urllib.parse.urlunsplit((parts.scheme, parts.netloc, parts.path or "/", parts.query, ""))

def main() -> int:
    resources: dict[str, set[str]] = collections.defaultdict(set)
    page_status: list[tuple[str, int, str]] = []
    for path in CATEGORY_PATHS:
        page = BASE + path
        status, final, content_type, error = fetch(page)
        page_status.append((page, status, error))
        if status != 200 or "html" not in content_type:
            continue
        req = urllib.request.Request(page, headers={"User-Agent": "PetCenter-live-category-assets-audit/1.0"})
        try:
            with urllib.request.urlopen(req, timeout=25) as response:
                body = response.read(4_000_000).decode("utf-8", errors="replace")
        except Exception:
            continue
        parser = AssetParser()
        parser.feed(body)
        refs = list(parser.refs)
        for css in parser.css_chunks:
            refs.extend(("style[url]", match.group(2)) for match in CSS_URL_RE.finditer(css))
        for tag, raw in refs:
            target = resolve(raw, final)
            if target:
                resources[target].add(f"{path} {tag}={raw}")

    failures = []
    for target in sorted(resources):
        status, final, content_type, error = fetch(target)
        if status < 200 or status >= 400:
            failures.append((target, status, content_type, error, sorted(resources[target])))

    print(f"BASE {BASE}")
    print(f"CATEGORY_PAGES {len(CATEGORY_PATHS)}")
    print(f"CATEGORY_PAGE_FAILURES {sum(1 for _, status, _ in page_status if status != 200)}")
    for page, status, error in page_status:
        if status != 200:
            print(f"PAGE_BROKEN\t{status}\t{page}\t{error}")
    print(f"UNIQUE_ASSETS_TESTED {len(resources)}")
    print(f"ASSET_FAILURES {len(failures)}")
    for target, status, content_type, error, sources in failures:
        print(f"ASSET_BROKEN\t{status}\t{content_type}\t{target}\t{error}")
        for source in sources:
            print(f"  REFERENCED_BY\t{source}")
    same_origin = sum(1 for target in resources if urllib.parse.urlsplit(target).netloc == BASE_HOST)
    external = len(resources) - same_origin
    print(f"SAME_ORIGIN_ASSETS {same_origin}")
    print(f"EXTERNAL_ASSETS {external}")
    return 1 if failures or any(status != 200 for _, status, _ in page_status) else 0

if __name__ == "__main__":
    raise SystemExit(main())
