# Roadmap

Planned changes for badi-menu, each verified against the codebase on the date given in its own
**Status:** line (the bulk were checked on 2026-07-16; the most recent pass was 2026-08-02).
These items document intent — they are not a work queue; do not start them unprompted
(see the Roadmap section in CLAUDE.md). Effort: **S** < 1 h, **M** = hours, **L** = days+.
This file is public: no secrets, server details, or private Docker-repo internals belong here.

## Now

Overdue maintenance and quick wins.

Empty as of 2026-08-02 — the last three items were cleared together; see the first entry under
Done. Promote from Next when starting new work.

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
  **No longer blocked** — the `var/` ownership fix shipped 2026-08-02 (`106bdb3`, see Done).
  `PHP_MAJOR_VERSION` and `PHP_MINOR_VERSION` are inputs to Twig's template cache key, so a minor
  bump relocates every compiled template — which makes this the **first deploy that genuinely
  exercises that fix**. Send it on its own and watch the deploy log.
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

- **The last three Now items, cleared together 2026-08-02.** *Uncommitted at the time of writing —
  if these land in separate commits, split this entry to match.*

  - **`ImageController::serve()`'s guards work again.** `03afa44` had cleared its PHPStan errors by
    rewriting `!preg_match(...)` as `preg_match(...) === false` (`ImageController.php:31,35`), which
    made both `createNotFoundException()` branches unreachable: `preg_match()` returns `1` on a
    match and `0` on no match, and `false` only on a compilation or backtrack-limit error. Now
    `!== 1`, which restores the check and keeps PHPStan quiet. Measured before the change:
    `../etc` and `evil/../..` both yield `preg_match = 0`, so `=== false` was `false` — the guard
    let them through — while `!== 1` is `true`; a valid `abc` yields `1` and still passes.
    Never a live path-traversal hole: `serve()` has exactly one entry point, the `imageServe`
    route, whose `requirements` (`config/routes.yaml:4-7`) carry the same two patterns and are
    anchored by Symfony's compiled route regex — grep found no other caller in `src/`, `config/`,
    `assets/` or `templates/`. It was lost defence-in-depth against a docblock
    (`ImageController.php:20-28`) that claimed the parameters were validated.

  - **Runtime/dev artifacts untracked.** `supervisord.pid` is out of version control and added to
    `.gitignore` beside `supervisord.log`; `docker-compose.override.yml` is untracked, which makes
    the long-dead `.gitignore:24` rule effective at last, and is replaced in the repo by
    `docker-compose.override.yml.dist`. Both files remain in the working tree — `git rm --cached`
    only stages the removal, so `git status` showing them as `deleted:` is expected.
    The `.dist` is verified to parse: `docker compose -f docker-compose.yml -f
    docker-compose.override.yml.dist config` exits 0 and resolves the same `3306` publish as the
    live override.

  - **`declare(strict_types = 1)` added to `src/Kernel.php`**, the last file in `src/` without it.
    Note that `dev/ruleset.xml:273` excludes `Kernel.php`, so no phpcs run covers this file — which
    is why it was missed for so long, and why it is still 4-space indented against the project's
    tabs standard. Verified by boot instead: `bin/console about` runs in both dev and prod.

  Gate after all three: phpcs **66/66, 0 errors**, PHPStan `[OK] No errors`,
  `doctrine:schema:validate --skip-sync` OK. Smoke: `/` → 302, `/prihlaseni` → 200,
  `/image/meal/1/deadbeef` → 404.

- **`make ci` passes on master again** — closed by `03afa44` (2026-08-01, the 4 phpcs files plus
  the 2 PHPStan errors) and `1df707d`, which added the gate to `build.yml` so it can no longer be
  bypassed silently. Re-verified on master 2026-08-02: phpcs **66/66, 0 errors**, PHPStan
  `[OK] No errors`, `doctrine:schema:validate --skip-sync` OK, and the tree unmodified afterwards.

  Two things worth keeping close:
  - **`make ci` is not a read-only check.** `Makefile:124` is `ci: csfix phpstan test-entity`, and
    `csfix` runs **phpcbf**, which rewrites files under `src/` in place rather than reporting on
    them. Use `make cs` (phpcs) for a check that leaves the tree alone. `build.yml` already does
    exactly that — its `ci` job runs `make cs RAW=1`, deliberately not `make ci`. `CLAUDE.md`
    described the gate as "phpcs + PHPStan + doctrine:schema:validate" and was corrected
    2026-08-02 to say `csfix` and spell out that it rewrites `src/`.
  - **The fix traded a lint error for a dead guard.** Silencing PHPStan in `ImageController` made
    both of its validation branches unreachable — tracked as its own item under Now.

