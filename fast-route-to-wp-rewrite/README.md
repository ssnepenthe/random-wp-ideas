# fast-route-to-wp-rewrite

Converts route syntax from `nikic/fast-route` to `[ $regex => $query ]` syntax used by WP_Rewrite.

## Usage

First install dependencies with `composer install`.

Then create a `Converter` instance and call the `to_wp_rewrite` method:

```php
$converter = new \FRWP\Converter;

var_export( $converter->to_wp_rewrite( '/books/{title:[a-z0-9-]+}' ) );

/**

array (
  '/books/([a-z0-9-]+)' => 'index.php?title=$matches[1]&matched_route=/books/([a-z0-9-]+)',
)

 */
```

You can set a prefix for all query vars:

```php
$converter = new \FRWP\Converter( 'pfx_' );

var_export( $converter->to_wp_rewrite( '/books/{title:[a-z0-9-]+}' ) );

/**

array (
  '/books/([a-z0-9-]+)' => 'index.php?pfx_title=$matches[1]&pfx_matched_route=/books/([a-z0-9-]+)',
)

 */
```

You can customize the additional `matched_route` query var with the `identify_routes_using` method:

```php
$converter = new \FRWP\Converter( 'pfx_' );
$converter->identify_routes_using( function( $regex, $query_array ) {
    // Closure is bound to converter instance so you have access to current prefix.
    return [ "{$this->prefix}hash", hash( 'md5', $regex ) ];
} );

var_export( $converter->to_wp_rewrite( '/books/{title:[a-z0-9-]+}' ) );

/**

array (
  '/books/([a-z0-9-]+)' => 'index.php?pfx_title=$matches[1]&pfx_hash=02329ff13b47562dc5332bef948208a5',
)

 */
```

You can add arbitrary additional query vars with the `resolve_additional_params_using` method:

```php
$converter = new \FRWP\Converter( 'pfx_' );
$converter->resolve_additional_params_using( function( $regex, $query_array ) {
    if ( ! some_condition() ) {
        return [];
    }

    // Closure is bound to converter instance. Params array is merged into parsed params array.
    return [ "{$this->prefix}status" => 'preorder' ];
} );

var_export( $converter->to_wp_rewrite( '/books/{title:[a-z0-9-]+}' ) );

/**

array (
  '/books/([a-z0-9-]+)' => 'index.php?pfx_title=$matches[1]&pfx_matched_route=/books/([a-z0-9-]+)&pfx_status=preorder',
)

 */
```

Because of the way fast-route works, each optional segment of the route will result in an additional rewrite rule:

```php
$converter = new \FRWP\Converter( 'pfx_' );

var_export( $converter->to_wp_rewrite( '/books/{title:[a-z0-9-]+}[/json[/{field:[a-z]+}]]' ) );

/**

array (
  '/books/([a-z0-9-]+)' => 'index.php?pfx_title=$matches[1]&pfx_matched_route=/books/([a-z0-9-]+)',
  '/books/([a-z0-9-]+)/json' => 'index.php?pfx_title=$matches[1]&pfx_matched_route=/books/([a-z0-9-]+)/json',
  '/books/([a-z0-9-]+)/json/([a-z]+)' => 'index.php?pfx_title=$matches[1]&pfx_field=$matches[2]&pfx_matched_route=/books/([a-z0-9-]+)/json/([a-z]+)',
)

 */
```

## Why?

Just a fun little experiment for now...