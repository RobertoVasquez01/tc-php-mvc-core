<?php

namespace app\core;

use app\core\exception\NotFoundException;

class Router
{
	public $request; //Request
	public $response; //Response
	protected $routes = [];

	public function __construct(Request $request, Response $response)
	{
		$this->request = $request;
		$this->response = $response;
	}

	public function get($patch, $callback)
	{
		$this->routes['get'][$patch] = $callback;
	}

	public function post($patch, $callback)
	{
		$this->routes['post'][$patch] = $callback;
	}	

	public function resolve()
	{
		$path = $this->request->getPath();
		$method = $this->request->method();
		$callback = $this->routes[$method][$path] ?? false;

		if($callback === false) {
			throw new NotFoundException();
			
		}

		if(is_string($callback)){
			return Application::$app->view->renderView($callback);
		}

		if(is_array($callback)) {
			/**
			 * @var \app\core\Controller $controller
			 * La La variabe del controlador es una instancia de la clase del controlador
			 */
			$controller = new $callback[0]();
			Application::$app->controller = $controller;
			$controller->action = $callback[1]; //Estoy guardando la acción del controlador
			$callback[0] = $controller;

			foreach ($controller->getMiddlewares() as $middleware) {
				$middleware->execute();
			}
		}
		return call_user_func($callback, $this->request, $this->response);
	}
}