- **`var/` ownership on the server — repaired from the deploy script.** Committed 2026-08-02 as
  `106bdb3`, deployed the same day; production serves normally. Closes the 2026-08-01 outage,
  where every request 500'd on `Unable to create the cache directory (var/cache/prod/twig/47)`
  from `Twig\Cache\FilesystemCache::write()` — php-fpm runs as `www-data` and could not create
  directories under a root-owned `var/`.

  `deploy.yml` now chowns `/var/www/var` to `www-data` twice: best-effort through the old
  container before `docker compose up -d app`, so the new container starts against a writable
  `var/`, then unguarded against the new container. `cache:clear` stays as `www-data` — as root it
  would recreate root-owned cache files. Each deploy closes with two evidence lines: a
  `mkdir`/`rmdir` probe under `var/cache/prod/twig/` run as `www-data`, and
  `find /var/www/var ! -user www-data -print`.

  Three things this surfaced that were not obvious up front:
  - **The image's own `chown` is inert on this server.** The `prod` target ends with
    `chown -R www-data:www-data /var/www/var`, so every prod image built since the multi-stage
    build landed (2026-03-21) is correct — but on the server `/var/www/var` is the named volume
    `app_var`. Docker seeds an *empty* named volume from the image directory once, preserving
    ownership, and never re-seeds a populated one. The build-time `chown` has therefore had no
    effect since the day that volume was created, and **no rebuild can repair it**. That is what
    makes the deploy-script repair the fix rather than a stopgap.
  - **Twig's cache key was the trigger, not the upgrade.**
    `Twig\Environment::updateOptionsHash()` keys the template cache on `extensionSet signature :
    PHP_MAJOR_VERSION : PHP_MINOR_VERSION : Twig VERSION : debug : strictVariables`. Twig
    3.11 → 3.28 moved every template to a new path, so the two-character subdirectories had to be
    **created** instead of read — the first time ownership ever mattered. A PHP minor bump, any
    Twig upgrade, a new Twig extension or toggling `strict_variables` trips the same wire.
  - **The existing mitigation was disabled by the defect it was meant to prevent.** `cache:clear`
    has run as `www-data` since `c6d1cc8` (2026-07-13) precisely to keep `var/` writable, but as
    `www-data` it also could not delete the root-owned files.

  How `app_var` became root-owned is **not established** — either it was seeded that way or
  polluted later. Ruled out: the deploy's own warmup, per the dates above. The most likely route,
  a console or composer command run by hand as root, is closed below.

  Verified by deploy run `30756175163`, though not by the check that was written for it. The
  script runs under `set -eu` and the step is green, so every unguarded command in it exited 0 —
  including `cache:clear --env=prod` run **as www-data**. That is the discriminating result:
  clearing the cache as www-data is exactly what a root-owned `var/` breaks, since www-data
  cannot delete the root-owned files, and the failure would have turned the step red.

  The `mkdir`/`rmdir` probe and `find` line that shipped in `106bdb3` have since been removed:
  their `|| true` discarded the exit status, and the run shows **no remote stdout at all** — not
  compose progress, which is stderr, but the script's own `echo "Deploying IMAGE_TAG=…"` and
  `cache:clear`'s `[OK]` line are both absent. A failed probe was indistinguishable from a passing
  one. Worth remembering when debugging any future deploy: only exit codes cross back from the
  ssh step, so evidence has to be an unguarded command, not an `echo`.

  **The routes that could re-poison the volume are closed too.** Every `docker compose exec app …`
  defaults to root, so any console or composer command run by hand writes root-owned files into
  `var/`. `make db-migrate` now runs as `www-data`; `make prod-install` keeps composer and npm as
  root — they write `vendor/` and `node_modules/` — and hands `var/` back at the end; and
  `make fix-var-owner` is the standalone repair for anything else executed as root.

  Confirmed directly on the server afterwards: `find /var/www/var ! -user www-data -print` returns
  nothing, so the whole volume — uploads included — belongs to www-data, and `bin/console about
  --env=prod` boots as www-data (Symfony 7.4.15, PHP 8.3.33, prod cache 5.9 MiB). Both commands
  print normally when run by hand; the missing stdout is specific to the ssh-action's relay into
  the Actions log, not to the commands.

  Still untested: no deploy since has moved Twig's cache key, so the create-a-new-directory path
  that caused the outage is first exercised by the PHP 8.3 → 8.4 bump. `make prod-install` is also
  unexercised — its change is reasoned, not verified.

