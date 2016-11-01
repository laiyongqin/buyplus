<?php
function getConfig($key, $default = null)
{
	S(['type'=>'File']);
	if (! $value = S('setting_' . $key)) {
    	$value = M('Setting')->where(['key' => $key])->getField('value');
		S('setting_' . $key, $value);
	}
    return is_null($value) ? $default : $value;
}
