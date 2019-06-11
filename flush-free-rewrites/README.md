# flush-free-rewrites

A very minimal abstraction over the rewrite API for adding/removing rewrite rules at runtime without touching the database.

## Usage

Require the `bootstrap.php` file from an early hook:

```php
add_action( 'muplugins_loaded', function() {
    require_once __DIR__ . '/lib/flush-free-rewrites/bootstrap.php';
} );
```

You will now have access to the `Flush_Free_Rewrites\add_rewrite_rule` function which can be used in exactly the same way as the core `add_rewrite_rule` function:

```php
add_action( 'init', function() {
    Flush_Free_Rewrites\add_rewrite_rule( '^leaf/([0-9]+)/?', 'index.php?page_id=$matches[1]', 'top' );
} );
```

Rules are automatically merged at runtime so you never have to call `flush_rewrite_rules`.

## How It Works

It is actually very simple:

1. Collect all rules in a central registry
2. Filter `rewrite_rules_array` to ensure that rules are added when WordPress generates the rewrite rules array
3. Filter `option_rewrite_rules` to ensure that rules are added when WordPress retrieves the rewrite rules array from the database
4. Filter `pre_update_option_rewrite_rules` to ensure that none of the rules get saved to the database rewrites are persisted by WordPress

## When/Why To Use It

This is mostly intended for situations where you have some rewrite-dependent functionality that can be toggled at runtime (e.g. via plugin settings, filter, etc.).

## Ideas For Further Development

* Automatically parse and add new query vars as rules are added
    - Would need manual overrides
    - Might be nice to further support QV transformers and casts
    - Maybe even validators?
* Support common tasks for matched rules
    - Add/remove body classes
    - Reset global query flags
    - Modify page title
    - Load a template
    - Override canonical redirects
    - Preempt 404s
    - Plus hooks/callbacks to override any preset behavior defined above
* Support route handlers/controllers
    - Handlers are automatically triggered for matched rules
    - Matching can be done based on matched rewrite rule as well as request method
    - Bonus points for providing an auth hook/callback before invoking handler
    - Could be nice to define a response interface with implementations for common use cases:
        + HTML
        + JSON
        + redirect
        + etc.
    - Maybe provide some sort of autowiring capability to handlers?
    - Maybe support some sort of automatic callable resolution, e.g. from PSR container interface?
    - Maybe support easy error triggers - HTTP specific exceptions (e.g. `throw new Http_Not_Found_Exception`)?
