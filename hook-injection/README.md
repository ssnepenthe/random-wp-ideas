# hook-injection

An abstraction over the hook API which provides automatic injection of WordPress globals into hook callbacks.

## Usage

Composer is required - start by running `composer install` and include `vendor/autoload.php` somewhere early in your project.

Then instead of this:

```php
\add_action( 'wp_head', function() {
    global $wp;

    ?><!-- Matched rule: <?php echo $wp->matched_rule; ?> --><?php
} );
```

You can do this:

```php
\Hook_Injection\on( 'wp_head', function( $wp ) {
    ?><!-- Matched rule: <?php echo $wp->matched_rule; ?> --><?php
} );
```

Same thing for filters - instead of this:

```php
\add_filter( 'template_include', function( $template ) {
    global $wp_rewrite;

    if ( \some_rewrite_dependent_conditional( $wp_rewrite ) ) {
        return '/path/to/a/template.php';
    }

    return $template;
} );
```

Do this:

```php
\Hook_Injection\on( 'template_include', function( $template, $wp_rewrite ) {
    if ( \some_rewrite_dependent_conditional( $wp_rewrite ) ) {
        return '/path/to/a/template.php';
    }

    return $template;
} );
```

The first `n` args are always those supplied by WordPress - everything after comes from `$GLOBALS`.

Similar to `\add_filter`, `\Hook_Injection\on()` accepts optional third and fourth parameters:

* `$priority` - default 10
* `$accepted_args` - default 1

```php
\Hook_Injection\on( 'style_loader_src', function( $src, $handle, $wp_query ) {
    // ...
}, 25, 2 );
```

Note that the example above sets `$accepted_args` to 2 instead of 3 - this should always match the number of args provided by WordPress and not include any args injected from `$GLOBALS`.

Alternatively, you can resolve dependencies via type hint:

```php
\Hook_Injection\on( 'wp_head', function( WP_Object_Cache $cache ) {
    // ...
} );
```

## How It Works

There is nothing particularly complex going on here... First we require the `php-di/invoker` package. From there it is as simple as wrapping hook callbacks in a closure that uses `php-di/invoker` to invoke the desired callable.

Parameters are resolved in the following order:

* The first `n` args are resolved using the args passed by the WordPress hook system
* Remaining args with type hints are resolved next
* Any args that haven't been resolved are then matched by name of their key in the `$GLOBALS` array
* Lastly any unresolved args that have a default value will use that default value

## When/Why To Use It

Most likely for stylistic reasons??? It might simplify tests in some cases.

There is probably a bit of a tradeoff in using this as it most likely adds a bit of unnecessary complexity on the debugging front...

## Ideas For Further Development

* Resolve dependencies from a PSR container instance - container should be configurable