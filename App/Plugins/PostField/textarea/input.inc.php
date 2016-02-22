<?php
if(!$this->fields[$field]['enablehtml']) $value = strip_tags($value);
return $value;