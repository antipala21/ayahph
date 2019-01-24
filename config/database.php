<?php
class DATABASE_CONFIG {

	public $default = array(
		'datasource' => 'Database/Mysql',
		'persistent' => false,
		'host' => 'localhost',
		'login' => 'root',
		'password' => '',
		'database' => 'ayahph_db',
		'prefix' => '',
		'encoding' => 'utf8',
	);

	// public $default = array(
	// 	'datasource' => 'Database/Mysql',
	// 	'persistent' => false,
	// 	'host' => 'sql211.epizy.com',
	// 	'login' => 'epiz_22921037',
	// 	'password' => '0WdM6Xh8g3ZXzJ',
	// 	'database' => 'epiz_22921037_ayaph_db',
	// 	'prefix' => '',
	// 	'encoding' => 'utf8',
	// );

	public $test = array(
		'datasource' => 'Database/Mysql',
		'persistent' => false,
		'host'       => 'localhost',
		'login'      => 'root',
		'password'   => '123',
		'database'   => 'testtete',
		'prefix'     => '',
		//'encoding' => 'utf8',
	);
}
