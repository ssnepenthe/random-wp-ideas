# query-builder

A very simple bridge between wpdb and the latitude query builder package.

## Usage

TODO!

In the meantime, start with the [latitude docs](https://latitude.shadowhand.me/).

## Issues

This was slapped together pretty quickly so I am sure there are plenty - The following would be at the top of my list:

* IDE autocompletion on query proxy objects - method calls are proxied to underlying query objects using __call. Consider implementing multiple query proxy types to match underlying query types.
* Camel to snake casing fail - Query proxy objects also proxy snake cased method name to the corresponding camel cased version on the underlying query objects. This is great on the WPCS front but should be further fleshed out to include more of the latitude API (e.g. criteria and like builders) or dropped altogether to avoid the confusion from mixed casing.
* Exception messages - I haven't written any :(.
* Like queries - seem to be working as expected but I would like to do some more thorough testing to verify that the latitude engine escapeLike method (which uses str_replace) is working EXACTLY THE SAME AS the wpdb esc_like method (which uses addcslashes).
