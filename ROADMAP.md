# Roadmap

Planned changes for badi-menu, each verified against the codebase on **2026-07-16**.
These items document intent — they are not a work queue; do not start them unprompted
(see the Roadmap section in CLAUDE.md). Effort: **S** < 1 h, **M** = hours, **L** = days+.
This file is public: no secrets, server details, or private Docker-repo internals belong here.

## Now

Overdue maintenance and quick wins.

- **Move GitHub Actions off Node 20 runners** — **implemented 2026-07-30, awaiting verification.**
  Corrected framing: runners have defaulted to Node 24 since **2026-06-16**, so the node20-declared
  actions were already executing on Node 24; the hard failure is when Node 20 is **removed from the
  runner image in autumn 2026**. Four actions declared `runs.using: node20`
  (`actions/checkout@v4`, `webfactory/ssh-agent@v0.9.0`, `docker/login-action@v3`,
  `docker/setup-buildx-action@v3`); `docker/build-push-action@v7` was already Node 24, and
  `appleboy/ssh-action@v1` is a composite action with no Node runtime. All actions are now pinned
  to full commit SHAs with version comments, plus `.github/dependabot.yml` to keep them current.
  Also fixed: `build.yml`/`deploy.yml` shared a repo-wide concurrency group and cancelled each
  other; `:latest` now only moves on master; `deploy.yml` gained an `image_tag` input, a
  pre-flight image-existence check, and env-var passing instead of `${{ }}` interpolation into
  the remote script.
  **Verified so far:** static check only — all six pinned SHAs resolve and every Node action
  declares `node24`. **Still to do (human):** dispatch Build on a branch and confirm the
  deprecation warning count drops 8 → 0 (baseline: run `29268035747`, 8 warnings = 4 actions ×
  main + post), the private submodule still checks out, and `:latest` does not move; then deploy.
  **Not yet active:** `deploy.yml` references a `SERVER_FINGERPRINT` secret for SSH host-key
  verification, but the secret does not exist — it resolves to empty and the action skips host-key
  checking, i.e. unchanged from before. See the comment block in `deploy.yml` for how to populate
  it from the server. Effort: S.

- **Upgrade Symfony 7.1 → 7.4 LTS** — **overdue**: 7.1 security fixes ended July 2025.
  Status: on v7.1.3. Evidence: `composer.lock` (`symfony/framework-bundle`), `composer.json`. Effort: M.

- **Upgrade MariaDB 11.5 → 11.8 LTS** — **overdue**: 11.5 is a short-term release, out of
  support since ~Aug 2025. Status: pinned to `mariadb:11.5.2`. Evidence: `docker-compose.yml`. Effort: M.

- **composer.json cleanup** — remove or fix stale entries: `extra.symfony.require` stuck at
  `"5.2.*"` (installed Symfony is 7.1.x); `dg/ftp-deployment` (dead — deploys are SSH/Docker via
  `deploy.yml`); `symfony/proxy-manager-bridge ^6.4` (abandoned, no Symfony 7 release);
  `composer/package-versions-deprecated` (Composer 1 shim); `doctrine/annotations` (entities use
  attributes — `type: attribute` in `config/packages/doctrine.yaml`, no annotation imports in `src/`).
  Status: all still present. Evidence: `composer.json`, `composer.lock`. Effort: S.

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

- **PHPStan 1.x → 2.x** — phpstan 1.11.10 plus 1.x extensions (doctrine, symfony, strict-rules);
  bump `slevomat/coding-standard ^7` (current major is 8) alongside.
  Status: on 1.x. Evidence: `composer.json` require-dev, `dev/phpstan.neon`. Effort: M.

- **PHP 8.3 → 8.4/8.5** — PHP 8.3 is security-only since January 2026. Requires a runtime-image
  rebuild in the private Docker config repo (details live there).
  Status: `"php": ">=8.3"`. Evidence: `composer.json`. Effort: M.

- **Review GHCR package visibility** — the prod image is anonymously pullable; decide whether
  that is intended, given it is built from the private Docker config.
  Status: public as of 2026-07-16. Evidence: `ghcr.io/t-ror/badi-menu` (anonymous pull). Effort: S.

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

- **TLS renewal confirmed** — live certificate reissued 2026-07-10, valid to 2026-10-08
  (checked 2026-07-16); the ~June 2026 renewal concern is resolved.
- **GHCR prod image verified as remote backup** — registry lists `latest` plus recent commit-SHA
  tags including HEAD (`c6d1cc8`), checked 2026-07-16. Visibility review tracked under Next.
- **PWA webmanifest + icons** — shipped in `c21a29f`.
- **Image build/deploy pipeline fixes** — `4a8b277`, `6c82f50`, and `c6d1cc8` (post-deploy
  `cache:clear` in `deploy.yml`, fixing stale compiled Twig after deploys — verified live 2026-07-16).
