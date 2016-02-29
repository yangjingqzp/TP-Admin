<?php
class content_form {
	public $modelid;
	public $pageTemplate;
	public $fields;
	public $id;
	public $formValidator;
	public $siteid;

	public function __construct($data, $type=1) {
		switch ($type) {
			case 1:
				$this->modelid = $data;
				$this->fields = $this->getModelFields($data);
				break;
			case 2:
				$this->pageTemplate = $data;
				$this->fields = $this->getPageFields($data);
				break;
			default:
				$this->modelid = $data;
				$this->fields = $this->getModelFields($data);
				break;
		}
		$this->siteid = get_siteid();
	}

	public function getModelFields($modelid) {
		return model('ModelField')->getFieldsByModelID($modelid);
	}

	public function getPageFields($pageTemplate) {
		return model('PageField')->getFields($pageTemplate);
	}

	public function get($data = array()) {
		$this->data = $data;
		if(isset($data['id'])) $this->id = $data['id'];
		$info = array();
		foreach($this->fields as $field=>$v) {
			if(defined('IN_ADMIN')) {
				if($v['iscore']) continue;
			}
			$func = $v['formtype'];

			$value = isset($data[$field]) ? new_addslashes($data[$field]) : '';
			// if(!method_exists($this, $func)) continue;
			$form = $this->$func($field, $value, $v);
			if ($form === false) {
				continue;
			}
			$star = $v['minlength'] || $v['pattern'] ? 1 : 0;
			$info[$field] = array('name'=>$v['name'], 'tips'=>$v['tips'], 'form'=>$form, 'star'=>$star,'isomnipotent'=>$v['isomnipotent'],'formtype'=>$v['formtype']);
		}
		return $info;
	}

	/*function omnipotent($field, $value, $fieldinfo) {
		extract($fieldinfo);
		eval("\$formtext = \"$formtext\";");
		$formtext .= '<input type="' . ($ishide ? 'hidden' : $fieldtype) . '" name="info['.$field.']" id="'.$field.'" value="'.$value.'" class="omnipotent-'.$field.'">';

		$errortips = $this->fields[$field]['errortips'];
		if($errortips) $this->formValidator .= '$("#'.$field.'").formValidator({onshow:"",onfocus:"'.$errortips.'"}).inputValidator({min:'.$minlength.',max:'.$maxlength.',onerror:"'.$errortips.'"});';

		if($errortips) $this->formValidator .= '$("#'.$field.'").formValidator({onshow:"'.$errortips.'",onfocus:"'.$errortips.'"}).inputValidator({min:1,onerror:"'.$errortips.'"});';
		return $formtext;
	}*/

	public function __call($name, $arguments) {
		list($field, $value, $fieldinfo) = $arguments;
		return file_exists(PLUGINS_PATH . 'PostField' . DS . $name . DS . 'form.inc.php') ? include PLUGINS_PATH . 'PostField' . DS . $name . DS . 'form.inc.php' : false;
	}
}