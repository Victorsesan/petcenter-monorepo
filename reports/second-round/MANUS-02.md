

## Production-check availability — 2026-11-05

Checked the latest fetched repository refs before attempting production authentication or audio submission. `adopt/admin/chat-transcribe.php` is present on this MANUS-02 task branch but absent from both `origin/main` and `origin/adopt-deploy`; the transcript migration is likewise not on `origin/main`. Therefore the feature under test is not the code currently published by the repository's deployment pipeline. The repository's Worker Git Sync Protocol and Integrator Git Sync Merge Protocol explicitly reserve merging to `main` and refreshing `adopt-deploy`/hosting for the Integrator; workers must not touch the deploy branch or hosting panel. No deployment or production request was attempted.

This sandbox also has no `GROQ_API_KEY`, SSH agent socket, FTP/SFTP host, or production database connection variables. The earlier local test used synthetic fixture bytes and localhost-only mocks, not a recording of real speech. Consequently I did not claim to have verified production authentication, migration execution, real-audio recognition quality, or a live Groq response. Those checks can only target this implementation after the Integrator publishes it and an authorized server-side provider key plus a permitted test chat/audio fixture are available.
