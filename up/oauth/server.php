<?php
namespace up\oauth;

use up\header as headers;
use up\oauth\server\controller;
use up\oauth\server\error as error;

class server
{
	private $uriParams;
	private $path;

	private static $statusGroups = array(
		  204 => array(

		)
		, 400 => array(
			  error::PARAM_NOT_SET
			, error::ACTION_NOT_FOUND
		)
		, 500 => array(
			error::UNKNOWN
		)
	);

	private $contentType = 'application/json';


	public function __construct()
	{

	}

	public function setUriParams( array $params )
	{
		$this->uriParams = $params;
	}

	public function setProjectsRoot( $path )
	{
		$this->path = $path;
	}

	public function call( $action )
	{
		$status = 200;

		try {
			$Controller = new controller( $this->path );

			$result = $Controller->call( $action, $this->uriParams );
		} catch ( \Exception $e ) {
			$code   = $e->getCode();
			$status = $this->getStatusByErrorCode( $code );

			$result = array(
				'error' => array(
					  'code' => $code
					, 'msg'  => $e->getMessage()
				)
			);
		}

		$result = $result ? $result : array();

		if ( empty( $result ) )
			$status = 204;

		$result = json_encode( $result );

		$this->sendResult( $result, $status );
	}

	private function sendResult( $result, $httpStatus )
	{
		header::add( 'content-type', $this->contentType );
		header::send( $httpStatus );

		echo $result;
	}

	private function getStatusByErrorCode( $errorCode )
	{
		foreach ( self::$statusGroups as $httpCode => $groups ) {
			if ( $code = array_search( $errorCode, $groups ) !== false )
				return $httpCode;
		}

		return null;
	}

	public static function error( $code = error::UNKNOWN, $msg = null )
	{
		$msg = $msg ? $msg : error::getMsgByCode( $code );

		throw new \Exception( $msg, $code );
	}
}