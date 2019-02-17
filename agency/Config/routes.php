<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
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
 * @package       app.Config
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
 Router::parseExtensions();
	Router::connect('/', array('controller' => 'home', 'action' => 'index', 'home'));
/**
 * ...and connect the rest of 'Pages' controller's URLs.
 */
	Router::connect('/pages/*', array('controller' => 'pages', 'action' => 'display'));
	Router::connect('/register/*', array('controller' => 'Register', 'action' => 'index'));
	Router::connect('/register-legal-documents/', array('controller' => 'Register', 'action' => 'legalDocuments'));
	Router::connect('/register-legal-documents/delete', array('controller' => 'Account', 'action' => 'legalDocumentsDelete'));
	Router::connect('/checkEmail', array('controller' => 'Account', 'action' => 'checkEmail'));

	Router::connect('/logout', array('controller' => 'Account', 'action' => 'logout'));

	Router::connect('/nursemaid', array('controller' => 'NurseMaid', 'action' => 'index'));
	Router::connect('/nursemaid/add', array('controller' => 'NurseMaid', 'action' => 'add'));
	Router::connect('/nursemaid/detail/:nursemaid_id', array('controller' => 'NurseMaid', 'action' => 'detail'));
	Router::connect('/nursemaid/edit/:nursemaid_id', array('controller' => 'NurseMaid', 'action' => 'edit'));

	Router::connect('/announcement', array('controller' => 'Announcement', 'action' => 'index'));
	Router::connect('/announcement/add', array('controller' => 'Announcement', 'action' => 'add'));
	Router::connect('/announcement/detail/:id', array('controller' => 'Announcement', 'action' => 'detail'));

	Router::connect('/account', array('controller' => 'Account', 'action' => 'index'));
	Router::connect('/account/payment', array('controller' => 'Account', 'action' => 'payment'));
	Router::connect('/account/requirements', array('controller' => 'Account', 'action' => 'updateRequirements'));
	Router::connect('/account/ajax_image_upload', array('controller' => 'Account', 'action' => 'ajax_image_upload'));
	Router::connect('/account/ajax_nursemaid_image_upload', array('controller' => 'NurseMaid', 'action' => 'ajax_nursemaid_image_upload'));
	Router::connect('/account/success_card', array('controller' => 'Account', 'action' => 'success_card'));
	
	Router::connect('/transaction', array('controller' => 'Transaction', 'action' => 'index'));
	Router::connect('/transaction/detail/:transaction_id', array('controller' => 'Transaction', 'action' => 'detail'));

	Router::connect('/schedules', array('controller' => 'Schedule', 'action' => 'index'));
	Router::connect('/schedule/detail/:schedule_id', array('controller' => 'Schedule', 'action' => 'detail'));

	// Notification
	Router::connect('/notif/hire_request', array('controller' => 'Notification', 'action' => 'hire_request'));

	Router::connect('/token', array('controller' => 'Transaction', 'action' => 'token'));
	Router::connect('/payment', array('controller' => 'Transaction', 'action' => 'payment'));

	// view user data
	Router::connect('/user/:id', array('controller' => 'User', 'action' => 'index'));

	// history 
	Router::connect('/history', array('controller' => 'History', 'action' => 'index'));

/**
 * Load all plugin routes. See the CakePlugin documentation on
 * how to customize the loading of plugin routes.
 */
	CakePlugin::routes();

/**
 * Load the CakePHP default routes. Only remove this if you do not want to use
 * the built-in default routes.
 */
	require CAKE . 'Config' . DS . 'routes.php';
