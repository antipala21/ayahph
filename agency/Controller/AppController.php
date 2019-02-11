<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {
	public $Session;
	public $uses = array('Agency', 'Transaction');
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
					'fields' => array('username' => 'email', 'password' => 'password')
				)
			),
			'loginRedirect' => array('controller' => 'Account', 'action' => 'index'),
			'logoutRedirect' => array('controller' => 'login', 'action' => 'index'),
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

		// Braintree
		App::import('Vendor', 'autoload');

		Braintree_Configuration::environment('sandbox');
		Braintree_Configuration::merchantId('tnnc2y3sq3ctj5cb');
		Braintree_Configuration::publicKey('9pzgf9x7z4g3hmz8');
		Braintree_Configuration::privateKey('ee008d3d62a3f086dcb655424a3929d0');

		/*Autoload table class*/
		spl_autoload_register(function($class){
			$classFile1 = CAKE_CORE_INCLUDE_PATH.'/Model/'.$class.'.php';
			$classFile2 = CAKE_CORE_INCLUDE_PATH.'/Model/Form/'.$class.'.php';
			if(is_file($classFile1)){ require_once($classFile1); }
			if(is_file($classFile2)){ require_once($classFile2); }
		});

		// NOtification
		$this->set('hire_request_count', $this->Transaction->hire_request_count($this->Auth->user('id'), 0));
	}
}
