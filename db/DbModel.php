<?php

namespace app\core\db;

use app\core\Application;
use app\core\Model;
//MAPEO RELACIONAL DE OBJETOS DE MODELO ORM.
//QUE MAPEARÁ LA CLASE DE USUARIOS DEL MODELO DEL USUARIO EN LA TABLA DE LA DB
//NO SE MAPEARÁ, PERO  SERÁ UNA CLASE DE REGISTRO ACTIVO DE LA DB
abstract class DbModel extends Model
{
	abstract public static function tableName(): string;

	abstract public function attributes(): array;

	abstract public static function primaryKey(): string;

	public function save()
	{
		$tableName = $this->tableName();
		$attributes = $this->attributes();
        $params = array_map(function($attr){return ":$attr";}, $attributes);

        $statement = self::prepare("INSERT INTO $tableName (" . implode(",", $attributes) . ") 
                VALUES (" . implode(",", $params) . ")");



        foreach ($attributes as $attribute) {
            $statement->bindValue(":$attribute", $this->{$attribute}); //bindValue, vincula un valor
        }
        $statement->execute();
        return true;		
	}

	public static function findOne($where) // [email => zura@example.com, firstname => zura]
	{
        $tableName = static::tableName();
        $attributes = array_keys($where);
        $sql = implode("AND", array_map(function($attr){return "$attr = :$attr";}, $attributes));
        $statement = self::prepare("SELECT * FROM $tableName WHERE $sql");
        foreach ($where as $key => $item) {
            $statement->bindValue(":$key", $item);
        }

        $statement->execute();

		//Devuelve el objeto de búsqueda con el nombre de la clase. Es decir:
		//El objeto de recuperación devuelve un objeto por defecto, pero se DESEA QUE DEVUELVA UNA INSTANCIA DE LA CLASE DE USUARIO
        return $statement->fetchObject(static::class);
	}

	public static function prepare($sql)
	{
		return Application::$app->db->pdo->prepare($sql);
	}
}

?>