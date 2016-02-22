<?php
extract(string2array($fieldinfo['setting']));
$list_str = '';
if($value) {
	$value = string2array(html_entity_decode($value,ENT_QUOTES));
	if(is_array($value)) {
		foreach($value as $_k=>$_v) {
			$list_str .= "<div id='multifile{$_k}'><input type='text' name='{$field}_fileurl[]' value='{$_v[fileurl]}' style='width:310px;' class='input-text'> <input type='text' name='{$field}_filename[]' value='{$_v[filename]}' style='width:160px;' class='input-text'> <a href=\"javascript:remove_div('multifile{$_k}')\">移除</a></div>";
		}
	}
}
$string = '<input name="info['.$field.']" type="hidden" value="1">
<fieldset class="blue pad-10">
<legend>文件列表</legend>';
$string .= $list_str;
$string .= '<ul id="'.$field.'" class="picList"></ul>
</fieldset>
<div class="bk10"></div>
';
$string .= $str."<input type=\"button\"  class=\"button\" value=\"多文件上传\" onclick=\"javascript:flashupload('{$field}_multifile', '附件上传','{$field}',change_multifile,'{$upload_number},{$upload_allowext},{$isselectimage}','content','','{$authkey}')\"/>    <input type=\"button\" class=\"button\" value=\"添加远程地址\" onclick=\"add_multifile('{$field}')\">";
return $string;