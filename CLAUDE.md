# CLAUDE.md

Guidance for Claude Code when working in this repository.

## Project Overview

**badi-menu** is a meal-planning web application (households, meals, ingredients, weekly menus), originally a bachelor's thesis project, now used as a modernization playground. Live at https://badimenu.com.

This is a **public repository**. Never add secrets, credentials, server addresses, or infrastructure identifiers to any committed file — including this one.

## Roadmap

Planned and deferred changes are tracked in `ROADMAP.md` (sections Now / Next / Later / Done). Consult it before proposing any modernization, upgrade, or dependency work — it records what is already planned, verified, and deliberately deferred. Never start roadmap items unprompted; they are documentation of intent, not a work queue.

## Tech Stack

- **Backend:** PHP 8.3, Symfony 7.1, Doctrine ORM 3, Doctrine Migrations
- **Database:** MariaDB 11.8.8 (LTS)
- **Frontend:** Twig templates with Vue 2 components (`assets/`), built by Webpack Encore (single entry: `assets/app.js`), Bootstrap 5, Font Awesome, jQuery/Select2/Quill (legacy)
- **Infrastructure:** Docker (single app container running nginx + PHP-FPM via supervisord), MariaDB in a separate container
- **CI/CD:** GitHub Actions building images to GHCR (`ghcr.io/t-ror/badi-menu`)

## Repository Layout

```
assets/          Frontend source (JS, Vue components, CSS)
bin/             Symfony console
config/          Symfony config (env-specific via when@dev / when@prod / when@test blocks)
dev/             QA tooling config: phpstan.neon, ruleset.xml (phpcs)
docker/          Git SUBMODULE → private repo t-ror/docker-badi-menu (Dockerfile, nginx/supervisor config)
migrations/      Doctrine migrations, organized by year/month (migrations/YYYY/MM/)
public/          Web root; public/build/ is generated (gitignored)
src/             Application code (PSR-4: App\)
tests/           PHPUnit bootstrap (no test suite yet)
translations/    Symfony translations
```

`src/` organization: `Controller/`, `Entity/` (+ `Entity/Collection/`), `Repository/`, `Service/` (grouped by domain, e.g. `Service/Household/`, `Service/Security/`), `Type/` (Symfony form types), `Component/`, `Event/`, `EventListener/`, `EventSubscriber/`, `Exception/`, `Security/`, `Twig/`, `Utils/`, `ValueObject/`. Place new code in the matching directory; group new domain services under `Service/<Domain>/`.

## Docker & Environments

The `docker/` submodule provides a **multi-stage Dockerfile** with two targets:

- **`dev`** — no baked dependencies; app code is volume-mounted; Xdebug installable via `INSTALL_XDEBUG` build arg; nginx config selected via `NGINX_CONFIG` build arg.
- **`prod`** — self-contained image; app source pulled in at build time via Docker `additional_contexts` (`app-src`); no source volume mounts at runtime.

Compose files:

| File | Purpose |
|------|---------|
| `docker-compose.yml` | Dev base (dev target, code volume mount, MariaDB) |
| `docker-compose.override.yml` | Dev-only extras (publishes DB port to host). **Must never exist on a server** — a stray override once exposed the DB port publicly. |
| `docker-compose.ci.yml` | CI image build (prod target + `app-src` context) |
| `docker-compose.prod.yml` | Prod overrides: GHCR image pinned by `IMAGE_TAG` (commit SHA), no source mounts, no published DB port |

Architectural decisions (do not "fix" these):
- nginx + PHP-FPM intentionally live in **one container** under supervisord — deliberate choice based on real-world health-check pain with split containers; do not propose splitting them.
- App repo and Docker config repo are intentionally **separate**, joined via the submodule. Do not inline the Dockerfile into this repo.
- Server-specific production values (ports, env vars, volume mounts) live on the server, not in this repo.

## Development Workflow

The **Makefile is the source of truth** for commands. It auto-detects whether you are inside the container (`/.dockerenv`) and prefixes `docker compose exec app` when outside; `RAW=1` forces host-mode execution.

```bash
make up            # start containers (clears cache first)
make down          # stop containers
make exec          # shell into the app container
make install       # full dev setup: composer, migrations, npm, Encore build
make migration     # doctrine:migrations:diff (creates migrations/YYYY/MM/)
make migration-empty
make db-migrate    # run migrations
make ci            # QUALITY GATE: phpcs + PHPStan + doctrine:schema:validate
make cs            # phpcs only (dev/ruleset.xml)
make phpstan       # PHPStan only (dev/phpstan.neon)
make test-entity   # doctrine:schema:validate --skip-sync
make hot-reload    # Encore watch mode
make production    # one-off Encore production build
```

The app is served at `http://localhost:${APP_PORT:-8080}` in dev.

