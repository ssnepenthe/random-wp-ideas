# random-wp-ideas

Just a central repository where I can collect some random ideas for WordPress as they pop into my head.

## dump-server

A (rough) integration of the Symfony VarDumper dump server for non-framework sites. This is not a WP specific tool but could potentially be updated to include WP-specific context data and/or be reworked to run within WP-CLI.

## flush-free-rewrites

A very minimal abstraction over the rewrite API which allows for adding/removing rewrite rules at runtime without touching the database.

## footer-dump

A wrapper around the Symfony VarDumper component which sends the output of the `dump` function to `wp_footer`.

## hook-injection

An abstraction over the hook API which provides automatic injection of WordPress globals into hook callbacks.