- **composer.json cleanup — all 5 entries resolved.** Committed 2026-08-02 as `8bf0f54`
  (`composer.json`, `composer.lock`, `symfony.lock`; no source changes). `dg/ftp-deployment`
  v3.5.2, `composer/package-versions-deprecated` 1.11.99.1 and `doctrine/annotations` ^2.0 are
  gone; `extra.symfony.require` and `symfony/proxy-manager-bridge` had already been cleared by the
  Symfony 7.4 upgrade. Removing `dg/ftp-deployment` took `phpseclib/phpseclib` 3.0.41 and its two
  `paragonie/*` dependencies with it, so `composer audit` now reports *No security vulnerability
  advisories found* — **4 advisories (2 high, 1 medium, 1 low) → 0**. The lock went 113 packages →
  107 (93 → 91 prod, 20 → 16 dev), and `composer validate` lost its only general warning (the exact
  version constraint on the Composer 1 shim).

  Three things this surfaced that were not obvious up front:
  - **`composer audit` without `--locked` does not read `composer.lock`.** It audits the installed
    set from `vendor/composer/installed.json`, which carries each package's abandoned flag as it
    stood at install time. `doctrine/cache` 2.2.0 is marked abandoned in the lock both before and
    after this change, and neither audit run surfaced it — only `composer audit --locked` does. The
    clean audit is therefore true of the default command, not of the dependency tree: one abandoned
    package remains, transitively via doctrine-bundle 2.12, which the doctrine-bundle item under
    Next should clear.
  - **`doctrine/annotations` was held open only by the root `require`.** Three locked packages
    reference it — `doctrine/doctrine-bundle`, `phpstan/phpdoc-parser`, `phpstan/phpstan-doctrine`
    — but all three list it under their *own* `require-dev`, which Composer never installs for
    dependencies. The reverse-dependency grep looks alarming and means nothing; deleting the root
    line genuinely uninstalled the package.
  - **Flex removed 6 `symfony.lock` entries, not 3** — it tracks transitive packages too, so
    `phpseclib/phpseclib` and both `paragonie/*` went with `dg/ftp-deployment` (113 → 107 entries).
    The `doctrine/annotations` recipe registers `./config/routes/annotations.yaml`, deleted long
    ago, so unconfigure was a no-op.

  Two of the three lived in `require`, not `require-dev`, so **the production image does change** —
  the next build drops `composer/package-versions-deprecated` and `doctrine/annotations`. Only
  `dg/ftp-deployment`, and with it the entire phpseclib advisory set, was dev-only and already
  absent from the image via `--no-dev`.

  Composer still cannot run under `docker compose exec` — the app container has no external DNS
  (see the Symfony 7.4 entry below). The removals were run in a one-off `docker run --dns 1.1.1.1`
  on the same app image, as before.

  Verified after the change: the lock diff is **removal-only** — nothing added, no retained package
  changed version, with `doctrine/lexer` 3.0.1 and `psr/cache` 3.0.0 correctly surviving on
  `doctrine/orm` and `symfony/cache`; `composer install --dry-run` reports *Nothing to install,
  update or remove*; `composer install --no-dev --dry-run` resolves; phpcs 66/66 with 0 errors,
  PHPStan `[OK] No errors`, `doctrine:schema:validate --skip-sync` OK; `/` → 302 and `/prihlaseni`
  → 200 with a rendered form and CSRF token; `bin/console about` boots in **prod** env (Symfony
  7.4.15, PHP 8.3.30); `autoload_classmap.php`, `autoload_psr4.php` and `autoload_static.php` carry
  zero dangling references.

  Also cleared: the orphaned **`FTP_PASSWORD` repository secret is deleted** — `gh secret list`
  returns 5 secrets, none FTP-related (checked 2026-08-02). Whether the underlying FTP account
  still exists, and whether its password was rotated, is not recorded here.

  Not exercised: no authenticated page was rendered, no test suite exists, and the production image
  has not yet been rebuilt against the reduced `require` set.

