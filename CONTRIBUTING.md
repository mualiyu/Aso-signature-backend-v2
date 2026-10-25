# Contributing

Setup instructions are in the [README](README.md).

## Workflow

1. Branch off `main`. Use `feature/<short-name>`, `fix/<short-name>` or `chore/<short-name>`.
2. Keep each branch to one change. Small pull requests get reviewed faster.
3. Before you push:
   ```bash
   vendor/bin/pint            # code style
   php artisan test           # or the suite for the module you changed
   ```
4. If you changed storefront or admin CSS/JS, run `npm run build` in that package and commit the updated `public/themes/.../build` output.
5. Open a pull request into `main` with:
   - what changed and why
   - how you tested it (screenshots for UI changes)
   - any new `.env` variables, migrations, or admin configuration steps
6. At least one other developer reviews before merge. Don't push straight to `main`.

## Guidelines

- **Put new features in their own module** (`packages/Webkul/<Module>`), the way `Alteration` and `Designer` are built. Keep edits to the core modules small.
- **Add translation keys to `en` first** (`src/Resources/lang/en/app.php`). Don't hard-code user-facing text in views.
- **Migrations are append-only.** Never edit a migration that has already run in production. Add a new one.
- **No secrets in git.** New environment variables go in `.env.example` (empty or with a safe default) and the README's configuration table.
- **Write tests** for new behaviour where practical. See `packages/Webkul/Alteration/tests` for examples.

## Commit messages

Use the imperative mood and say what the change does, for example:

```text
Add delivered_at to orders and set it from DHL tracking
Fix PDP buy box spacing on mobile
```
