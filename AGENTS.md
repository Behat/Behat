# Repository Guidelines

## Project Structure & Module Organization
- Core PHP code lives in `src/Behat/...`, grouped by component (Context, Definition, Output, Tester).
- Feature specifications reside in `features/`; 
- Unit and integration tests live in `tests/Behat/Tests` with additional fixtures in `tests/Fixtures`.
- Shared configuration defaults (`behat.yml.dist`, `phpunit.xml.dist`, `phpstan.dist.neon`, `rector.php`) sit at the repository root

## Build, Test, and Development Commands
- Run `composer install` after cloning to install dependencies.
- `composer behat` exercises Behat features with rerun support;
- use `composer behat-progress` for concise output.
- `composer phpunit` checks unit tests;
- `composer phpstan` runs static analysis.
- `composer cs` performs a dry-run style check, while `composer cs-fix` applies formatting.
- `composer rector` and `composer rector-fix` help with automated refactors.
- Use `composer all-tests` before submitting to run the full validation pipeline.

## Coding Style & Naming Conventions
- We follow PSR-12 with 4-space indentation and strict type hints when available.
- Name new PHP classes using StudlyCase and place them in PSR-4 paths that mirror namespace depth under `src/Behat/...`. 
- Scenario files in `features/` use kebab-case descriptors (e.g. `list-file.feature`) and descriptive scenario titles.
- Before committing, run `composer cs` and `composer rector --dry-run`; never commit formatting noise without the functional change.

## Testing Guidelines
- Every user-visible change needs a covering scenario under `features/` or an updated specification;
- Keep steps declarative and reuse contexts from `features/bootstrap`.
- If needed, add or adjust PHPUnit tests in `tests/Behat/Tests`, naming classes with the `Test` suffix and methods with `test...`
- Prefer Behat integration tests to unit tests
- Run targeted suites (`composer behat`, `composer phpunit`) during development and finish with `composer all-tests`.
- Maintain green static analysis by addressing `composer phpstan` findings before raising a PR.

## Commit & Pull Request Guidelines
- Write commits in imperative mood with short prefixes seen in history (`fix:`, `test:`, `dx:`) followed by a concise summary under ~60 characters.
- Separate formatting or fixture shuffles into their own commit when they aid review.
- Pull requests must describe the motivation, note configuration changes, and link any related issues (`Fixes #123`).

## Backward Compatibility & Releases
- Behat honors semantic versioning; changes to public classes, interfaces, or services must remain backward compatible or provide shims. 
- Do not touch `BehatApplication::VERSION` and document BC layers in the PR narrative.
- When introducing extensibility points, update relevant feature guides or docblocks so downstream extensions remain stable.
