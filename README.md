# Inline Formats

Core-like Gutenberg inline formats for WordPress — PHP-configured, no settings UI, no admin notices, fully open-source, no premium upsells.

Apply visual styles (starting with **font-weight**) to a *selection* inside a paragraph, heading, or other rich-text field — not the whole block. Output is plain HTML:

```html
<span class="has-inline-font-weight" style="font-weight: 500;">just these words</span>
```

That markup works in classic WordPress themes and headless frontends (e.g. Next.js) that render block HTML as-is.

## Install paths

### 1. Composer (must-use plugin) — recommended

```bash
composer require cloakwp/inline-formats
```

Package type is `wordpress-muplugin`. With [`composer/installers`](https://github.com/composer/installers) configured, that installs to:

```
wp-content/mu-plugins/inline-formats/
```

(Your project may map that path differently — e.g. Bedrock uses `public/app/mu-plugins/`.)

**Important:** WordPress core only auto-loads PHP files directly in `mu-plugins/`. It does **not** load plugins nested in subdirectories like `mu-plugins/inline-formats/inline-formats.php`. You need an autoloader (or a tiny stub) for subdirectory must-use plugins.

**Recommended:** [Roots Bedrock Autoloader](https://github.com/roots/bedrock-autoloader) — it scans `mu-plugins/*/*.php` for plugin headers and includes them. Ships with [Bedrock](https://roots.io/bedrock/); usable in any WordPress project as `roots/bedrock-autoloader`. Once loaded, this package shows under **Plugins → Must-Use** (not the toggleable Plugins list).

**Without an autoloader**, add a one-line stub at the mu-plugins root:

```php
<?php
// wp-content/mu-plugins/inline-formats-loader.php
require WPMU_PLUGIN_DIR . '/inline-formats/inline-formats.php';
```

Optional fluent config in your theme `functions.php` (runs before the deferred default boot):

```php
use CloakWP\InlineFormats\InlineFormats;
use CloakWP\InlineFormats\Format\FontWeight;
use CloakWP\InlineFormats\Format\ToggleFormat;

InlineFormats::make()
  ->add(FontWeight::make()->weights([400, 500, 600, 700]))
  ->add(
    ToggleFormat::make('small-caps')
      ->title('Small caps')
      ->className('has-inline-small-caps')
      ->style('font-variant: small-caps')
  )
  ->register();
```

If you never call `register()`, the plugin bootstrap starts with defaults on `init` priority 1 (FontWeight with Light → Bold).

### 2. Traditional plugin install (download as a zip)

For sites that don’t use Composer — install it like any other WordPress plugin:

1. Open the [GitHub repository page](https://github.com/cloak-labs/cloakwp-inline-formats).
2. Click the green **Code** button, then **Download ZIP**.
3. Unzip the file. You’ll get a folder named something like `cloakwp-inline-formats-main`.
4. Rename that folder to `inline-formats` (optional but keeps the Plugins list tidy).
5. Install it in either way:
   - **WordPress admin:** Plugins → Add New → Upload Plugin → choose the zip (re-zip the renamed folder if you renamed it) → Install Now → Activate, **or**
   - **Manually:** upload the `inline-formats` folder into `wp-content/plugins/` on your server (via FTP/SFTP or your host’s file manager), then go to Plugins and click **Activate**.

Same defaults as the Composer path. Developers can still override config via fluent `register()` or the config filter (below). No mu-plugin autoloader needed.

## Fluent API

```php
InlineFormats::make()
  ->add(
    FontWeight::make()
      ->weights([400, 500, 600, 700])          // or [400 => 'Book', 500 => 'Medium']
      ->blocks(['core/paragraph', 'core/heading']) // optional
      // defaults: ->inToolbar() + replaces core Bold
      // ->inDropdown()   // overflow menu; keeps core Bold
      // ->keepBold()     // toolbar, but leave core Bold alone
  )
  ->add(
    ToggleFormat::make('small-caps')
      ->title('Small caps')
      ->style('font-variant: small-caps')
      ->inDropdown()                           // default for non-FontWeight formats
  )
  ->except(['cloakwp/font-weight'])            // remove a format by name
  ->register();
```

`add()` is keyed by format name — a second `FontWeight::make()` **replaces** the default. Use `->allCssWeights()` for the full 100–900 range.

### Toolbar placement

| Method | Where it shows | Notes |
|--------|----------------|-------|
| `->inToolbar()` | Block toolbar (main row) | FontWeight default; replaces core Bold unless `->keepBold()` |
| `->inDropdown()` | Formatting overflow (▾) | Default for `ChoiceFormat` / `ToggleFormat`; keeps core Bold |

When Font Weight sits on the toolbar it **replaces** the native Bold button — same “B” affordance, but a weight menu (Light → Bold) instead of a binary toggle. Semantic `<strong>` via Bold is intentionally dropped in that mode; use `->inDropdown()` or `->keepBold()` if you need both.

### Config filter

```php
add_filter('cloakwp/inline-formats/config', function ($config) {
  return $config->withFormat(
    \CloakWP\InlineFormats\Format\FontWeight::make()->weights([500, 700])
  );
});
```

(Prefer the fluent facade when you control boot order.)

## Architecture (Core + Plugin)

```
src/Core/      # Config, Format contract
src/Format/    # FontWeight, ChoiceFormat, ToggleFormat, InlineFormat
src/Plugin/    # Editor asset enqueue, kses style allowlist
InlineFormats.php  # Fluent facade
js/src/            # Generic registerFormatType engine (built → js/build/)
```

PHP owns format definitions. The JS bundle is a thin engine: it reads `window.inlineFormatsEditor.formats` and registers each with Gutenberg’s Format API. Adding a new format type is usually **PHP only**.

## Building editor assets

Prebuilt `js/build/` is committed so Composer/ZIP installs don't need Node.js. To rebuild:

```bash
pnpm install
pnpm run build
```

## Data model

Formats store styles as HTML attributes (`style="font-weight: 500"`) plus a marker class. No custom tables, no options UI that saves config to the database (configure via PHP).

Weight is visual (`font-weight`) and can nest with italic/links. Core Bold (`<strong>`) is unregistered by default when Font Weight sits on the toolbar; use `->inDropdown()` or `->keepBold()` to keep both.
