<?php

namespace Dump_Server;

/**
 * Adapted from Symfony\Component\HttpFoundation\Request.
 *
 * @todo We have explicitly removed the following functionality:
 *       * Trusted proxies
 *       * Trusted hosts/host patterns
 *       * HTTP method overrides
 *       * Authorization headers
 */
class Request {
	protected $base_url;
	protected $headers;
	protected $identifier;
	protected $path_info;
	protected $request_uri;
	protected $server;

	public function __construct( array $server ) {
		$this->server = $server;
		$this->headers = $this->extract_headers_from_server( $server );
	}

	public function get_identifier() {
		// This is used by descriptors to group dumps.
		// @todo Hash of current method, url and ?
		if ( null === $this->identifier ) {
			$this->identifier = bin2hex( random_bytes( 32 ) );
		}

		return $this->identifier;
	}

	public function get_method() { // AKA get_real_method().
		return strtoupper( $this->get_server_param( 'REQUEST_METHOD', 'GET' ) );
	}

	public function get_uri() {
		$qs = $this->get_query_string();

		if ( null !== $qs ) {
			$qs = "?{$qs}";
		}

		return $this->get_scheme_and_http_host()
			. $this->get_base_url()
			. $this->get_path_info()
			. $qs;
	}

	protected function extract_headers_from_server( array $server ) {
		$headers = [];
		$content_headers = [
			'CONTENT_LENGTH' => true,
			'CONTENT_MD5' => true,
			'CONTENT_TYPE' => true,
		];

		foreach ( $server as $key => $value ) {
			if ( 0 === strpos( $key, 'HTTP_' ) ) {
				$headers[ substr( $key, 5 ) ] = $value;
			} elseif ( isset( $content_headers[ $key ] ) ) {
				$headers[ $key ] = $value;
			}
		}

		return $headers;
	}

	protected function get_base_url() {
		if ( null === $this->base_url ) {
			$this->base_url = $this->prepare_base_url();
		}

		return $this->base_url;
	}

	protected function get_header_param( $key, $default = null ) {
		return array_key_exists( $key, $this->headers ) ? $this->headers[ $key ] : $default;
	}

	protected function get_host() {
		$host = $this->get_header_param( 'HOST' )
			?: $this->get_server_param( 'SERVER_NAME' )
			?: $this->get_server_param( 'SERVER_ADDR', '' );

		$host = strtolower( preg_replace( '/:\d+$/', '', trim( $host ) ) );

		// @todo Should we bother?
		if ( $host && '' !== preg_replace( '/(?:^\[)?[a-zA-Z0-9-:\]_]+\.?/', '', $host ) ) {
			throw new \RuntimeException( "Suspicious Operation: Invalid host \"{$host}\"." );
		}

		return $host;
	}

	protected function get_http_host() {
		$scheme = $this->get_scheme();
		$port = $this->get_port();

		if ( ( 'http' === $scheme && 80 === $port ) || ( 'https' === $scheme && 443 === $port ) ) {
			return $this->get_host();
		}

		return $this->get_host() . ":{$port}";
	}

	protected function get_path_info() {
		if ( null === $this->path_info ) {
			$this->path_info = $this->prepare_path_info();
		}

		return $this->path_info;
	}

	protected function get_port() {
		$host = $this->get_header_param( 'HOST' );

		if ( ! $host ) {
			return $this->get_server_param( 'SERVER_PORT' );
		}

		if ( '[' === $host[0] ) {
			$pos = strpos( $host, ':', strrpos( $host, ']' ) );
		} else {
			$pos = strrpos( $host, ':' );
		}

		if ( false !== $pos ) {
			$port = substr( $host, $pos + 1 );

			if ( $port ) {
				return (int) $port;
			}
		}

		return 'https' === $this->get_scheme() ? 443 : 80;
	}

	protected function get_query_string() {
		$qs = $this->get_server_param( 'QUERY_STRING' );

		if ( '' === $qs ) {
			return null;
		}

		parse_str( $qs, $qs );
		ksort( $qs );

		return http_build_query( $qs, '', '&', PHP_QUERY_RFC3986 );
	}

	protected function get_request_uri() {
		if ( null === $this->request_uri ) {
			$this->request_uri = $this->prepare_request_uri();
		}

		return $this->request_uri;
	}

	protected function get_scheme() {
		return $this->is_secure() ? 'https' : 'http';
	}

	protected function get_scheme_and_http_host() {
		return $this->get_scheme() . '://' . $this->get_http_host();
	}

	protected function get_server_param( $key, $default = null ) {
		return array_key_exists( $key, $this->server ) ? $this->server[ $key ] : $default;
	}

	protected function get_urlencoded_prefix( string $string, string $prefix ) {
		if ( 0 !== strpos( rawurldecode( $string ), $prefix ) ) {
			return false;
		}

		$len = strlen( $prefix );

		if ( preg_match( "#^(%%[[:xdigit:]]{2}|.){{$len}}#", $string, $match ) ) {
			return $match[0];
		}

		return false;
	}

	protected function is_secure() {
		$https = $this->get_server_param( 'HTTPS' );

		return ! empty( $https ) && 'off' !== strtolower( $https );
	}

