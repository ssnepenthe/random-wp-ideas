<?php

namespace Dump_Server;

use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\ContextProvider\CliContextProvider;
use Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider;
use Symfony\Component\VarDumper\Dumper\DataDumperInterface;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\Dumper\ServerDumper;

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

	public static function default_context_providers( string $project_dir = null ) {
		return [
			'cli' => new CliContextProvider(),
			'source' => new SourceContextProvider( null, $project_dir ),
			'request' => new Request_Context_Provider( static::request() ),
			// @todo WP-specific context provider?
		];
	}

	public static function dumper(
		string $host = 'tcp://127.0.0.1:9912',
		DataDumperInterface $wrapped_dumper = null,
		array $context_providers = []
	) {
		return new ServerDumper(
			$host,
			$wrapped_dumper ?: static::default_fallback_dumper(),
			count( $context_providers ) > 0
				? $context_providers
				: static::default_context_providers()
		);
	}

	public static function default_fallback_dumper() {
		return in_array( PHP_SAPI, [ 'cli', 'phpdbg' ], true ) ? new CliDumper() : new HtmlDumper();
	}

	public static function request() {
		return new Request( $_SERVER );
	}
}
