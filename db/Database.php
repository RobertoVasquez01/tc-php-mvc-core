<?php

namespace app\core\db;

use app\core\Application;

class Database
{
	public $pdo; // \PDO

    public function __construct(array $dbConfig = [])
    {
        $dsn = $dbConfig['dsn'] ?? '';
        $user = $dbConfig['user'] ?? '';
        $password = $dbConfig['password'] ?? '';

        $this->pdo = new \PDO($dsn, $user, $password);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function applyMigrations() //ESTO LEE LOS ARCHIVOS DE LA CARPETA MIGRACIONES Y LOS APLICA A LA BASE DE DATOS
    {
        $this->createMigrationsTable();
        $appliedMigrations = $this->getAppliedMigrations(); //PARA OBETENER LAS MIGRACIONES YA APLICADAS.

        $files = scandir(Application::$ROOT_DIR.'/migrations'); //lISTA DE ARCHIVOS DEL DIRECTORIO: migrations
        $toApplyMigrations = array_diff($files, $appliedMigrations); //Restamos a files, las migraciones ya hechas

        foreach ($toApplyMigrations as $migration) {
        	if ($migration === '.' || $migration === '..') {
        		continue;
        	}

	        require_once Application::$ROOT_DIR.'/migrations/'.$migration;
	        $className = pathinfo($migration, PATHINFO_FILENAME);

	        $instance = new $className;

            $this->log("APLICANDO MIGRACIÓN EN =========> $migration");
            $instance->up();
            $this->log("SE COMPLETÓ LA MIGRACIÓN: $migration");
            $newMigrations[] = $migration;

        }

        if (!empty($newMigrations)) {
            $this->saveMigrations($newMigrations);
        } else {
            $this->log("Todas las migraciones están aplicadas.");
        }
    }

    public function createMigrationsTable() // Esto registra las migraciones para no volverlas a hacer
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )  ENGINE=INNODB;");
    }

    public function getAppliedMigrations()
    {
        $statement = $this->pdo->prepare("SELECT migration FROM migrations");
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_COLUMN); 
        // obtener los valores de la columna como una matriz UNIDIMENSIONAL
        //Si no especifico \PDO::FETCH_COLUMN, devolverá una matriz SECUNDARIA de cada registro de las migraciones
    }

    protected function saveMigrations(array $migrations)
    {
		$prepareFile = function($file) {return "('".$file."')";};

        $str = implode(',', array_map($prepareFile, $migrations));
        $statement = $this->pdo->prepare("INSERT INTO migrations (migration) VALUES
        	$str
        ");
        $statement->execute();
    }

	public function prepare($sql)
	{
		return $this->pdo->prepare($sql);
	}

    private function log($message)
    {
        echo "[" . date("Y-m-d H:i:s") . "] - " . $message . PHP_EOL;
    } 
}
?>