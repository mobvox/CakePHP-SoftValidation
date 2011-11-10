<?php
class SoftValidationBehavior extends ModelBehavior {
/**
 * Hold the settings of models.
 */
	public $settings = array();
/**
 * Hold the softValidate rules extracted from model.
 * Eg: array('ModelName' => array( //rules extracted ));
 */
	public $softValidate = array();
/**
 * Hold default settings.
 */
	protected $_defaultSettings = array();
/**
 * Hold the model isntance.
 */
	private $__model;

/**
 * Setup
 */
	public function setup(&$model, $config = array()) {
		$this->__model =& $model;
		$this->softValidate[$model->alias] = $this->_parseSoftValidates();
	}

	public function afterSave(&$model, $created){
		
	}

/**
 * Return the soft validation and remove them from
 * model validate to prevent validations on soft.
 */
	protected function _parseSoftValidates(){
		$softValidate = array();


		foreach ($this->__model->validate as $field => $rules){
			foreach($rules as $rule){
				if(!is_array($rule)){
					continue;
				}
				if(isset($rule['soft']) && $rule['soft'] == true){
					$softValidate[$field] = $rules;
					unset($this->__model->validate[$field]);
				}
			}
		}
		return $softValidate;
	}
}
