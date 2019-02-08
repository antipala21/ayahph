<?php
$file = '../../config/const.php';
if(is_file($file)){
	require_once($file);
}

//include memcached config
$file = ROOT . '/config/memcached.php';
if(is_file($file)){
	include($file);
}

//include environment settings
$file = ROOT . '/config/environment.php';
if(is_file($file)){
	include($file);
}

//include aws s3 settings
$file = ROOT . '/config/aws_s3.php';
if(is_file($file)){
	include($file);
}