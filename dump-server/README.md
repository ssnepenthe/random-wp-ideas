# dump-server

A (rough) integration of the Symfony VarDumper dump server for non-framework sites. This is not a WP specific tool but could potentially be updated to include WP-specific context data and/or be reworked to run within WP-CLI.

## Usage

Composer is required.

Copy the `dump-server` directory into your project and run `composer install`.

In your project, require the Composer autoloader and load the dump server package:

```php
require_once __DIR__ . '/lib/dump-server/vendor/autoload.php';

Dump_Server\Package::load();
```

You now have access to the `dump` function from the Symfony VarDumper component - it is configured to output data as usual until the dump server has been started.

To start the dump server, run the `var-dump-server` command (e.g. `./lib/dump-server/vendor/bin/var-dump-server`.

Once it is running, the output of calls to the `dump` function will appear in the terminal.
