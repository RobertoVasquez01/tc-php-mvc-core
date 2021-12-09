<?php
namespace app\core;

use app\core\db\Database;
use app\core\db\DbModel;

class Application
{
	public $layout = 'main'; // string
	public static $ROOT_DIR;
	public $router; //Router
	public $request; //Request
	public $response; //Response
	public static $app; //Application
	public $controller = null; //Controller
	public $session; //Session
	public $db; //Database
	
	public $user; //?UserModel  --- public ?DbModel $user //El signo de interrogación es porque podría ser nulo
	public $userClass; //Creo que es para utilizarlo para el login

	public $view; //View

	public function __construct($rootPath, array $config)
	{
		$this->userClass = $config['userClass'];
		self::$ROOT_DIR = $rootPath;
		self::$app = $this;
		$this->request = new Request();
		$this->response = new Response();
		$this->session = new Session();
		$this->router = new Router($this->request,$this->response);
		$this->view = new View();

		$this->db = new Database($config['db']);

		//ESTO BUSCA AL USUARIO CUANDO NAVEGO ENTRE PÁGINAS
        $userId = $this->session->get('user');
        if ($userId) {
            $key = $this->userClass::primaryKey();
            $this->user = $this->userClass::findOne([$key => $userId]);
        } else {
        	$this->user = null;
        }
	}

    public static function isGuest()
    {
        return !self::$app->user;
    }	

	public function run()
	{
		try {
			echo $this->router->resolve();

		} catch (\Exception $e) {
			$this->response->setStatusCode($e->getCode());
			//echo $e; //Aqui está el error que fue digitado en: ForbiddenException
			echo $this->view->renderView('_error',[
				'exception' => $e
			]);
		}
	}

	public function getController()//: \app\core\Controller
	{
		return $this->controller;
	}

	public function setController(\app\core\Controller $controller)
	{
		$this->controller = $Controller;
	}

	public function login(UserModel $user)
	{
		$this->user = $user;
		$primaryKey = $user->primaryKey();
		$primaryValue = $user->{$primaryKey};
		$this->session->set('user', $primaryValue);
		return true;
	}

	public function logout()
	{
		$this->user = null;
		$this->session->remove('user');
	}
}