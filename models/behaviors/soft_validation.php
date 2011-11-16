<?php
class SoftValidationBehavior extends ModelBehavior {
/**
  * Hold the settings of models.
  */
	public $settings = array();
/**
  * Hold the softValidate rules extracted from model.
  * Eg.: array('ModelName' => array( //rules extracted ));
  */
	protected $_softValidateFields = array();
/**
  * Hold default settings.
  */
	protected $_defaultSettings = array();
/**
  * Hold the softValidate status of model.
  * Eg.: array('ModelName' => true);
  */
	protected $_softValidateStatus = array();
/**
  * Hold the model isntance.
  */
	private $__Model;

/**
  * Setup
  */
	public function setup(&$Model, $config = array()) {
		$this->__Model =& $Model;
		$this->_softValidateFields[$Model->alias] = $this->_parseSoftValidates();
	}
/**
  * beforeSave callback
  */
	public function beforeSave(&$Model){
		$this->_softValidate();
		return $this->_triggerCallback();
	}
/**
 * Perform the softValidation rules
 *
 * @return void
 */
	protected function _softValidate(){
		$this->__Model->validate = Set::merge($this->__Model->validate, $this->_softValidateFields[$this->__Model->alias]);
		$validates = $this->__Model->validates();
		$this->__Model->validationErrors = array();
		if($validates){
			$this->_softValidationUpdateStatus(true);
		}else{
			$this->_softValidationUpdateStatus(false);
		}
	}
/**
 * Trigger callbacks.
 *
 * @return boolean
 */
	protected function _triggerCallback(){
		if($this->getSoftValidationStatus($this->__Model)){
			return $this->__Model->beforeSaveSoftValidationSuccess();
		}else{
			return $this->__Model->beforeSaveSoftValidationFail();
		}
	}
/**
  * Get the Soft Validation status.
  *
  * @return boolean
  */
	public function getSoftValidationStatus(&$Model, $data = null){
		if(!is_null($data)){
			$this->__Model = $Model;
			$this->__Model->set($data);
			$this->_softValidate();
		}
		debug($this->_softValidateStatus);
		if(isset($this->_softValidateStatus[$Model->alias])){
			return $this->_softValidateStatus[$Model->alias];
		}
		return false;
	}
/**
  * Return the fields soft validated.
  * 
  * @return mixed 
  * 		array with fields name or false if model dont have soft validates.
  */
	public function getSoftValidationFields(&$Model){
		if(isset($this->_softValidateFields[$Model->alias])){
			return array_keys($this->_softValidateFields[$Model->alias]);
		}
		return false;
	}
/**
  * Return the soft validation and remove fields from
  * model validate to prevent validations on soft fields.
  *
  * @return array of soft validation rules
  */
	protected function _parseSoftValidates(){
		$softValidate = array();
		foreach ($this->__Model->validate as $field => $rules){
			foreach($rules as $rule){
				if(!is_array($rule)){
					continue;
				}
				if(isset($rule['soft']) && $rule['soft'] == true){
					$softValidate[$field] = $rules;
					unset($this->__Model->validate[$field]);
				}
			}
		}
		return $softValidate;
	}
/**
  * Set the Soft Validation status.
  *
  * @return boolean
  */
	protected function _softValidationUpdateStatus($status){
		$this->_softValidateStatus[$this->__Model->alias] = $status;
		return true;
	}

/**
  * Callbacks API.
  *
  * Called when all fields are filled.
  */
	public function beforeSaveSoftValidationSuccess(){	return true; }
/**
  * Called when soft fields is not filled.
  */
	public function beforeSaveSoftValidationFail(){ return true; }
}