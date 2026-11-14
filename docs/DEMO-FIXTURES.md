# PetCenter Preview Fixtures

This recovery candidate contains temporary, clearly labelled fixture data so the public catalog and detail routes can be tested without a live database or real applicant/listing data.

- `adopt/adoptions/pets/*/*/pet.json` contains one synthetic record for every ID listed by the category `index.json` files.
- `adopt/assets/images/demo-birds/` contains five locally generated SVG illustrations for the first five bird fixtures.
- Every fixture contains `demo_fixture: true` and a `fixture_notice` stating that it must be replaced before production publication.
- Fixture data must not be copied to Hostinger as real inventory, used for real adoption decisions, or presented as verified animal/veterinary information.
- The generator is `tools/generate-demo-pet-fixtures.py`; rerunning it refreshes timestamps and deterministic sample values.

The fixtures exist only to exercise catalog loading, filters, detail pages, image fallbacks, quiz matching, application links, and route/404 checks in the isolated preview.