## Quality Gates

**Run `make ci` after any PHP change.** It must pass before work is considered done:

1. **phpcs** — coding standard from `dev/ruleset.xml`
2. **PHPStan level 8** with `phpstan-strict-rules`, Doctrine and Symfony extensions (`dev/phpstan.neon`)
3. **Doctrine schema validation** — entity mappings must stay consistent

PHPUnit is configured (`phpunit.xml.dist`) but there is no test suite yet; do not claim tests were run. If asked to add tests, put them under `tests/` in the `App\Tests\` namespace.

## Coding Standards (PHP)

- Every PHP file starts with `<?php declare(strict_types = 1);` on the opening line (note the spaces around `=`).
- **Tabs for indentation** (enforced by phpcs), not spaces.
- **Allman/BSD braces**: opening brace on its own line for classes and functions.
- Strict OOP: typed properties, typed parameters and return types everywhere; PHPStan level 8 + strict rules must pass.
- Short array syntax, camelCase methods, UPPER_CASE constants, no inline HTML in PHP files, no Perl-style `#` comments.
- Follow the modified-PSR2 + Slevomat ruleset in `dev/ruleset.xml`; when unsure, match the style of neighboring files.
- Value objects go in `ValueObject/`; custom exceptions in `Exception/`.

## Frontend Conventions

- Frontend is intentionally **Vue 2 embedded in Twig** via Webpack Encore. Do not introduce Vue 3, Vite, SPA routing, or API-first patterns — the Vue 3 SPA migration is tracked in `ROADMAP.md` (Later) and is out of scope until explicitly started.
- Single Encore entry `assets/app.js`; register new Vue components there.
- Build with `make production` (or `make hot-reload` while developing).

## Configuration & Secrets

Env layering (Symfony dotenv):

- `.env` — committed defaults (dev-safe values only)
- `.env.local` — developer machine secrets (gitignored)
- `.env.prod` — committed prod defaults with placeholders only
- `.env.prod.local` — real prod secrets, exists only on the server (gitignored)
- `.env.test` — test environment

Rules:
- Never write real credentials into any committed `.env*` file. Placeholders only.
- Shell-style interpolation (`${VAR}`) does **not** resolve across env-file layers — set composite values like `DATABASE_URL` explicitly rather than composing them from other variables.
- Environment-specific Symfony config uses `when@dev` / `when@prod` / `when@test` blocks **within a single file**. Do not create duplicate keys across `config/packages/` and `config/packages/<env>/` — duplicated `security.access_control` previously broke the app.
- PHP-FPM runs with `clear_env=yes`; any new environment variable the app needs at runtime must also be explicitly passed through in the FPM config (docker submodule).

## Database & Migrations

- Migrations are generated into `migrations/YYYY/MM/` (the Makefile creates the folder). Keep that layout.
- Never edit an already-committed migration; create a new one.
- Schema changes: modify entities → `make migration` → review the generated SQL → `make db-migrate` → `make ci` (schema validation is part of the gate).
- Uploaded files and `var/` are owned by `www-data` (UID 33) in containers; keep that in mind for anything touching the filesystem.

## Git & CI/CD Conventions

**Commit message prefixes are functional, not cosmetic.** `build.yml` only builds and pushes an image on push to `master` when at least one commit message starts (case-insensitively) with:

- `feature:` — new functionality
- `fix:` — bug fixes
- `refactor:` — restructuring without behavior change

Commits without these prefixes (docs, chores) intentionally skip the image build. `workflow_dispatch` always builds.

Pipelines:
- **`build.yml`** — checks out with the private `docker/` submodule (deploy-key SSH), builds the `prod` target via Buildx with GHA cache, pushes `ghcr.io/t-ror/badi-menu:<sha>` and `:latest`.
- **`deploy.yml`** — manual dispatch only; SSHes to the server, pulls the SHA-pinned image, restarts the app service, clears the Symfony cache as `www-data` (because `var/` persists across deploys), prunes old images.

Submodule flow: changes to Docker config happen in the private `docker-badi-menu` repo; then bump the submodule pointer here (`git submodule update --remote docker`, commit the pointer).

## Instructions for Claude Code

- **Never commit or push unless explicitly asked.** Default to leaving changes staged/unstaged for review.
- Prefer small, incremental, reviewable changes over large rewrites.
- After PHP changes, run `make ci` and fix everything it reports.
- Do not modify `.env*` files, GitHub workflow secrets references, or the `docker/` submodule contents from this repo.
- Do not add server addresses, usernames, or credentials anywhere — this repo is public.
- Respect the architectural decisions above (single container, separate docker repo, Vue 2 status quo) instead of "modernizing" them unprompted.
- When adding dependencies, justify them; this project deliberately stays lean.