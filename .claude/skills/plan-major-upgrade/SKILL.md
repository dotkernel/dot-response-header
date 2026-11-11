---
name: plan-major-upgrade
description: Build the written plan for the next major version of a Dotkernel PHP package, saved in `.claude/plan-code-review.md`. Use when asked to review a package and plan its next major release - code review with before/after fixes, phpstan level, workflow and PHP version bumps, PHPUnit upgrade, new docs folder, CHANGELOG and tests. Also use to re-check or update that plan after the maintainer changes `composer.json`.
---

# Plan a major upgrade

Produce one plan file, `.claude/plan-code-review.md`, and keep it current while the work is done.
The plan is the deliverable of the first pass. Make no other changes until the maintainer asks you to implement it.

## Ground rules

- **Measure before you claim.** Run the checks and reproduce each suspected bug with a failing test or a small script before it goes in the plan as a bug. Mark anything you could not run as "unverified" and say why (PHP version not installed, no coverage driver, lowest dependencies not installed).
- **Do not edit `composer.json` or install packages.** Edits are denied by the permission settings and the guard hooks block installs. Give the exact lines to change and ask the maintainer to apply them and run the install. Re-check the plan after every such change.
- **Do not circumvent a hook.** If a guard blocks a command, adapt (use the Edit and Write tools, a scratch directory) or ask.
- **Verify facts about other packages from the source**, for example `vendor/` or the published package listing, never from memory. A wrong claim in the plan becomes wrong docs.
- **Markdown is lint-clean.** Every `.md` file you write or edit must pass the maintainer's markdownlint config before you report it as done.

## Inputs to settle first

Ask only what you cannot determine from the repository.

| Item | How to find it |
| --- | --- |
| Next major version number and branch | Ask. Use the current branch name for the plan. Check `git tag`, `git describe --tags` and the remote branches for existing lines |
| Whether other release lines exist | Check branches and tags. A separate line (for example an attribute-based `5.x`) changes the upgrade guide and the README |
| Target PHP versions | From the maintainer or the CI workflow. Note which are installed locally |
| What the maintainer does by hand | Typically `composer.json`, `phpstan.neon` level and the workflow versions. Do not redo or revert them |

## Task list

The first eight items (T1 to T8) are the requested tasks. The rest were missed in the original list and should be part of every plan.

### Requested

- **T1. Recommend fixes for issues and bugs in the codebase.** Review `src/` for correctness, robustness, types and style. Give each finding an id (F1, F2, ...), a severity, a location and evidence from a reproduction.
- **T2. Add before/after sections** for every recommended update: current code or behaviour, recommended code or behaviour, and why. Cover code, config, docs and tests.
- **T3. `phpstan.neon` to level 8.** Record the current level, run PHPStan at level 8 and list every error with its identifier and fix. Do not propose ignores, baselines or `assert()` workarounds.
- **T4. Workflow versions.** Remove the old PHP version, add the new one, and set the action versions the maintainer named (for example `actions/checkout@v7`, `actions/cache@v6`, `codecov/codecov-action@v7`). Check every file in `.github/workflows` and `.laminas-ci.json`.
- **T5. `phpunit/phpunit` to the new major.** Say whether the maintainer has to do it (it edits `composer.json`) and list the PHPUnit changes the tests need: configuration schema, attributes instead of annotations, static data providers, mock and stub rules, `failOn*` flags.
- **T6. New docs folder for the next major version** in `docs/book`, copied from the most recent version. Keep the old version folders unchanged. List every page that needs content changes.
- **T7. CHANGELOG.md** with all missing releases and the new major release, following the file's section convention.
- **T8. Update the tests**, including new tests for each fixed bug.

### Recommended additions

