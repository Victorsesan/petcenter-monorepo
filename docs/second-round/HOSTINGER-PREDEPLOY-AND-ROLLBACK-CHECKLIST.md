# PetCenter Hostinger Publication, Backup, and Rollback Checklist

**Applies to:** the coordinator and every account contributing to the final release.

**Target domain:** `petcenterco.com`

**Repository:** `Victorsesan/petcenter-monorepo`

## Non-negotiable release controls

1. No worker account may publish, deploy, push, upload, or synchronize anything to Hostinger.
2. No worker account may trigger an automatic deployment from GitHub to Hostinger.
3. Only the user/coordinator may authorize the production release after reviewing the final staging result.
4. The coordinator must request explicit user permission immediately before the production action. A previous instruction to prepare the site is not permission to deploy it.
5. If GitHub is connected to Hostinger with automatic redeployment enabled, disable the trigger or use a protected release branch until the user explicitly approves the release commit.
6. Never store Hostinger, FTP/SFTP, SSH, database, OAuth, or application secrets in this repository, a report, a prompt, a URL, or shell history.

## A. Before any production change

### 1. Freeze and identify the release

- Confirm the release commit hash.
- Confirm the exact source branch.
- Confirm that the branch contains only reviewed changes.
- Confirm that `git status` is clean.
- Confirm that no worker branch is being merged concurrently.
- Tag or otherwise record the release commit in Git without exposing secrets.
- Record the intended Hostinger document root and deployment method.
- Record whether Hostinger is connected to GitHub auto-deployment, SSH/Git, SFTP, File Manager, or another mechanism.

The current integration candidate is not automatically a production release. It must first pass staging and the coordinator’s review.

### 2. Create a repository backup

The coordinator must create and verify a recoverable Git backup before deployment:

```bash
git fetch --all --prune
git status --short --branch
git tag -a pre-hostinger-petcenterco-YYYY-MM-DD -m "Verified pre-production release checkpoint"
git bundle create ../petcenterco-pre-hostinger-YYYY-MM-DD.bundle --all
sha256sum ../petcenterco-pre-hostinger-YYYY-MM-DD.bundle
```

Store the bundle outside the repository and in a second safe location. Record the tag name, bundle path, SHA-256 checksum, and commit hash in the release record. Never commit the bundle into the website repository.

Also preserve the last known-good production commit and do not delete it until the new release has passed its observation window.

### 3. Create a Hostinger backup

Before uploading or triggering deployment, the coordinator must create and verify:

- a full website-file backup of the relevant Hostinger document root;
- a database export if the site uses a database or any external data store;
- a separate backup of `.htaccess` and other hosting configuration files;
- a separate backup of production environment/configuration values, stored securely and not inside Git;
- a record of scheduled jobs, PHP version, extensions, domain bindings, redirects, SSL settings, and DNS settings;
- a record of writable runtime directories and their permissions;
- a record of protected runtime data such as applications, uploads, chat media, logs, and merchant records.

Verify that the backup can be opened and that its file count, archive size, and database export are non-empty. A backup that was created but never tested is not a release safeguard.

Do not replace the backup with a screenshot of hPanel. Keep the actual downloadable archive/export in a secure location controlled by the user.

### 4. Verify the Hostinger target

Before release, confirm:

- `petcenterco.com` points to the intended Hostinger site;
- the correct document root is selected;
- `www.petcenterco.com` behavior is intentional;
- HTTPS is active and redirects are correct;
- the PHP version and required extensions match the application;
- the production environment contains required server-side values;
- `.env`, private config, application records, uploads, logs, and runtime data will not be overwritten by a code-only deployment;
- GitHub/Hostinger automatic deployment will not run before approval;
- the deployment does not copy repository documentation or test fixtures into a public path unless intentionally required;
- no staging credentials or test accounts will be exposed to visitors.

## B. Release candidate validation

Run these checks against the final integration candidate before production:

- PHP lint for every changed PHP file;
- JavaScript syntax checks for every changed JavaScript file;
- `git diff --check`;
- repository-wide conflict-marker scan, excluding binary false positives;
- route and asset smoke tests;
- browser tests at desktop, tablet, mobile, and RTL mobile sizes;
- console-error and failed-network-request checks;
- keyboard and focus checks;
- form validation and error-state checks;
- authentication, authorization, CSRF, session, upload, and rate-limit checks;
- no-secret and protected-runtime-file scan;
- no new 404s;
- no unintended changes to API response fields or storage paths;
- accessibility and reduced-motion checks;
- verification of legal-page links;
- verification of chat and accessibility widgets;
- verification of all new imagery, licenses, alt text, dimensions, and fallback behavior.

