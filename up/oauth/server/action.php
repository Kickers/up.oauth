<?php
namespace up\oauth\server;

use up\oauth\server;

abstract class action
{
	private $requestParams = array();

	/**
	 * @var \ReflectionClass
	 */
	private $_reflection;

	/**
	 * @var ReflectionMethod
	 */
	private $_reflectionMethod;

	private $action;


	final public function __construct( $action )
	{
		$this->action = $action;
	}

	final public function call( array $params )
	{
		$this->requestParams = $params;

		$this->init();
		$this->checkAccess();

		$args = $this->getArgsForCall();

		$result = call_user_func_array( array( $this, 'action' ), array_values( $args ) );

		return $result;
	}

	protected function init()
	{
		$this->_reflection       = new \ReflectionClass( $this );
		$this->_reflectionMethod = $this->reflection()->getMethod( 'action' );
	}

	/**
	 * @return \ReflectionClass
	 */
	protected function reflection()
	{
		return $this->_reflection;
	}

	/**
	 * @return \ReflectionMethod
	 */
	protected function reflectionMethod()
	{
		return $this->_reflectionMethod;
	}

	protected function requestParam( $param )
	{
		return isset( $this->requestParams[$param] ) ? $this->requestParams[$param] : null;
	}

	private function checkAccess()
	{
		if ( $this->reflection()->getMethod( 'action' )->isPublic() )
			return true;

		// check auth api

		$accessToken = $this->requestParam( 'access_token' );
		$clientId    = $this->requestParam( 'client_id' );

		if ( !$accessToken )
			server::error( error::PARAM_NOT_SET, 'missing param - access_token' );

		if ( !$clientId )
			server::error( error::PARAM_NOT_SET, 'missing param - client_id' );




		return false;
	}

	private function getArgsForCall()
	{
		$actionParams  = $this->reflectionMethod()->getParameters();
		$requestParams = $this->requestParams;

		$callArgs = array();
		foreach ( $actionParams as $actionParam ) {
			$paramName  = $actionParam->name;
			$value      = null;
			$isValueSet = isset( $requestParams[$paramName] );

			if ( $isValueSet )
				$value = $requestParams[$paramName];

			if ( $actionParam->isDefaultValueAvailable() )
			{
				if ( !$isValueSet )
					$value = $actionParam->getDefaultValue();
			}
			else
			{
				if ( !$isValueSet )
					server::error( error::PARAM_NOT_SET, 'missing param - ' . $paramName );
			}

			$callArgs[$paramName] = $value;
		}

		return $callArgs;
	}
}