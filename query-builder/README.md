# query-builder

A very simple bridge between wpdb and the latitude query builder package.

## Usage

Start with the [latitude docs](https://latitude.shadowhand.me/).

From there you can create query objects using the following factory functions:

```php
QueryBuilder\Second\select( ...$columns )
QueryBuilder\Second\selectDistinct( ...$columns )
QueryBuilder\Second\insert( $table, array $map = [] )
QueryBuilder\Second\delete( $table )
QueryBuilder\Second\update( $table, array $map = [] )
```

Each of these returns a latitude query object of the corresponding type wrapped in a `QueryBuilder\Second\DbAwareQueryProxy` instance. The proxy class forwards method calls to either the global `wpdb` instance or the wrapped query instance as appropriate.

There is also a `QueryBuilder\Second\table` function which is used to properly prefix a table name - example usage can be seen below.

Prior to actually querying the database, all queries are appropriately escaped by latitude and then run through `$wpdb->prepare`.

Beyond that, I will try to explain by adapting examples from the codex...

### Select A Variable

```php
global $wpdb;

$user_count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->users" );
```

Becomes:

```php
use function Latitude\QueryBuilder\func;
use function QueryBuilder\Second\select;
use function QueryBuilder\Second\table;

$user_count = select( func( 'COUNT', '*' ) )
    ->from( table( 'users' ) )
    ->getVar();
```

Or the alternate syntax:

```php
use function Latitude\QueryBuilder\func;
use function QueryBuilder\Second\db;
use function QueryBuilder\Second\select;
use function QueryBuilder\Second\table;

$user_count = db()->getVar(
    select( func( 'COUNT', '*' ) )->from( table( 'users' ) )
);
```

For the remaining examples I will only show the primary syntax (which is what I prefer), but the alternate syntax will work just the same.

### Select A Row

```php
global $wpdb;

$mypost = $wpdb->get_row(
    $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE ID = %d", 1 )
);
```

Becomes:

```php
use function Latitude\QueryBuilder\field;
use function QueryBuilder\Second\select;
use function QueryBuilder\Second\table;

$mypost = select( '*' )
    ->from( table( 'posts' ) )
    ->where( field( 'ID' )->eq( 1 ) )
    ->getRow();
```

### Select Generic Results

```php
global $wpdb;

$fivesdrafts = $wpdb->get_results(
    $wpdb->prepare(
        "
            SELECT ID, post_title
            FROM $wpdb->posts
            WHERE post_status = %s
                AND post_author = %d
        ",
        'draft',
        5
    )
);
```

Becomes:

```php
use function Latitude\QueryBuilder\field;
use function QueryBuilder\Second\select;
use function QueryBuilder\Second\table;

$fivesdrafts = select( 'ID', 'post_title' )
    ->from( table( 'posts' ) )
    ->where( field( 'post_status' )->eq( 'draft' ) )
    ->andWhere( field( 'post_author' )->eq( 5 ) )
    ->getResults();
```

### General Queries

```php
global $wpdb;

$wpdb->query(
    $wpdb->prepare(
        "
            DELETE FROM $wpdb->postmeta
            WHERE post_id = %d
            AND meta_key = %s
        ",
        13,
        'gargle'
    )
);
```

Becomes:

```php
use function Latitude\QueryBuilder\field;
use function QueryBuilder\Second\delete;
use function QueryBuilder\Second\table;

delete( table( 'postmeta' ) )
    ->where( field( 'post_id' )->eq( 13 ) )
    ->andWhere( field( 'meta_key' )->eq( 'gargle' ) )
    ->query();
```

Additionally:

```php
global $wpdb;

$wpdb->query(
    $wpdb->prepare(
        "
            UPDATE $wpdb->posts
            SET post_parent = %d
            WHERE ID = %d
                AND post_status = %s
        ",
        7,
        15,
        'static'
    )
);
```

Becomes:

```php
use function Latitude\QueryBuilder\field;
use function QueryBuilder\Second\table;
use function QueryBuilder\Second\update;

update( table( 'posts' ) )
    ->set( [
        'post_parent' => 7,
    ] )
    ->where( field( 'ID' )->eq( 15 ) )
    ->andWhere( field( 'post_status' )->eq( 'static' ) )
    ->query();
```

## Issues/Considerations

This was slapped together pretty quickly so I am sure there are plenty - The following would be at the top of my list:

* IDE autocompletion on query proxy objects - method calls are proxied to underlying query objects using __call. Consider implementing multiple query proxy types to match underlying query types.
* Exception messages - I haven't written any :(.
* Like queries - seem to be working as expected but I would like to do some more thorough testing to verify that the latitude engine escapeLike method (which uses str_replace) is working EXACTLY THE SAME AS the wpdb esc_like method (which uses addcslashes).
