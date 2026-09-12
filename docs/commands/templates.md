---
title: "Custom templates"
description: "Make new files start with your team\u2019s conventions."
---

# Custom templates

Copy the package templates from `vendor/fahadmayow/exert/src/Console/stubs/` into these application paths:

```text
stubs/action.stub
stubs/action-controller.stub
```

The generators prefer those files when present. Keep the `{{ namespace }}` and `{{ class }}` placeholders. Overrides affect future generated files only.

## A useful customization

If most of your operations are writes and do not use prediction, your application stub might start with:

```php
protected array $methods = ['POST'];

protected bool $precognition = false;
```

Keep the base class and a public `handle()` method. Use the singular `middleware()` method if your template includes it.

This changes the files generated in your application. It does not change Exert's defaults for actions that inherit directly from the base class.

## Review before using force

A changed template does not update older actions. Regenerating with `--force` overwrites their files, including work already written there. Update existing actions deliberately instead of using a template refresh as a migration tool.
