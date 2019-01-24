<?php
class EmailConfig {
	public $default = array(
		'transport' => 'Mail',
		'from' => array('noreply@test.net'=>'Test.net'),
		'additionalParameters' => '-f return@test.net',
	);
}