For WEB-04 specifically, validate the actual repository paths rather than assuming a proposed route. The current known entry/platform paths include:

```text
adopt/signin/index.html
adopt/rehoming/index.html
adopt/rehoming/list-a-pet/index.html
adopt/rehoming/*.php
adopt/assets/js/rehoming-platform-v1.js
adopt/assets/js/show-auth-prompt.js
```

The final gateway route may change, but the route decision must be documented and every link, redirect, test, and report must use the same authoritative route.

## C. Approval gate before production

Prepare a release packet containing:

- release commit and tag;
- staging URL and test account instructions, without secrets in the packet;
- test results and known limitations;
- backup locations and checksums;
- Hostinger deployment method and target document root;
- environment/configuration checklist;
- rollback command or file-restore procedure;
- monitoring and observation-window plan;
- exact list of files that will be changed in production.

Then stop and ask the user for explicit approval using a clear statement such as:

> The release candidate has passed staging checks. The repository backup and Hostinger backup have been verified. The exact release is commit `[HASH]`, targeting `[DOCUMENT ROOT]` through `[DEPLOYMENT METHOD]`. May I publish this release to `petcenterco.com`?

No account may infer approval from silence, a prior request to prepare, a branch merge, a successful staging test, or a previous deployment.

## D. Controlled production release

After explicit approval only:

1. Confirm the release hash one more time.
2. Confirm the backup archive and checksum one more time.
3. Put the site into a maintenance or protected deployment state if the deployment method requires it.
4. Preserve production runtime data; do not overwrite it with repository placeholders.
5. Deploy only the approved commit and approved file scope.
6. Watch the deployment output and Hostinger logs.
7. Do not make unrelated fixes during the release.
8. Record the deployment time, operator, method, target, and result.
9. Keep the previous production backup and commit available.

Hostinger’s published deployment guidance recommends verifying pages/navigation, images/CSS/JavaScript, forms and interactions, mobile/tablet layouts, and HTTPS after deployment. Use those checks together with the PetCenter-specific checks above.

## E. Immediate post-deployment smoke test

Within minutes of release, test:

- `https://petcenterco.com/`;
- `https://www.petcenterco.com/` if supported;
- representative public adoption pages;
- representative rehoming pages;
- the authentication gateway;
- legal pages;
- admin sign-in without exposing an admin surface publicly;
- chat widget and accessibility widget;
- one valid and one invalid form submission in a safe test path;
- asset loading from nested routes;
- HTTPS, redirects, cookies, and security headers;
- PHP error log and server response codes;
- no accidental source/configuration exposure.

Do not enable verbose `display_errors` in production. Use Hostinger error logging for diagnosis and remove temporary diagnostic files such as `phpinfo.php` immediately after use.

## F. Observation window and rollback

Observe the site for an agreed period, including real navigation and relevant logs. Keep the old production state available until the observation window ends.

Rollback immediately if there is a severe error such as:

- widespread HTTP 500/502/503 responses;
- authentication or authorization bypass;
- data loss or corrupted writes;
- exposed secrets or protected files;
- broken application, merchant, or admin flows;
- severe asset or route failure;
- unsafe duplicate submissions or payment-related behavior;
- a security regression.

Rollback procedure:

1. Stop any automatic deployment trigger.
2. Record the failure and time without exposing sensitive data.
3. Restore the previous known-good Git commit through the approved deployment method, or restore the verified Hostinger file backup if Git rollback is not sufficient.
4. Restore the database backup only when the failure affected data and the coordinator has confirmed the correct restore point.
5. Re-check HTTPS, routes, authentication, forms, logs, and runtime data.
6. Confirm the previous version is serving correctly.
7. Preserve the failed release for diagnosis; do not delete its commit or logs.
8. Open a corrective task and do not redeploy until the root cause and new release have been reviewed.

## G. Account reporting requirements

Every worker must add its own findings to its report, including:

- additional security, accessibility, performance, UX, compatibility, route, data, or deployment risks discovered;
- the exact file and path affected;
- severity and reproducibility;
- whether the issue was fixed in scope, handed to another owner, or recorded for coordinator review;
- tests added or run;
- any backup, rollback, or production-safety concern.

Accounts must not merely follow the checklist mechanically. They must improve it through evidence-based findings, while leaving the final production approval and Hostinger action to the user/coordinator.

## References

[1]: https://www.hostinger.com/tutorials/how-to-deploy-a-website-from-your-ide/ "Hostinger: How to deploy a website from your IDE"
[2]: https://www.hostinger.com/tutorials/how-to-host-a-web-application/ "Hostinger: How to host a web application"
[3]: https://www.hostinger.com/tutorials/how-to-check-your-website-error-log/ "Hostinger: How to check your website error log"
