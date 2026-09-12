---
title: "Package development"
description: "Run the package checks and keep framework behavior covered."
---

# Package development

These commands are for this package's checkout, not the consuming application's test suite:

```bash
composer install
composer validate --strict
composer test
```

Run a focused test when needed:

```bash
composer test -- --filter=ActionContextualBindingTest
```

The PHP suite uses Orchestra Testbench to boot Laravel. It covers dispatch, configuration, middleware, route caching, generators, action listing, and Precognition. Generator tests use temporary directories. A separate application or database server is not required for the package suite.

The main GitHub Actions workflow tests the lowest and latest dependency boundaries for Laravel 11, 12, and 13 on their supported PHP versions. Precognition context handling depends on protected Laravel container internals, so keep those regression tests when changing supported framework versions.

An exhaustive compatibility workflow discovers every stable Laravel patch release in a requested major range and runs the complete suite once per release. Patch releases are grouped into minor-line jobs to stay within GitHub Actions' matrix limit, but every exact version is installed and tested. Start it from GitHub Actions with a range such as `11.x-13.x`, or use GitHub CLI:

```bash
gh workflow run laravel-compatibility.yml -f laravel_range=11.x-13.x
```

The workflow also runs weekly for the full supported range. Historical patch jobs disable Composer's insecure-package blocking inside isolated CI because their purpose is compatibility testing, not selecting dependencies for production.

The separate release-helper tests can be run with Node.js:

```bash
node --test tests/release.test.cjs
```

They mock publication; they do not create a release. The interactive `release.js` helper is for maintainers and can publish a GitHub release. Application users do not need it.

## Documentation development

The documentation uses the VitePress installation in this checkout. You can run it directly with Node.js:

```bash
node node_modules/vitepress/bin/vitepress.js dev docs
node node_modules/vitepress/bin/vitepress.js build docs
node node_modules/vitepress/bin/vitepress.js preview docs
```

The build checks internal links and writes the static output to `docs/.vitepress/dist`. The site uses local search, so no search account or API key is needed.

When editing examples, distinguish complete files from method excerpts and list any application models, policies, or services they assume. Try the beginner example without a database to catch accidental setup requirements.

## Release notes

Keep release behavior separate from application setup. A consuming Laravel application needs the package and its provider, not this repository's release helper.

The package is MIT-licensed. Read the [license](https://github.com/fahadmayow/exert/blob/main/LICENSE) in the repository for the full terms.
