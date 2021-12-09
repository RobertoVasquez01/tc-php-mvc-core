<?php 

namespace app\core\form;

use app\core\Model;

abstract class BaseField
{
	protected $model; //Model
	protected $attribute; //string


	public function __construct(Model $model, string $attribute)
	{
		$this->model = $model;
		$this->attribute = $attribute;
	}

	abstract public function renderInput(): string;  //Debería devolver una cadena y debería ser un text Area, o seleccionar cualquier cosa


	public function __toString()
	{
		return sprintf('
			<div class="mb-3">
				<label class="form-label">%s</label>
				%s
				<div class="invalid-feedback">
					%s
				</div>
			</div>',
			$this->model->getLabel($this->attribute),
			$this->renderInput(),
			$this->model->getFirstError($this->attribute),
		);
	}
}

 ?>