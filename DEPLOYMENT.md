# VPS auto deployment

The workflow in `.github/workflows/deploy.yml` deploys pushes to `main` and
supports a manual run from GitHub Actions. It updates only the configured project
directory; it does not change Nginx, other applications, APP_KEY, or seed users.

## GitHub configuration

Create these repository secrets under Settings > Secrets and variables > Actions:

| Secret | Value |
| --- | --- |
| `VPS_HOST` | Public IP or hostname of the VPS |
| `VPS_SSH_KEY` | Complete PEM private key, including header and footer |

Never commit the PEM file. The workflow obtains SSH host keys automatically using
`ssh-keyscan` on each run; no `VPS_KNOWN_HOSTS` secret is needed. It supports the
configured SSH port and fails if the scan cannot obtain keys. Scanned keys are
not independently verified against a trusted fingerprint.

Optional repository variables:

| Variable | Default |
| --- | --- |
| `VPS_USER` | `ubuntu` |
| `VPS_PORT` | `22` |
| `DEPLOY_PATH` | `/var/www/invoice-printer` |

Set `VPS_USER=root` only if your PEM authenticates that account. A non-root deploy
user is supported. The workflow uses the GitHub environment named `production`;
any protection rules configured for that environment also apply.

## Prepare the server once

- Configure the existing application, production `.env`, APP_KEY, database,
  public storage link if needed, Nginx and HTTPS before enabling automation.
- Install compatible PHP and extensions (Laravel 12 needs PHP 8.2+), Composer,
  Git, npm, Node 20.19+ or 22.12+, and `flock`.
- The target directory must contain the Git checkout and installed `vendor`.
  Its origin must point to this repository. GitHub is the source of deployed code:
  tracked local changes are automatically backed up in Git stash, then the checkout
  is reset to the triggering commit. Local commits are not deployed.
- The SSH user must own the checkout and be able to write `storage` and
  `bootstrap/cache`. PHP-FPM must also have the necessary runtime permissions.
- For a private repository, separately configure the server's read-only GitHub
  deploy key. The inbound VPS PEM does not give the server access to GitHub.
- Keep database and uploaded-file backups before deploying migrations.
- Commit `composer.lock` and `package-lock.json` for reproducible installs.
  Without locks, Composer/npm resolve dependencies on the VPS during deployment.
- Ensure PHP CLI is the intended version. Long-running queue workers must already
  be supervised; `queue:restart` requests that they restart after their current jobs.
  Set distinct cache prefixes and queues when sharing Redis with another app.
  Horizon, Octane, or other persistent services need their own restart steps.

## Run and recover

Commit and push the workflow to main. Open the repository's Actions tab to inspect
the deployment. A run checks server prerequisites, enters maintenance mode, pulls
the exact triggering commit (automatically stashing tracked server edits first),
installs dependencies, builds Vite assets, migrates
the database, caches Laravel configuration/routes/views and restarts queue workers.

This is an in-place deployment with downtime, not an atomic release system.
If a step fails after maintenance begins, the app intentionally remains down.
Inspect the Actions output and Laravel logs, repair or restore the application,
then run `php artisan up` from the project directory. Code rollback alone does
not undo database changes. Do not generate a new APP_KEY during updates.

Verify login, invoice printing/uploads and the other hosted applications after the
first successful run. No deployment health URL has been assumed in this workflow.

The referral text migration retains names on invoices and expenses, removes their
referral foreign keys and deletes the old referral catalog table and permissions.
The catalog's contact/commission metadata is removed. Restoring that metadata
requires a database backup; migration rollback can reconstruct names only.

No `git clean` is run. Ignored `.env`, installed dependencies and uploaded files
are retained; keep production secrets and uploads outside tracked code paths.
View saved server edits with `git stash list`. Do not automatically apply a stash
after deployment, as that would overwrite the newly deployed code.
