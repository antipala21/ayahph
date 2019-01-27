<?php

App::uses('Controller', 'Controller');

class AppController extends Controller {
	public $Session;
	public $uses = array('Agency');
	public $ext = '.php';
	public $components = array(
		'Paginator',
		'RequestHandler',
		'DebugKit.Toolbar',
		'Session',
		'Cookie',
		'Auth' => array(
			'authenticate' => array(
				'Form' => array(
					'fields' => array('username' => 'user_id', 'password' => 'password')
				)
			),
			'loginRedirect' => array('controller' => 'Agency', 'action' => 'index'),
			'logoutRedirect' => array('controller' => 'Login', 'action' => 'index'),
			'loginAction' => '/login',
			'authError' => 'Auth Error',
			'loginError' => 'Login Error'
		)
	);

	public function beforeFilter() {
		/*Configure Path*/
		App::build(array(
			'Model'=>array(CAKE_CORE_INCLUDE_PATH.'/Model/Base/',CAKE_CORE_INCLUDE_PATH.'/Model/',APP_DIR.'/Model/',CAKE_CORE_INCLUDE_PATH.'/Model/Form/'),
			'Lib'=>array(CAKE_CORE_INCLUDE_PATH.'/Lib/'),
			'Vendor'=>array(CAKE_CORE_INCLUDE_PATH.'/Vendor/')
		));

		/*Autoload Model*/

		/*Autoload Lib*/
		App::uses('myTools','Lib');
		App::uses('myMailer','Lib');
		App::uses('myError','Lib');
		Configure::load('const');

		// Braintree_Configuration::environment('sandbox');
		// Braintree_Configuration::merchantId('tnnc2y3sq3ctj5cb');
		// Braintree_Configuration::publicKey('9pzgf9x7z4g3hmz8');
		// Braintree_Configuration::privateKey('ee008d3d62a3f086dcb655424a3929d0');
		// sandbox_ypwwzxvh_tnnc2y3sq3ctj5cb

		/*Autoload table class*/
		spl_autoload_register(function($class){
			$classFile1 = CAKE_CORE_INCLUDE_PATH.'/Model/'.$class.'.php';
			$classFile2 = CAKE_CORE_INCLUDE_PATH.'/Model/Form/'.$class.'.php';
			if(is_file($classFile1)){ require_once($classFile1); }
			if(is_file($classFile2)){ require_once($classFile2); }
		});
	}
}
