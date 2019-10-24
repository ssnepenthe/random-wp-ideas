<?php

namespace Dump_Server;

use Symfony\Component\VarDumper\Dumper\ContextProvider\ContextProviderInterface;

final class Request_Context_Provider implements ContextProviderInterface {
	protected $request;

	public function __construct( Request $request ) {
		$this->request = $request;
	}

	public function getContext(): ?array {
		// @todo This only applies to http requests - Bail early on cli requests?

		return [
			'uri' => $this->request->get_uri(),
			'method' => $this->request->get_method(),
			'controller' => null, // @todo ?
			'identifier' => $this->request->get_identifier(),
		];
	}
}