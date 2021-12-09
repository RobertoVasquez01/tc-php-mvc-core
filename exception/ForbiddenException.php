<?php
namespace app\core\exception;

//Excepción prohibida
class ForbiddenException extends \Exception
{
	protected $message = 'Usted no tiene permiso para acceder a esta página';
	protected $code = 403;
}

?>