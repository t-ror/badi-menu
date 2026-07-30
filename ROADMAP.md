# Roadmap

Planned changes for badi-menu, each verified against the codebase on **2026-07-16**.
These items document intent — they are not a work queue; do not start them unprompted
(see the Roadmap section in CLAUDE.md). Effort: **S** < 1 h, **M** = hours, **L** = days+.
This file is public: no secrets, server details, or private Docker-repo internals belong here.

## Now

Overdue maintenance and quick wins.

- **Move GitHub Actions off Node 20 runners** — **overdue** (deprecation deadline was June 2026).
  `actions/checkout@v4` runs on Node 20 (v5+ runs on Node 24); audit `webfactory/ssh-agent@v0.9.0`,
  `docker/login-action@v3`, `docker/setup-buildx-action@v3`, `appleboy/ssh-action@v1` against
  current majors, and fix stale version comments (`docker/build-push-action@v7 # v6.18.0`).
  Status: not started. Evidence: `.github/workflows/build.yml`, `.github/workflows/deploy.yml`. Effort: S.

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
