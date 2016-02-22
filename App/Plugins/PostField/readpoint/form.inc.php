<?php
$paytype = $this->data['paytype'];
if($paytype) {
	$checked1 = '';
	$checked2 = 'checked';
} else {
	$checked1 = 'checked';
	$checked2 = '';
}
return '<input type="text" name="info['.$field.']" value="'.$value.'" size="5"><input type="radio" name="info[paytype]" value="0" '.$checked1.'> 点 <input type="radio" name="info[paytype]" value="1" '.$checked2.'>元';