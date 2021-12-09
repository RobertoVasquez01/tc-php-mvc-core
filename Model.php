<?php

namespace app\core;

abstract class Model
{
	public const RULE_REQUIRED = 'required';
	public const RULE_EMAIL = 'email';
	public const RULE_MIN = 'min';
	public const RULE_MAX = 'max';
	public const RULE_MATCH = 'match';
	public const RULE_UNIQUE = 'unique';

	public function loadData($data)
	{
		foreach ($data as $key => $value) {
			if (property_exists($this, $key)) {
				$this->{$key} = $value;
			}
		}
	}

	abstract public function rules(): array;

	public $errors = []; //array

	/*public function attributes()
    {
        return [];
    }*/

	public function getLabel($attribute)
    {
        return $this->labels()[$attribute] ?? $attribute;
    }    

    public function labels()
    {
        return [];
    }

	public function validate()
	{	
		foreach ($this->rules() as $attribute => $rules) {
			$value = $this->{$attribute};
			foreach ($rules as $rule) {
				$ruleName = $rule;
				if (!is_string($ruleName)) {
					$ruleName = $rule[0];
				}
				if ($ruleName === self::RULE_REQUIRED && !$value) {
					$this->addErrorForRule($attribute, self::RULE_REQUIRED);
				}
				if ($ruleName === self::RULE_EMAIL && !filter_var($value, FILTER_VALIDATE_EMAIL) ) {
					$this->addErrorForRule($attribute, self::RULE_EMAIL);
				}
				if ($ruleName === self::RULE_MIN && strlen($value) < $rule['min']) {
					$this->addErrorForRule($attribute, self::RULE_MIN, $rule);
				}
				if ($ruleName === self::RULE_MAX && strlen($value) > $rule['max']) {
					$this->addErrorForRule($attribute, self::RULE_MAX, $rule);
				}			
				if ($ruleName === self::RULE_MATCH && $value !== $this->{$rule['coincidencia']}) {
					$rule['coincidencia'] = $this->getLabel($rule['coincidencia']);
					$this->addErrorForRule($attribute, self::RULE_MATCH, $rule);
				}
                if ($ruleName === self::RULE_UNIQUE) {
                    $className = $rule['class'];
                    $uniqueAttr = $rule['attribute'] ?? $attribute;
                    $tableName = $className::tableName();
                    $db = Application::$app->db;
                    $statement = $db->prepare("SELECT * FROM $tableName WHERE $uniqueAttr = :attr");
                    $statement->bindValue(":attr", $value);
                    $statement->execute();
                    $record = $statement->fetchObject();

                    if ($record) {
                        $this->addErrorForRule($attribute, self::RULE_UNIQUE, ['field' => $this->getLabel($attribute)]);
                    }
                }				
			}
		}

		return empty($this->errors);
	}

	private function addErrorForRule(string $attribute, string $rule, $params = [])
	{
		$message = $this->errorMenssages()[$rule] ?? '';

		foreach ($params as $key => $value) {
			$message = str_replace("{{$key}}", $value, $message);
		}

		$this->errors[$attribute][] = $message;
	}	

	public function addError(string $attribute, string $message)
	{
		$this->errors[$attribute][] = $message;
	}

	public function errorMenssages()
	{
		return [
		    self::RULE_REQUIRED => 'Este campo es obligatorio',
		    self::RULE_EMAIL => 'Este campo debe ser una dirección de correo electrónico válida',
		    self::RULE_MIN => 'La longitud mínima de este campo debe ser {min}',
		    self::RULE_MAX => 'La longitud máxima de este campo debe ser {max}',
		    self::RULE_MATCH => 'Este campo debe ser el mismo que {coincidencia}',
		    self::RULE_UNIQUE => 'El registro con este {field} ya existe',
		];
	}

    public function hasError($attribute)
    {
        return $this->errors[$attribute] ?? false;
    }

    public function getFirstError($attribute)
    {
        $errors = $this->errors[$attribute] ?? [];
        return $errors[0] ?? '';
    }
	
}

?>