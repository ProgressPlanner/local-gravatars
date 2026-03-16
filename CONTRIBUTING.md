# Contributing to Local Gravatars

Thanks for your interest in improving Local Gravatars.

## Before you start

- Check existing issues and pull requests before opening a new one.
- For security issues, please do **not** open a public issue. See [SECURITY.md](SECURITY.md).
- Keep changes focused. Small, targeted pull requests are easier to review and merge.

## Local development

1. Clone the repository.
2. Put the plugin in your local WordPress installation's `wp-content/plugins/` directory.
3. Activate **Local Gravatars**.
4. Test avatar output on pages that use `get_avatar()`.

## What to include in a pull request

Please include:

- a clear summary of the change
- why the change is needed
- any relevant screenshots or output if behavior changes
- testing notes, especially around avatar URLs, fallback behavior, and cleanup

## Coding expectations

- Follow WordPress coding standards.
- Keep the plugin lightweight and easy to read.
- Preserve backwards compatibility where possible.
- Avoid adding UI or settings unless there is a strong product reason.
- When adding filters or hooks, document them clearly.

## Good contribution ideas

- bug fixes and compatibility improvements
- performance or filesystem safety improvements
- documentation improvements
- better test coverage or reproducible bug reports

Thanks for helping make Local Gravatars better.