	protected function prepare_base_url() {
		$filename = basename( $this->get_server_param( 'SCRIPT_FILENAME' ) );

		if ( basename( $this->get_server_param( 'SCRIPT_NAME' ) ) === $filename ) {
			$base_url = $this->get_server_param( 'SCRIPT_NAME' );
		} elseif ( basename( $this->get_server_param( 'PHP_SELF' ) ) === $filename ) {
			$base_url = $this->get_server_param( 'PHP_SELF' );
		} elseif ( basename( $this->get_server_param( 'ORIG_SCRIPT_NAME' ) ) === $filename ) {
			$base_url = $this->get_server_param( 'ORIG_SCRIPT_NAME' ); // 1and1 shared hosting compatibility
		} else {
			// Backtrack up the script_filename to find the portion matching php_self
			$path = $this->get_server_param( 'PHP_SELF', '' );
			$file = $this->get_server_param( 'SCRIPT_FILENAME', '' );
			$segs = explode( '/', trim( $file, '/' ) );
			$segs = array_reverse( $segs );
			$index = 0;
			$last = count( $segs );
			$base_url = '';

			do {
				$seg = $segs[ $index ];
				$base_url = "/{$seg}{$base_url}";
				++$index;
			} while (
				$last > $index && ( false !== $pos = strpos( $path, $base_url ) ) && 0 != $pos
			);
		}

		// Does the base_url have anything in common with the request_uri?
		$request_uri = $this->get_request_uri();

		if ( '' !== $request_uri && '/' !== $request_uri[0] ) {
			$request_uri = "/{$request_uri}";
		}

		$prefix = $this->get_urlencoded_prefix( $request_uri, $base_url );

		if ( $base_url && false !== $prefix ) {
			// full $base_url matches
			return $prefix;
		}

		$prefix = $this->get_urlencoded_prefix(
			$request_uri,
			rtrim( dirname( $base_url ), '/'. DIRECTORY_SEPARATOR ) . '/'
		);

		if ( $base_url && false !== $prefix ) {
			// directory portion of $base_url matches
			return rtrim( $prefix, '/' . DIRECTORY_SEPARATOR );
		}

		$truncated_request_uri = $request_uri;
		$pos = strpos( $request_uri, '?' );

		if ( false !== $pos ) {
			$truncated_request_uri = substr( $request_uri, 0, $pos );
		}

		$basename = basename( $base_url );

		if ( empty( $basename ) || ! strpos( rawurldecode( $truncated_request_uri ), $basename ) ) {
			// no match whatsoever; set it blank
			return '';
		}

		// If using mod_rewrite or ISAPI_Rewrite strip the script filename
		// out of base_url. $pos !== 0 makes sure it is not matching a value
		// from PATH_INFO or QUERY_STRING
		$pos = strpos( $request_uri, $base_url );

		if ( strlen( $request_uri ) >= strlen( $base_url ) && false !== $pos && 0 !== $pos ) {
			$base_url = substr( $request_uri, 0, $pos + strlen( $base_url ) );
		}

		return rtrim( $base_url, '/' . DIRECTORY_SEPARATOR );
	}

	protected function prepare_path_info() {
		$request_uri = $this->get_request_uri();

		if ( null === $request_uri ) {
			return '/';
		}

		// Remove the query string from REQUEST_URI
		$pos = strpos($request_uri, '?');

		if ( false !== $pos ) {
			$request_uri = substr( $request_uri, 0, $pos );
		}

		if ( '' !== $request_uri && '/' !== $request_uri[0] ) {
			$request_uri = "/{$request_uri}";
		}

		$base_url = $this->get_base_url();

		if ( null === $base_url ) {
			return $request_uri;
		}

		$path_info = substr( $request_uri, strlen( $base_url ) );
		if ( false === $path_info || '' === $path_info ) {
			// If substr() returns false then PATH_INFO is set to an empty string
			return '/';
		}

		return (string) $path_info;
	}

	protected function prepare_request_uri() {
		$request_uri = '';

		if (
			'1' == $this->get_server_param( 'IIS_WasUrlRewritten' )
			&& '' != $this->get_server_param( 'UNENCODED_URL' )
		) {
			// IIS7 with URL Rewrite: make sure we get the unencoded URL (double slash problem)
			$request_uri = $this->get_server_param( 'UNENCODED_URL' );

			unset( $this->server['UNENCODED_URL'] );
			unset( $this->server['IIS_WasUrlRewritten'] );
		} elseif ( array_key_exists( 'REQUEST_URI', $this->server ) ) {
			$request_uri = $this->get_server_param( 'REQUEST_URI' );

			if ( '' !== $request_uri && '/' === $request_uri[0] ) {
				// To only use path and query remove the fragment.
				$pos = strpos($request_uri, '#');

				if ( false !== $pos ) {
					$request_uri = substr( $request_uri, 0, $pos );
				}
			} else {
				// HTTP proxy reqs setup request URI with scheme and host [and port] + the URL path,
				// only use URL path.
				$uri_components = parse_url( $request_uri );

				if ( isset( $uri_components['path'] ) ) {
					$request_uri = $uri_components['path'];
				}

				if ( isset( $uri_components['query'] ) ) {
					$request_uri .= "?{$uri_components['query']}";
				}
			}
		} elseif ( array_key_exists( 'ORIG_PATH_INFO', $this->server ) ) {
			// IIS 5.0, PHP as CGI
			$request_uri = $this->get_server_param( 'ORIG_PATH_INFO' );

			if ( '' != $this->get_server_param( 'QUERY_STRING' ) ) {
				$request_uri .= '?' . $this->get_server_param( 'QUERY_STRING' );
			}

			unset( $this->server['ORIG_PATH_INFO'] );
		}

		// normalize the request URI to ease creating sub-requests from this request
		$this->server['REQUEST_URI'] = $request_uri;

		return $request_uri;
	}
}
