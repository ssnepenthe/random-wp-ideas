<?php

require __DIR__ . '/vendor/autoload.php';

// Swiped from fast-route tests.
$test_routes = [
	[
		'/test',
		[ '/test' => 'index.php?pfx_matched_route=/test' ],
	],
	[
		'/test/{param}',
		[ '/test/([^/]+)' => 'index.php?pfx_param=$matches[1]&pfx_matched_route=/test/([^/]+)' ],
	],
	[
		'/te{ param }st',
		[ '/te([^/]+)st' => 'index.php?pfx_param=$matches[1]&pfx_matched_route=/te([^/]+)st' ],
	],
	[
		'/test/{param1}/test2/{param2}',
		[ '/test/([^/]+)/test2/([^/]+)' => 'index.php?pfx_param1=$matches[1]&pfx_param2=$matches[2]&pfx_matched_route=/test/([^/]+)/test2/([^/]+)' ],
	],
	[
		'/test/{param:\d+}',
		[ '/test/(\d+)' => 'index.php?pfx_param=$matches[1]&pfx_matched_route=/test/(\d+)' ],
	],
	[
		'/test/{ param : \d{1,9} }',
		[ '/test/(\d{1,9})' => 'index.php?pfx_param=$matches[1]&pfx_matched_route=/test/(\d{1,9})' ],
	],
	[
		'/test[opt]',
		[
			'/test' => 'index.php?pfx_matched_route=/test',
			'/testopt' => 'index.php?pfx_matched_route=/testopt',
		],
	],
	[
		'/test[/{param}]',
		[
			'/test' => 'index.php?pfx_matched_route=/test',
			'/test/([^/]+)' => 'index.php?pfx_param=$matches[1]&pfx_matched_route=/test/([^/]+)',
		]
	],
	[
		'/{param}[opt]',
		[
			'/([^/]+)' => 'index.php?pfx_param=$matches[1]&pfx_matched_route=/([^/]+)',
			'/([^/]+)opt' => 'index.php?pfx_param=$matches[1]&pfx_matched_route=/([^/]+)opt',
		],
	],
	[
		'/test[/{name}[/{id:[0-9]+}]]',
		[
			'/test' => 'index.php?pfx_matched_route=/test',
			'/test/([^/]+)' => 'index.php?pfx_name=$matches[1]&pfx_matched_route=/test/([^/]+)',
			'/test/([^/]+)/([0-9]+)' => 'index.php?pfx_name=$matches[1]&pfx_id=$matches[2]&pfx_matched_route=/test/([^/]+)/([0-9]+)',
		],
	],
	// @todo Empty routes probably shouldn't be allowed...
	// [
	//     '',
	//     [
	//         [''],
	//     ]
	// ],
	// [
	//     '[test]',
	//     [
	//         [''],
	//         ['test'],
	//     ]
	// ],
	[
		'/{foo-bar}',
		[ '/([^/]+)' => 'index.php?pfx_foo-bar=$matches[1]&pfx_matched_route=/([^/]+)' ],
	],
	[
		'/{_foo:.*}',
		[ '/(.*)' => 'index.php?pfx__foo=$matches[1]&pfx_matched_route=/(.*)' ],
	],
];

$converter = new \FRWP\Converter( 'pfx_' );

foreach ( $test_routes as [ $input, $output ] ) {
	assert( $converter->to_wp_rewrite( $input ) === $output );
}

echo 'SUCCESS!';

$converter = new \FRWP\Converter( 'pfx_' );
$converter->resolve_additional_params_using( function( $regex, $query_array ) {
    // Closure is bound to converter instance. Params array is merged into parsed params array.
    return [ "{$this->prefix}status" => 'preorder' ];
} );

var_export( $converter->to_wp_rewrite( '/books/{title:[a-z0-9-]+}' ) );