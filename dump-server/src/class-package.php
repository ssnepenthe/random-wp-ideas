<?php

namespace Dump_Server;

use Symfony\Component\VarDumper\Dumper\DataDumperInterface;
use Symfony\Component\VarDumper\VarDumper;

class Package {
	protected static $loaded = false;

	public static function load(
		string $host = 'tcp://127.0.0.1:9912',
		DataDumperInterface $fallback_dumper = null,
		array $context_providers = [],
		array $casters = []
	) {
		if ( static::$loaded ) {
			return;
		}

		$cloner = Factory::cloner( $casters );
		$dumper = Factory::dumper( $host, $fallback_dumper, $context_providers );

		VarDumper::setHandler( function( $var ) use ( $cloner, $dumper ) {
			$dumper->dump( $cloner->cloneVar( $var ) );
		} );

		static::$loaded = true;
	}
}