- **T9. Baseline.** Before planning, run `composer check` and record the result, the PHP versions available locally, and the nearest reachable tag (`git describe --tags`). State what the Laminas backward-compatibility check will compare against.
- **T10. Dependency review** of `composer.json`: PHP constraint, each runtime requirement and its range, requirements that are used but undeclared, requirements that are declared but unused, dev dependencies, `suggest` and `conflict`. List exact edits for the maintainer.
- **T11. Dependency removal evaluation.** For any dependency that exists for one import, check whether a PSR interface can replace it and what that would break. Give options and a recommendation.
- **T12. Upgrade guide** (`upgrading.md`) for each older line, with a table of changes that can break code: change, symptom, fix.
- **T13. Discontinued lines.** If a release line is discontinued, add a page in that version's docs folder and point to the replacement package. Read the replacement's source or listing first.
- **T14. README, `docs/book/index.md`, `SECURITY.md` and `mkdocs.yml`.** Version table rows, badges, the supported versions table, `current_version`, `versions` and the navigation. Check whether `docs/book/index.md` is a symlink to the README.
- **T15. `phpunit.xml` and CI flags.** Fail on notices, warnings, deprecations and risky tests. Plan for deprecations raised in vendor code on the newest PHP version.
- **T16. Test quality.** Prefer fixtures and a real collaborator over mocks. Check for traps, for example an annotation reader parsing an `@Inject` mentioned in prose inside a docblock. List coverage gaps by branch, and suggest a coverage driver and mutation testing if none is installed.
- **T17. Documentation examples.** Run every PHP snippet and check every link and navigation entry. Fix examples that never worked.
- **T18. Integration check** outside the repository with the real dependencies (for example a SQLite entity manager and a PSR-6 pool), in a scratch directory.
- **T19. Lifecycle metadata.** Check `OSSMETADATA`, badges and branch names for the new version.
- **T20. Remove `laminas/laminas-servicemanager` when it is a runtime requirement.** Check the `require` section of `composer.json` (a `require-dev` entry or an absence means skip this item). If it is there, implement the removal as `dot-dependency-injection` does, and put each step in the plan with before/after sections:
    - Find every use: `grep -rni "laminas\|ServiceManager" src test`. Read `dot-dependency-injection` (`src/Factory`, `src/ConfigProvider.php`, `composer.json`) from its source, not from memory.
    - Make the factories plain classes with `__invoke(ContainerInterface $container, string $requestedName)` and only `Psr\Container` imports, so they work with any PSR-11 container that accepts a factory per service key.
    - Remove the classes that implement a Laminas interface, normally the abstract factory and its marker annotation. This is a breaking change: list it under Removed in the CHANGELOG and give a "replacing it" section in the upgrade guide, with before and after code.
    - Make `ConfigProvider` return `[]` instead of registering `abstract_factories`. Keep the class so applications that list it do not break.
    - Replace `ServiceManager` in the tests with a minimal PSR-11 fixture container (`get(string $id): mixed`, `has(string $id): bool`) and exception fixtures that implement `NotFoundExceptionInterface` and `ContainerExceptionInterface`. Class names must end in `Exception` for the coding standard.
    - Update the docs, README, version table and CHANGELOG. Say the factories work with any PSR-11 container, and that applications using Laminas Service Manager require it themselves.
    - Allow `psr/container` as `^1.1 || ^2.0`, and give the maintainer the `composer.json` edit: remove the requirement and reword the `description`.
    - Verify: no `Laminas` or `ServiceManager` left in `src` or `test`, and `composer check` passes. State that the lowest `psr/container` version is unverified unless it was installed.
- **T21. Unverified list.** End the plan with what could not be tested here and how the maintainer can test it in CI.

## Workflow

1. Read the repository: `src/`, `test/`, `composer.json`, `phpstan.neon`, `phpunit.xml`, `.github/workflows`, `.laminas-ci.json`, `docs/book`, `README.md`, `CHANGELOG.md`, `SECURITY.md`, `mkdocs.yml`.
2. Run the baseline (T9).
3. Review the code and reproduce each suspected bug (T1 and T2). Use scratch files outside the repository.
4. Run PHPStan at the target level (T3).
5. Review dependencies, workflows and documentation (T4 to T6, T10 to T15 and T20).
6. Write the plan with the structure below.
7. Lint the plan, then tell the maintainer what they have to do by hand.
8. On request, implement the plan in the rollout order, run `composer check` after each step, and update the plan's status table. After every `composer.json` change by the maintainer, re-run the checks and update the docs and CHANGELOG.

## Plan structure

Use these headings, in this order.

1. `# Plan: <package> <version>` with one paragraph of context (branch, version, date).
2. `## Implementation status`: a table of item, state and notes. Update it as work lands.
3. `## Decision: version <n>`: why this number, and how it relates to other release lines.
4. `## Baseline`: results of the commands you ran, and what you could not run.
5. `## Done by the maintainer`: the edits that were made by hand, and the exact edits still needed.
6. `## Findings`: one section per finding, each with Location, Evidence, Before, After and Why. Group as V1..Vn for version and tooling items, F1..Fn for code and test items.
7. `## Documentation`: a table of page and required change.
8. `## CHANGELOG`: the entries to add, by version.
9. `## Rollout order`: numbered steps, smallest safe change first.
10. `## Verification checklist`: checkboxes for every command and manual check, including the unverified list.

Use `Before` and `After` as fenced code blocks (`php`, `diff`, `yaml`, `json` or `text`), never an untagged fence.

## Checklist before reporting done

- Every finding has a reproduction or is marked unverified.
- Every dependency claim was read from `vendor/` or `composer.json`.
- The plan file passes markdownlint.
- The final message lists what the maintainer must do by hand and what could not be tested here.
