<?php
$value = str_replace(array("'",'"','(',')'),'',$value);
return trim($value);