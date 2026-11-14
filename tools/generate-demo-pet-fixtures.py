#!/usr/bin/env python3
"""Generate clearly labelled local demo pet records for preview/QA only."""
from __future__ import annotations

import json
import re
from datetime import datetime, timezone
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
PET_ROOT = ROOT / "adopt" / "adoptions" / "pets"
IMAGE_ROOT = ROOT / "adopt" / "assets" / "images" / "demo-birds"
NOW = datetime.now(timezone.utc).replace(microsecond=0).isoformat().replace("+00:00", "Z")

BIRD_DESIGNS = {
    "BIRD-00001": ("Sunny", "Budgerigar", "demo-bird-budgie.svg", "Bright green and yellow budgerigar"),
    "BIRD-00002": ("Coco", "Cockatiel", "demo-bird-cockatiel.svg", "Friendly grey cockatiel with a yellow crest"),
    "BIRD-00003": ("Rio", "Green-cheek Conure", "demo-bird-conure.svg", "Playful green-cheek conure"),
    "BIRD-00004": ("Pip", "Canary", "demo-bird-canary.svg", "Cheerful yellow canary"),
    "BIRD-00005": ("Kira", "White Belly Caique", "demo-bird-caique.svg", "Energetic white-bellied caique"),
}

SVG_TEMPLATES = {
    "demo-bird-budgie.svg": ("#7dbb57", "#f4d35e", "Budgie"),
    "demo-bird-cockatiel.svg": ("#a8a9ad", "#f7d154", "Cockatiel"),
    "demo-bird-conure.svg": ("#3da35d", "#e85d4a", "Conure"),
    "demo-bird-canary.svg": ("#f5c542", "#f08a24", "Canary"),
    "demo-bird-caique.svg": ("#f1f1e8", "#e66c45", "Caique"),
}


def write_bird_svg(name: str, body: str, accent: str, label: str) -> None:
    IMAGE_ROOT.mkdir(parents=True, exist_ok=True)
    svg = f'''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 480" role="img" aria-labelledby="title desc">
  <title id="title">Temporary demo {label} illustration</title>
  <desc id="desc">A locally generated placeholder illustration used for PetCenter preview testing.</desc>
  <rect width="640" height="480" fill="#fff8ed"/>
  <circle cx="505" cy="95" r="55" fill="#f9df9b" opacity=".7"/>
  <path d="M0 390 Q140 325 290 380 T640 350 V480 H0Z" fill="#dcebd6"/>
  <path d="M205 290 C190 205 265 130 370 160 C458 185 473 292 386 338 C316 375 224 354 205 290Z" fill="{body}" stroke="#27364b" stroke-width="8"/>
  <path d="M355 185 C407 145 468 165 489 210 C453 206 424 228 400 258Z" fill="{accent}" stroke="#27364b" stroke-width="8"/>
  <path d="M219 280 C165 252 120 268 90 302 C151 304 201 327 244 335Z" fill="{accent}" stroke="#27364b" stroke-width="8"/>
  <circle cx="403" cy="214" r="12" fill="#172238"/>
  <path d="M482 239 l68 18 -69 22Z" fill="#e88a32" stroke="#27364b" stroke-width="7"/>
  <path d="M300 342 v62 M360 342 v62" stroke="#27364b" stroke-width="9" stroke-linecap="round"/>
  <path d="M270 406 h55 M330 406 h55" stroke="#e88a32" stroke-width="9" stroke-linecap="round"/>
  <text x="32" y="54" font-family="Arial,sans-serif" font-size="22" font-weight="700" fill="#10214b">PetCenter demo fixture</text>
  <text x="32" y="84" font-family="Arial,sans-serif" font-size="18" fill="#6c5d50">{label} illustration — preview only</text>
</svg>\n'''
    (IMAGE_ROOT / name).write_text(svg, encoding="utf-8")


def read_ids(category: Path) -> list[str]:
    data = json.loads((category / "index.json").read_text(encoding="utf-8"))
    return [str(item) for item in data.get("pets", []) if re.fullmatch(r"[A-Z0-9-]+", str(item))]


def make_record(category: str, pet_id: str, ordinal: int) -> dict:
    species = {
        "dogs": "Dog", "cats": "Cat", "birds": "Bird", "reptiles": "Reptile",
        "small-mammals": "Small Mammal", "livestocks": "Livestock", "aquariums": "Aquarium",
    }.get(category, category.title())
    if pet_id in BIRD_DESIGNS:
        name, breed, image_name, description = BIRD_DESIGNS[pet_id]
        image = f"/assets/images/demo-birds/{image_name}"
    else:
        name = f"{species} Demo {ordinal:02d}"
        breed = f"{species} companion"
        description = f"Temporary {species.lower()} fixture for preview and route testing."
        image = "/assets/images/pet-placeholder.svg"
    return {
        "pet_id": pet_id, "id": pet_id, "name": name, "category": category,
        "species": species, "breed": breed, "age_years": str(round(1.5 + (ordinal % 5) * 0.7, 1)),
        "size": "Medium", "gender": "Female" if ordinal % 2 else "Male",
        "adoption_fee": str(150 + ordinal * 25), "currency_code": "USD", "status": "Available",
        "created_at": NOW, "updated_at": NOW,
        "description_short": description,
        "description_full": f"{description} This record is synthetic and must be replaced with verified listing data before production publication.",
        "health_info": "Demo fixture: health information is not a real veterinary record.",
        "health_status": "Demo fixture; veterinary status not verified.",
        "temperament": "Friendly demo temperament for interface testing.",
        "reason_for_rehoming": "Temporary preview fixture.", "spay_neuter_status": "unknown",
        "listing_type": "permanent", "is_urgent": False, "multiple_adoptable_options": "no",
        "location": "PetCenter Preview, CA", "single_image_url": image,
        "bulk_images_url": None, "personality": ["Friendly", "Curious"],
        "compatible_kids": "yes", "compatible_dogs": "yes", "compatible_cats": "yes",
        "compatible_other_pets": "yes", "vaccinated": "unknown", "spayed_neutered": "unknown",
        "house_trained": "unknown", "can_ship": "no", "merchant": "PetCenter Preview",
        "posted_by": "PetCenter Preview", "demo_fixture": True,
        "fixture_notice": "Synthetic preview data. Replace before production use.",
    }


def main() -> None:
    for filename, (body, accent, label) in SVG_TEMPLATES.items():
        write_bird_svg(filename, body, accent, label)
    count = 0
    for category in sorted(PET_ROOT.iterdir()):
        if not category.is_dir() or not (category / "index.json").exists():
            continue
        for ordinal, pet_id in enumerate(read_ids(category), start=1):
            record_dir = category / pet_id
            record_dir.mkdir(parents=True, exist_ok=True)
            (record_dir / "pet.json").write_text(json.dumps(make_record(category.name, pet_id, ordinal), indent=2) + "\n", encoding="utf-8")
            count += 1
    print(f"Generated {count} demo pet records under {PET_ROOT}")
    print(f"Generated {len(SVG_TEMPLATES)} local bird illustrations under {IMAGE_ROOT}")


if __name__ == "__main__":
    main()
