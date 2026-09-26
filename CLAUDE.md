# CLAUDE.md

Pienyrityksen taloushallinto: a small PHP + MariaDB bookkeeping app (transactions, VAT, quarterly and tax reports, CSV export). It is also a teaching repo for GitHub issue/branch/PR workflow — see `opiskelijanohje.md` and `tehtava.md`.

## Stack
- PHP 8.5 on Apache, plain PHP pages in `src/` (no framework), PDO for DB access
- MariaDB 11.4, schema and seed data in `schema.sql` (loaded on first DB start)
- Tailwind CSS 4: edit `src/assets/tailwind.input.css`, output is `src/assets/tailwind.css` (generated, gitignored — don't hand-edit or commit)
- Shared layout in `src/partials/header.php` and `src/partials/footer.php`
- Project docs and UI text are in Finnish; keep user-facing text in Finnish

## Running
- `docker compose up -d --build` → app at http://localhost:8080, phpMyAdmin at http://localhost:8081 (the `css` service builds Tailwind once before `web` starts)
- `docker compose --profile dev up -d` also starts the Tailwind watcher
- `npm run build:css` rebuilds Tailwind once (needs Node on host)
- Schema changes: `schema.sql` only runs on a fresh DB — `docker compose down -v` then up again
- No automated tests yet; verify changes by running the app and checking affected pages

## Code conventions
- Always use PDO prepared statements; never interpolate request data into SQL
- Escape all output with `htmlspecialchars()` (XSS)
- Money is `DECIMAL`; amounts are VAT-inclusive and `vat_amount = amount * rate / (100 + rate)` (see `src/add_transaction.php`)

## GitHub workflow
- Work from an issue: one branch per issue, named `issue-<number>-<short-description>` (e.g. `issue-5-haku-ja-suodatus`)
- Branch from an up-to-date `main`; never commit directly to `main`
- Commit messages: `WIP: issue #N ...` for work in progress, `Fix #N: ...` for the final commit
- PR into `main`, and put `Fixes #N` in the PR description so the issue closes on merge
- Keep PRs focused on a single issue
