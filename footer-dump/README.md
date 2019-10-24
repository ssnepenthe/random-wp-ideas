# footer-dump

A wrapper around the Symfony VarDumper component which sends the output of the `dump` function to `wp_footer`.

## Usage

Composer is required.

Copy the `footer-dump` directory into your project and run `composer install`.

In your project, require the Composer autoloader and load the footer dump package:

```php
require_once __DIR__ . '/lib/footer-dump/vendor/autoload.php';

Footer_Dump\Package::load();
```

You will now have access to the `fd` function - it works in essentially the same way as the `dump` function except that the output is deferred until the `wp_footer` hook. Depending on your theme, custom styling may be required.
