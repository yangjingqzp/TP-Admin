<?php
class content_input {
	public $modelid;
	public $fields;
	public $data;

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
	}

	public function getModelFields($modelid) {
		return model('ModelField')->getFieldsByModelID($modelid);
	}

	public function getPageFields($pageTemplate) {
		return model('PageField')->getFields($pageTemplate);
	}

	function get($data,$isimport = 0) {
		$this->data = $data = trim_script($data);
		$info = array();
		foreach($data as $field=>$value) {
			if(!isset($this->fields[$field]) && !check_in($field,'paytype,paginationtype,maxcharperpage,id')) continue;
			$name = $this->fields[$field]['name'];
			$minlength = $this->fields[$field]['minlength'];
			$maxlength = $this->fields[$field]['maxlength'];
			$pattern = $this->fields[$field]['pattern'];
			$errortips = $this->fields[$field]['errortips'];
			if(empty($errortips)) $errortips = $name.' 不符合要求';
			$length = empty($value) ? 0 : (is_string($value) ? strlen($value) : count($value));

			if($minlength && $length < $minlength) {
				if($isimport) {
					return false;
				} else {
					showmessage($name.' 不得少于 '.$minlength. ' 字符');
				}
			}
			if($maxlength && $length > $maxlength) {
				if($isimport) {
					$value = str_cut($value,$maxlength,'');
				} else {
					showmessage($name.' 不得多于 '.$maxlength. ' 字符');
				}
			} elseif($maxlength) {
				$value = str_cut($value,$maxlength,'');
			}
			if($pattern && $length && !preg_match($pattern, $value) && !$isimport) {
				showmessage($errortips);
			}

			// 附加函数验证
			$func = $this->fields[$field]['formtype'];

			$value = $this->$func($field, $value);

			$info['system'][$field] = $value;
		}
		//颜色选择为隐藏域 在这里进行取值
		if($_POST['style_color']) $info['system']['style'] = $_POST['style_color'] ? strip_tags($_POST['style_color']) : '';
		if($_POST['style_font_weight']) $info['system']['style'] = $info['system']['style'].';'.strip_tags($_POST['style_font_weight']);
		return $info;
	}

	function text($field, $value) {
		if($this->fields[$field]['ispassword']) $value = md5($value);
		return $value;
	}

	public function __call($name, $arguments) {
		list($field, $value) = $arguments;
		return file_exists(PLUGINS_PATH . 'PostField' . DS . $name . DS . 'input.inc.php') ? include PLUGINS_PATH . 'PostField' . DS . $name . DS . 'input.inc.php' : $value;
	}
}