<?php
namespace up\oauth\server;

use up\oauth\server;

class controller
{
	private $path;


	public function __construct( $path )
	{
		$this->path = $path;
	}

	public function call( $action, array $params )
	{
		$Action = $this->createAction( $action );

		return $Action->call( $params );
	}

	private function createAction( $action )
	{
		$class    = 'api\\app\\' . str_replace( '.', '\\', $action );
		$filename = $this->path . 'app/' . str_replace( '.', '/', $action ) . '.php';

		if ( !is_file( $filename ) )
			server::error( error::ACTION_NOT_FOUND );

		require_once $filename;

		if ( !class_exists( $class ) )
			server::error( error::ACTION_NOT_FOUND );

		return new $class( $action );
	}
}