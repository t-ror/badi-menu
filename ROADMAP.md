# Roadmap

Planned changes for badi-menu, each verified against the codebase on the date given in its own
**Status:** line (the bulk were checked on 2026-07-16; the most recent pass was 2026-08-01).
These items document intent — they are not a work queue; do not start them unprompted
(see the Roadmap section in CLAUDE.md). Effort: **S** < 1 h, **M** = hours, **L** = days+.
This file is public: no secrets, server details, or private Docker-repo internals belong here.

## Now

Overdue maintenance and quick wins.

- **Upgrade MariaDB 11.5 → 11.8 LTS** — **overdue**: 11.5 is a short-term release, out of
  support since ~Aug 2025. Status: pinned to `mariadb:11.5.2`. Evidence: `docker-compose.yml`. Effort: M.

- **composer.json cleanup** — remove or fix stale entries: `dg/ftp-deployment` (dead — deploys are
  SSH/Docker via `deploy.yml`); `composer/package-versions-deprecated` (Composer 1 shim);
  `doctrine/annotations` (entities use attributes — `type: attribute` in
  `config/packages/doctrine.yaml`, no annotation imports in `src/`).
  `dg/ftp-deployment` is now the priority of the three: it is the sole reason
  `phpseclib/phpseclib` is installed, and that package carries all 4 remaining security advisories
  (2 high, 1 medium, 1 low). Dev-only, so it never reaches the prod image (`make prod-install`
  uses `--no-dev`) — but removing it clears the audit outright.
  Resolved by the Symfony 7.4 upgrade: `extra.symfony.require` (was stuck at `"5.2.*"`, now
  `"7.4.*"`) and `symfony/proxy-manager-bridge` (removed).
  Status: 3 of 5 entries remain. Evidence: `composer.json`, `composer audit`,
  `composer why phpseclib/phpseclib`. Effort: S.

- **`var/` is not owned by the php-fpm user on the server — caused a production outage
  2026-08-01.** **Blocks the PHP 8.3 → 8.4/8.5 bump under Next; do that one only after this is
  fixed.** After the Symfony 7.4 deploy every request returned 500 with
  `RuntimeException: Unable to create the cache directory (var/cache/prod/twig/47)` and
  `Unable to write in the cache directory (var/cache/prod/twig/1a)`, both from
  `Twig\Cache\FilesystemCache::write()` (lines 53 and 57). Line 57 is the
  `elseif (!is_writable($dir))` branch — the directory existed and php-fpm simply could not write
  to it. Fixed live by removing `var/cache/prod` as root, `chown -R www-data:www-data var`, then
  re-running the warmup as www-data.

  **The upgrade was the trigger, not the cause.** `Twig\Environment::updateOptionsHash()` builds
  the template cache key from `extensionSet signature : PHP_MAJOR_VERSION : PHP_MINOR_VERSION :
  Twig VERSION : debug : strictVariables`. Twig 3.11 → 3.28 changed that hash, so every template
  moved to a new path and the two-character subdirectories had to be **created** instead of read —
  which is the first time the ownership ever mattered. The root-owned `var/` predates the upgrade.

  Anything that perturbs that hash trips the same wire: a **PHP minor bump**, any Twig upgrade
  (including a patch release), adding a Twig extension, or toggling `strict_variables`. This is
  why it must land before the PHP upgrade.

  Compounding it: `deploy.yml:117` runs `cache:clear` as `www-data` specifically to prevent this,
  but as www-data it also cannot delete the root-owned files — so the same defect disabled the
  mitigation. And because that step runs *after* `docker compose up -d app` under `set -eu`, the
  job goes red only once the broken container is already serving traffic (see the missing
  post-deploy health check under Next).

  The durable fix is to guarantee `var/` is owned by the php-fpm user — either in the image or as
  a step in the deploy script before the warmup. The image side lives in the private Docker config
  repo. Status: worked around live 2026-08-01, **not yet fixed permanently**. Effort: S.

- **`make ci` does not pass on master** — **pre-existing; not caused by the Symfony 7.4 upgrade,
  which left both tools exactly as red as it found them.** The quality gate is red on a clean
  checkout: **phpcs** fails on 4 files (`src/EventSubscriber/SecurityHeadersSubscriber.php`
  is space-indented and uses `strict_types=1` without the required spaces;
  `src/Controller/File/ImageController.php` and `src/ValueObject/File/Image.php` have formatting
  errors; `src/Twig/ImageExtension.php` has an unused import) and **PHPStan** reports 2 errors in
  `ImageController.php:31,35` (`Only booleans are allowed in a negated boolean, int|false given`).
  Introduced by `a8756d3` and `a1b23d3` (both 2026-03-08) — i.e. the gate has been bypassed since
  then. Most of the phpcs errors are auto-fixable with `phpcbf`. `doctrine:schema:validate` passes.
  Status: confirmed 2026-08-01. Effort: S.

