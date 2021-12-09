<?php
namespace app\core;

use app\core\middlewares\BaseMiddleware;

class Controller
{
	public $action = ""; //string
	public $layout = 'main'; //string
	/**
	 * @var \app\core\middlewares\BaseMiddleware[]
	*/
	//ESTO NO ES SOLO UNA MATRIZ, SINO UNA MATRIZ DE CLASES DE MDDLEWARE
	protected $middlewares = []; //array

	public function setLayout($layout)
	{
		$this->layout = $layout;
	}

	public function render($view, $params = [])
	{
		return Application::$app->view->renderView($view, $params);
	}

	public function registerMiddleware(BaseMiddleware $middleware)
	{
		$this->middlewares[] = $middleware;

	}

	public function getmiddlewares(): array
	{
		return $this->middlewares;
	}
}
?>