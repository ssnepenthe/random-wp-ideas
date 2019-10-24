<?php

namespace Footer_Dump;

use Symfony\Component\VarDumper\VarDumper;

class Package {
	protected static $loaded = false;

	public static function load() {
		if ( static::$loaded ) {
			return;
		}

		VarDumper::setHandler( Factory::dumper_handler() );

		require_once __DIR__ . '/footer-dump.php';

		static::$loaded =  true;
	}
}
