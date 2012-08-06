<?php

/**
 * Dispatcher: A class to handle the client routing
 * @author maraya
 *
 * Version: 0.1 (26 July 2012)
 */
 
class Dispatcher {

	/**
	 * Dispatcher/router method
	 * @param CazuelaRequest $request
	 * @param CazuelaResponse $response
	 * @throws CazuelaException
	 */
	public function dispatch($request, $response) {
		try {
			$request->setClass($_REQUEST['__class']);
			$request->setMethod($_REQUEST['__method']);
			$response->setType($_REQUEST['__type']);
			
			$params = array();
			foreach($_REQUEST as $parameter => $value) {
				if (stripos($parameter, '__') === false) {
					$params[$parameter] = $value;
				}
			}
			
			$request->setParams($params);
			$response->setContentType($response->getType());
			
			if (!in_array($response->getType(), array("json", "xml", "yml"))) {
				throw new CazuelaException("Type ". $response->getType() . " is invalid", 400);
			}
			
			if ($response->getType() == "yml" && function_exists("yaml_emit") == false) {
				$response->setType("json");
				throw new CazuelaException("YAML is not installed. Please verify your PHP instalation", 500);
			}
			
			if (!file_exists(CAZUELA_APP_ROOT ."/class/". $request->getClass() .".php")) {
				throw new CazuelaException("Class file ". $request->getClass() ." not found", 404);
			}
			
			include(CAZUELA_APP_ROOT ."/class/". $request->getClass() .".php");
			
			try {
				$obj = new ReflectionClass($request->getClass());
			} catch (ReflectionException $e) {
				throw new CazuelaException("Class ". $request->getMethod() ." doesn't exist", 404);
			}
			
			if ($obj->hasMethod($request->getMethod()) === false) {
				throw new CazuelaException("Method ". $request->getMethod() ." doesn't exist", 404);
			}
			
			$rMethod = new ReflectionMethod($request->getClass(), $request->getMethod());
			if ($rMethod->isProtected() === true) {
				// Forbidden access to protected methods!
				throw new CazuelaException("Method ". $request->getMethod() ." doesn't exist", 404);
			}
			
			$parameterCount = 0;
			foreach ($rMethod->getParameters() as $parameter) {
				if ($parameter->isOptional() === false) {
					$parameterCount++;
				}
			}
			
			if (sizeof($request->getParams()) < $parameterCount) {
				throw new CazuelaException("Wrong number of parameters", 404);
			}
			
			$className = $request->getClass();
			$data = $rMethod->invokeArgs(new $className, $request->getParams());
			$response->setData($data);
			
		} catch (CazuelaException $e) {
			$response->setMessage($e->getMessage());
			$response->setCode($e->getCode());
			$response->setError(true);
		}
		
		if ($response->error()) {
			$info = array("code" => $response->getCode(), "message" => $response->getMessage());
		} else {
			$info = array("data" => $response->getData());
		}
		
		$response->setResponse($info);
	}
}
?>