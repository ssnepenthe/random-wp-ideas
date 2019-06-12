<?php

namespace Hook_Injection;

use Invoker\Invoker;
use Invoker\InvokerInterface;
use Invoker\ParameterResolver\ResolverChain;
use Invoker\ParameterResolver\TypeHintResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\AssociativeArrayResolver;

// @todo Optimize use of _wp_filter_build_unique_id().
class Event_Manager {
	protected $callbacks = [];
	protected $callback_hook_counts = [];
	protected $invoker;

	// @todo Remove direct dependency on global function w/WP hook adapter?
	public function __construct( InvokerInterface $invoker = null ) {
		$this->invoker = $invoker ?: new Invoker(
			// The default resolver doesn't include TypeHintResolver.
			new ResolverChain([
	            new NumericArrayResolver,
	            new TypeHintResolver,
	            new AssociativeArrayResolver,
	            new DefaultValueResolver,
	        ])
		);
	}

	public function has( $tag, $callback = false ) {
		if ( false === $callback ) {
			return \has_filter( $tag, false );
		}

		if ( ! $this->is_callback_wrapped( $tag, $callback, false ) ) {
			return false;
		}

		return \has_filter( $tag, $this->wrap_callback( $tag, $callbacks, false ) );

	}

	public function off( $tag, $callback, $priority = 10 ) {
		if ( ! $this->is_callback_wrapped( $tag, $callback, $priority ) ) {
			return false;
		}

		$success = \remove_filter(
			$tag,
			$this->wrap_callback( $tag, $callback, $priority ),
			$priority
		);

		if ( $success ) {
			$this->decrement_callback_hook_count( $tag, $callback, $priority );
		}

		return $success;
	}

	public function on( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->increment_callback_hook_count( $tag, $callback, $priority );

		return \add_filter(
			$tag,
			$this->wrap_callback( $tag, $callback, $priority ),
			$priority,
			$accepted_args
		);
	}

	protected function decrement_callback_hook_count( $tag, $callback, $priority ) {
		$callback_id = \_wp_filter_build_unique_id( $tag, $callback, $priority );

		if ( ! \array_key_exists( $callback_id, $this->callback_hook_counts ) ) {
			return 0;
		}

		$new_val = --$this->callback_hook_counts[ $callback_id ];

		if ( 0 === $new_val ) {
			unset( $this->callback_hook_counts[ $callback_id ], $this->callbacks[ $callback_id ] );
		}

		return $new_val;
	}

	protected function increment_callback_hook_count( $tag, $callback, $priority ) {
		$callback_id = \_wp_filter_build_unique_id( $tag, $callback, $priority );

		if ( ! \array_key_exists( $callback_id, $this->callback_hook_counts ) ) {
			$this->callback_hook_counts[ $callback_id ] = 0;
		}

		return ++$this->callback_hook_counts[ $callback_id ];
	}

	protected function is_callback_wrapped( $tag, $callback, $priority ) {
		return \array_key_exists(
			\_wp_filter_build_unique_id( $tag, $callback, $priority ),
			$this->callbacks
		);
	}

	protected function wrap_callback( $tag, $callback, $priority ) {
		$callback_id = \_wp_filter_build_unique_id( $tag, $callback, $priority );

		if ( ! \array_key_exists( $callback_id, $this->callbacks ) ) {
			$this->callbacks[ $callback_id ] = function() use ( $callback ) {
				return $this->invoker->call(
					$callback,
					\array_filter( \func_get_args() + Util::gather_wp_globals() )
				);
			};
		}

		return $this->callbacks[ $callback_id ];
	}
}