- **Upgraded MariaDB 11.5.2 → 11.8.8 LTS** — deployed to production 2026-08-02, no outage.
  Rehearsed locally first against a tarball snapshot of the dev `db_data` volume, restored
  between runs; the server was then recreated by hand, since `deploy.yml` only touches the
  `app` service.
  Corrected framing: 11.5.2's EOL was **2024-11-21**, not "~Aug 2025" — it had been
  unsupported for ~21 months. 11.8 LTS: GA 2025-06-04, EOL 2028-06-04. 12.3 LTS (GA
  2026-05-28) was deliberately skipped — two months post-GA at `.2` against 11.8's eight
  maintenance releases.

  Four things this surfaced that were not obvious up front:
  - **`doctrine.yaml`'s `server_version` is dead config whenever `DATABASE_URL` carries
    `serverVersion=`.** `ConnectionFactory::parseDatabaseUrl()` ends in
    `array_merge($params, $parsedParams)`, so the URL wins; `override_url: true` is unrelated
    (it only covers `dbname`/`host`/`port`/`user`/`password`). Proven by setting
    `server_version: 'mariadb-DELIBERATELY-INVALID'` — `dbal:run-sql`,
    `doctrine:schema:validate` and `schema:update --dump-sql` all still worked. The version
    that matters lives in `.env`, `.env.local` and `.env.prod.local`.
  - **The MariaDB entrypoint does not run `mariadb-upgrade` unless `MARIADB_AUTO_UPGRADE`
    is set.** Bumping the image alone logs `upgrade … required, but skipped due to
    $MARIADB_AUTO_UPGRADE setting`, then serves traffic normally while
    `/var/lib/mysql/mariadb_upgrade_info` still reads `11.5.2-MariaDB` — a half-upgraded
    database with no failure signal. The flag is now set on the `db` service.
    `MARIADB_DISABLE_UPGRADE_BACKUP` is deliberately left unset: the
    `system_mysql_backup_*.sql.zst` it suppresses is the only downgrade path.
  - **`innodb_snapshot_isolation` has defaulted ON since 11.6.2**, so this jump crosses it.
    Under REPEATABLE READ an `UPDATE`/`DELETE` on a stale snapshot now rolls the whole
    transaction back with `ERROR 1020 (Record has changed since last read)`. Measured both
    shapes against the upgraded server: reading *inside* the transaction reproduces 1020;
    Doctrine's `flush()` shape (reads in autocommit, transaction opened at write time) does
    not. `src/` has no `beginTransaction`, no `wrapInTransaction`, no `LockMode` and no
    optimistic locking — only 14 `flush()` calls — so the default stays ON. Any future code
    that wraps reads and writes in one explicit transaction becomes exposed; the escape hatch
    (`--innodb-snapshot-isolation=OFF`) would need a config mount that does not exist today.
  - **`make ci` would not have caught schema drift** — `Makefile:142` runs
    `doctrine:schema:validate --skip-sync`, which never compares against the live database.
    The un-skipped comparison was captured before and after and is byte-identical: mapping OK,
    database "not in sync", drift `DROP TABLE doctrine_migration_versions;` — the pre-existing
    baseline, since that table belongs to the migrations bundle and not the ORM mapping.

  Also verified: both `11.5.2` and `11.8.8` build the same DBAL platform
  (`MariaDB1010Platform`, `>= 10.10.0`) — measured by constructing a connection with each
  value, not just read off the driver — so no generated SQL changed. That equivalence is
  load-bearing: the developer machine's gitignored `.env.local` still declares `11.5.2`, so
  every check below ran with `11.5.2` declared against a genuine `11.8.8` server.
  The mixed collations
  (`utf8mb4_unicode_ci` on app tables, `utf8mb4_uca1400_ai_ci` on
  `doctrine_migration_versions`) are unchanged and not version-sensitive — doctrine-bundle
  hard-codes `utf8mb4_unicode_ci` regardless of server version, and `collation_server` was
  already `utf8mb4_uca1400_ai_ci` under 11.5. MariaDB's own upgrade guide lists exactly one
  incompatible change on this path, `wsrep_load_data_splitting` (Galera-only, N/A).
  On the upgraded local stack: `make cs` 0 errors, `make phpstan` 0 errors (result cache
  cleared), `make test-entity` OK, `/` → 302, `/prihlaseni` → 200 with a CSRF token, all 11
  tables present as InnoDB.

  Caveats: every figure above is from the local stack — the production run was carried out
  separately and reported successful, but its `mariadb_upgrade_info` / `mariadb --version`
  output is not recorded here. No authenticated page was rendered locally, so no household
  template was hit over HTTP against the upgraded database. The container entrypoint runs
  `mariadb-upgrade --upgrade-system-tables`, which skips the user-table check that step 7.2
  of MariaDB's upgrade guide describes — so the image bump is not a full `mariadb-upgrade`.

  Procedure for future database version bumps is kept out of this repo, as a local-only
  note under `docs/local/` (gitignored).

- **Upgraded Symfony 7.1 → 7.4 LTS** — deployed to production 2026-08-01. **The deploy caused a
  brief outage** — every request 500'd on an unwritable Twig cache directory. Not a defect in the
  upgrade: it exposed pre-existing root-owned `var/` on the server, tracked as its own item and
  fixed permanently 2026-08-02 (`106bdb3`, above). Resolved live at the time by clearing
  `var/cache/prod` and correcting ownership.
  `v7.1.3` → `v7.4.15`; 51 `symfony/*` packages on 7.4.x, **0 left on 7.1.x**, no `dev-` versions
  despite `minimum-stability: dev`. `bin/console about` now reports *Long-Term Support: Yes*,
  end of maintenance 11/2028, EOL 11/2029 — previously EOL 01/2025 (expired).
  Security advisories dropped **42 across 14 packages → 4 across 1** (`phpseclib`, dev-only; those
  last 4 went with the composer.json cleanup — see the entry above).

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