- **Untrack committed runtime/dev artifacts** — `supervisord.pid` is tracked and not gitignored
  (only `supervisord.log` is); `docker-compose.override.yml` is tracked *and* matched by
  `.gitignore:24`, making the ignore rule dead. Untrack both, gitignore the pid file, and commit
  a `docker-compose.override.yml.dist` template instead.
  Status: not started. Evidence: `git ls-files`, `.gitignore`. Effort: S.

- **Add `declare(strict_types = 1)` to `src/Kernel.php`** — still the only PHP file in `src/`
  missing it. Status: confirmed 2026-07-16. Evidence: `src/Kernel.php:1`. Effort: S.

## Next

- **Establish a real test suite** — `phpunit.xml.dist` is configured but `tests/` contains only
  `bootstrap.php`; PHPUnit ships via `symfony/phpunit-bridge`. Start with unit tests for
  `Service/` and `ValueObject/` under `tests/` (`App\Tests\` namespace).
  Status: no tests exist. Evidence: `tests/`, `phpunit.xml.dist`. Effort: L.

- **`doctrine/doctrine-bundle` triggers a Symfony 7.4 deprecation** — `Since symfony/doctrine-bridge
  7.4: The "Symfony\Bridge\Doctrine\DependencyInjection\AbstractDoctrineExtension" class is
  deprecated`. Emitted by doctrine-bundle 2.12.0, which still extends it. A newer 2.x should clear
  it and the existing `^2.12` constraint already allows one, so this is a lockfile-only update — it
  was left out of the Symfony upgrade to keep that diff to `symfony/*`. The second deprecation seen
  at request time (`connection_override_options`, doctrine-bundle 2.4) predates the upgrade.
  Status: observed 2026-08-01. Effort: S.

- **PHPStan 1.x → 2.x** — phpstan 1.11.10 plus 1.x extensions (doctrine, symfony, strict-rules);
  bump `slevomat/coding-standard ^7` (current major is 8) alongside.
  Status: on 1.x. Evidence: `composer.json` require-dev, `dev/phpstan.neon`. Effort: M.

- **PHP 8.3 → 8.4/8.5** — PHP 8.3 is security-only since January 2026. Requires a runtime-image
  rebuild in the private Docker config repo (details live there).
  **Blocked on the `var/` ownership fix under Now.** `PHP_MAJOR_VERSION` and `PHP_MINOR_VERSION`
  are inputs to Twig's template cache key, so a minor bump relocates every compiled template and
  reproduces the 2026-08-01 outage exactly. Fix the ownership first, then bump.
  Status: `"php": ">=8.3"`. Evidence: `composer.json`. Effort: M.

- **Review GHCR package visibility** — the prod image is anonymously pullable; decide whether
  that is intended, given it is built from the private Docker config.
  Status: public as of 2026-07-16. Evidence: `ghcr.io/t-ror/badi-menu` (anonymous pull). Effort: S.

- **`DB_PASSWORD` / `DB_ROOT_PASSWORD` are unset when compose runs on the server** — every deploy
  logs `The "DB_PASSWORD" variable is not set. Defaulting to a blank string.` They are consumed by
  the `db` service (`docker-compose.yml:26-29`) but compose only auto-reads `.env`, not
  `.env.prod.local` where the real prod secrets live. Harmless today: deploys target only the `app`
  service, and MariaDB ignores `MYSQL_*` once its data directory is initialised. **The risk is
  disaster recovery** — a rebuild on a fresh volume would initialise the database with blank
  credentials while the app's `DATABASE_URL` carries real ones. Fix is server-side (compose
  `--env-file`, or exporting the vars in the deploy script). Status: observed 2026-07-30.
  Evidence: deploy run `30579773767`. Effort: S.

- **Delete the orphaned `FTP_PASSWORD` repository secret** — dating from 2021-05-08, left over from
  the removed `dg/ftp-deployment` workflow (see composer.json cleanup above). Nothing reads it, but
  any workflow in the repo still can. Delete it, and rotate the password on the FTP account if that
  account still exists. Status: observed 2026-07-30. Evidence: `gh secret list`. Effort: S.

- **`check-commits` misses commits past the push-payload cap** — `build.yml` greps
  `toJson(github.event.commits)`, which GitHub caps at 20 commits per push event. Pushing 25
  commits where only the first is `fix:` silently skips the build. Reading the commit range via
  the API would be robust. Status: not started. Evidence: `.github/workflows/build.yml`. Effort: S.

- **No post-deploy health check** — `deploy.yml` reports success once `cache:clear` returns; it
  never confirms the app actually serves traffic. A curl against a health endpoint after
  `compose up` would close the loop. Status: not started. Evidence: `.github/workflows/deploy.yml`.
  Effort: S.

- **Docker layer cache is ineffective at this build cadence** — `cache-to: type=gha` entries are
  evicted after 7 days unused, and builds land months apart (April, then July 2026), so nearly
  every build starts cold. Evaluate `type=registry` cache versus dropping caching entirely.
  Status: not started. Evidence: `.github/workflows/build.yml`. Effort: S.

- **`webfactory/ssh-agent` is likely redundant** — `actions/checkout`'s `ssh-key` input is what
  stops `git@github.com:` submodule URLs being rewritten to HTTPS, so it appears to be the
  load-bearing piece. Confirm and remove *on its own branch*, where a failure is diagnosable.
  Status: deliberately left in place during the Node 24 migration. Evidence:
  `.github/workflows/build.yml`. Effort: S.

## Later

- **Vue 3 SPA + Symfony REST API migration** — **not started; do not begin unprompted.**
  Current state: Vue 2.5 with `vue-loader` 15 and Webpack Encore 1.x (`package.json`), single
  entry `assets/app.js` (`webpack.config.js`), Vue mounted onto Twig-rendered nodes
  (`#flashes`, `#mealTagList` in `assets/app.js`). Coupling points a migration must address:
  - Server-rendered forms: 7 form types in `src/Type/` themed via `bootstrap_5_layout`
    (`config/packages/twig.yaml`); Twig templates co-located under `src/Controller/`.
  - Session-based security: `form_login` + remember-me + CSRF (`config/packages/security.yaml`);
    an API needs stateless/token auth.
  - No API routes: every route is a server-rendered page (`config/routes.yaml`); the single JSON
    endpoint `MealTagController::provideListData`
    (`src/Controller/MealTag/MealTagController.php:31`) returns Twig-rendered form HTML inside JSON.

  Status: not started. Effort: L.

- **CORS configuration** — deferred until the SPA migration (blocked on the item above); nothing
  exists today. Status: confirmed absent — no nelmio/cors-bundle in `composer.json`, no CORS
  config under `config/`. Effort: S.

- **Frontend legacy dependencies** — `quill ^1.3.6` (2.x GA since 2024), `select2` pinned at a
  release candidate (`4.1.0-rc.0`), `@symfony/stimulus-bridge` as an unpinned GitHub ref.
  Likely superseded by the SPA migration — decide there first.
  Status: all in use. Evidence: `package.json`. Effort: M.

## Done

- **Upgraded Symfony 7.1 → 7.4 LTS** — deployed to production 2026-08-01. **The deploy caused a
  brief outage** — every request 500'd on an unwritable Twig cache directory. Not a defect in the
  upgrade: it exposed pre-existing root-owned `var/` on the server, and is tracked as its own item
  under Now (which also blocks the PHP bump). Resolved live by clearing `var/cache/prod` and
  correcting ownership.
  `v7.1.3` → `v7.4.15`; 51 `symfony/*` packages on 7.4.x, **0 left on 7.1.x**, no `dev-` versions
  despite `minimum-stability: dev`. `bin/console about` now reports *Long-Term Support: Yes*,
  end of maintenance 11/2028, EOL 11/2029 — previously EOL 01/2025 (expired).
  Security advisories dropped **42 across 14 packages → 4 across 1** (`phpseclib`, dev-only; see
  the composer.json cleanup item under Now).

  Three things this surfaced that were not obvious up front:
  - **`extra.symfony.require` was load-bearing, not cosmetic.** Flex applies it as a version filter
    on every `symfony/*` package, so the update was a no-op until it was moved `5.2.*` → `7.4.*`.
    It has to change *before* `composer update`, not as later cleanup.
  - **`config/packages/twig.yaml` registered `public/` as the `public_path` namespace with no
    filename filter**, so the Twig cache warmer compiled every file under `public/` as a template —
    including the 4 MB webpack bundle `public/build/238.js`, which became an 8.2 MB PHP class and
    exhausted the 128 M memory limit. This made `cache:clear` fail, which breaks both
    `composer install` (via `post-install-cmd`) and the post-deploy `cache:clear` in `deploy.yml`.
    Fixed with `file_name_pattern: '*.twig'`. Latent misconfiguration, not created by the upgrade —
    the upgrade pushed it past the memory limit. Twig cache 17 MB → 3.4 MB (dev), 0 bundles
    compiled; verified in **both dev and prod** env at the default 128 M limit.
    `file_name_pattern` reaches only `twig.template_iterator` and `twig.command.lint`
    (`TwigExtension.php:136,139`) — never the runtime loader, so `source('@public_path'~icon_chef)`
    in the household templates is unaffected.
  - **`User::getUserIdentifier()`** needed a non-empty guard: Symfony 7.4 tightened
    `UserInterface::getUserIdentifier()` to `@return non-empty-string`.

  Also removed with `symfony/proxy-manager-bridge`: `friendsofphp/proxy-manager-lts` and
  `laminas/laminas-code` (lazy services use `symfony/var-exporter` natively since 7.0).
  Flex normalised one stray tab in `config/bundles.php`.

  Verified: `doctrine:schema:validate` OK; PHPStan **2 errors, identical to the pre-upgrade
  baseline** (the 7.4-introduced third was fixed, the 2 remaining are pre-existing — see the
  `make ci` item under Now); phpcs identical to baseline (same 4 files). `composer install` runs
  both auto-scripts clean. `/prihlaseni` returns 200 with a rendered login form and CSRF token,
  `/` returns 302; `symfony/webpack-encore-bundle` v2.1.1 → v2.4.1 still resolves the
  March-built `entrypoints.json` (`runtime.js`, `app.js`, `238.js`).
  Two deprecations at request time, both inside vendor code, none from `src/`.

  Not exercised: no authenticated page was rendered, so the household templates that use
  `source('@public_path'~icon_chef)` were not hit over HTTP (the DI wiring above is the evidence
  instead); no test suite exists; mailer/notifier paths untouched. Local verification was all in
  dev/prod env on the developer machine — nothing caught the server-side ownership problem, which
  only appears where php-fpm runs as a non-root user against a persistent `var/`.
  Note: the compose `app` container has **no external DNS** (Docker's embedded resolver reports
  *NO EXTERNAL NAMESERVERS DEFINED*), so `composer` cannot reach packagist from `docker compose
  exec`. The update was run via a one-off `docker run --dns 1.1.1.1` on the same app image.

- **Moved GitHub Actions off Node 20 runners** — completed and verified in production 2026-07-30
  (`d7dbf5c`). Corrected framing: runners had defaulted to Node 24 since **2026-06-16**, so the
  node20-declared actions were already executing on Node 24; the hard failure would have been
  Node 20's **removal from the runner image in autumn 2026**. Four actions declared
  `runs.using: node20` (`actions/checkout@v4`, `webfactory/ssh-agent@v0.9.0`,
  `docker/login-action@v3`, `docker/setup-buildx-action@v3`); `docker/build-push-action@v7` was
  already Node 24 and `appleboy/ssh-action@v1` is a composite action with no Node runtime.
  All actions are now pinned to full commit SHAs with version comments, plus
  `.github/dependabot.yml` (grouped, monthly, `chore` prefix so it does not trigger builds).
  Also fixed: `build.yml`/`deploy.yml` shared a repo-wide concurrency group and cancelled each
  other (groups are scoped per repository, not per workflow); `:latest` now only moves on master;
  `deploy.yml` gained an `image_tag` input, a pre-flight image-existence check, SSH host-key
  verification, and env-var passing instead of `${{ }}` interpolation into the remote script.
  Evidence: Node 20 deprecation warnings **8 → 0** (baseline run `29268035747`; branch build
  `30574368412` and master build `30575669611` both 0); private submodule still checks out under
  checkout v7; branch build applied a single `--tag` and left `:latest` untouched, master build
  moved it; deploy `30579773767` recreated the app container and cleared the cache.
  Note for future reference: the SSH host-key fingerprint is the **ECDSA** one, not RSA — despite
  Go's client preferring `rsa-sha2-*`, this server negotiates ECDSA. Stored as the
  `SERVER_FINGERPRINT` secret; deleting that secret reverts to no host-key checking.
  Not exercised: the pre-flight check's failure path, and the concurrency fix (would need a
  simultaneous build and deploy).

- **TLS renewal confirmed** — live certificate reissued 2026-07-10, valid to 2026-10-08
  (checked 2026-07-16); the ~June 2026 renewal concern is resolved.
- **GHCR prod image verified as remote backup** — registry lists `latest` plus recent commit-SHA
  tags including HEAD (`c6d1cc8`), checked 2026-07-16. Visibility review tracked under Next.
- **PWA webmanifest + icons** — shipped in `c21a29f`.
- **Image build/deploy pipeline fixes** — `4a8b277`, `6c82f50`, and `c6d1cc8` (post-deploy
  `cache:clear` in `deploy.yml`, fixing stale compiled Twig after deploys — verified live 2026-07-16).
