<?php
extract($fieldinfo);
if(!$value) $value = $defaultvalue;
$type = $ispassword ? 'password' : 'text';
$this->formValidator .= '$("#'.$field.'")';
if($errortips || $minlength) $this->formValidator .= '.formValidator({onfocus:"'.$errortips.'"}).inputValidator({min:'.$minlength. ($maxlength ? ', max:'.$maxlength : '') .', onerror:"'.$errortips.'"})';

if (!empty($pattern)) {
	$this->formValidator .= '.functionValidator({fun: function(value, _this) { return '.$pattern.'.test(value); }, onerror:\''. $errortips .'\'})';
}
$this->formValidator .= ';';
return '<input type="' . $type . '" name="info['.$field.']" id="'.$field.'" size="'.$size.'" value="'.$value.'" class="input-text" '.$formattribute.' '.$css.'>';