<?php

namespace Footer_Dump;

use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

class Factory {
	public static function cloner( array $casters = [] ) {
		$cloner = new VarCloner();

		if ( count( $casters ) > 0 ) {
			$cloner->addCasters( $casters );
		} else {
			$cloner->addCasters( ReflectionCaster::UNSET_CLOSURE_FILE_INFO );
		}

		return $cloner;
	}

	public static function dumper() {
		if ( isset( $_SERVER['VAR_DUMPER_FORMAT'] ) ) {
			return 'html' === $_SERVER['VAR_DUMPER_FORMAT'] ? new HtmlDumper() : new CliDumper();
		}

		return in_array( PHP_SAPI, [ 'cli', 'phpdbg' ], true ) ? new CliDumper() : new HtmlDumper();
	}

	public static function dumper_handler() {
		$cloner = static::cloner();
		$dumper = static::dumper();

		return function( $var ) use ( $cloner, $dumper ) {
			$dumper->dump( $var instanceof Data ? $var : $cloner->cloneVar( $var ) );
		};
	}
}
