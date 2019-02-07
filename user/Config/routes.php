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
	Router::connect('/register/legal-documents/*', array('controller' => 'Register', 'action' => 'legalDocuments'));
	Router::connect('/register-legal-documents/', array('controller' => 'Register', 'action' => 'legalDocuments'));
	Router::connect('/register-legal-documents/delete', array('controller' => 'Account', 'action' => 'legalDocumentsDelete'));
	Router::connect('/checkEmail', array('controller' => 'Account', 'action' => 'checkEmail'));
	
	Router::connect('/token', array('controller' => 'Register', 'action' => 'token'));

	Router::connect('/logout', array('controller' => 'Account', 'action' => 'logout'));

	Router::connect('/agency-detail/:id', array('controller' => 'AgencyDetail', 'action' => 'detail'));
	Router::connect('/agency-detail/announcement/:id', array('controller' => 'AgencyDetail', 'action' => 'announcement'));
	Router::connect('/agency-nursemaid/:agency_id', array('controller' => 'NurseMaidDetail', 'action' => 'index'));
	Router::connect('/agency-nursemaid-detail/:nursemaid_id', array('controller' => 'NurseMaidDetail', 'action' => 'detail'));

	Router::connect('/nursemaids', array('controller' => 'NurseMaid', 'action' => 'index'));

	// Transactions
	Router::connect('/ajax/send_hire_request', array('controller' => 'Transaction', 'action' => 'saveRequest'));

	Router::connect('/account/requirements', array('controller' => 'Account', 'action' => 'updateRequirements'));
	Router::connect('/account/ajax_image_upload', array('controller' => 'Account', 'action' => 'ajax_image_upload'));

	Router::connect('/schedules', array('controller' => 'Schedule', 'action' => 'index'));
	Router::connect('/schedule/detail/:schedule_id', array('controller' => 'Schedule', 'action' => 'detail'));
	Router::connect('/to_rate', array('controller' => 'Schedule', 'action' => 'to_rate'));
	Router::connect('/completeTransaction', array('controller' => 'Schedule', 'action' => 'completeTransaction'));

	// announcement
	Router::connect('/announcements', array('controller' => 'Announcement', 'action' => 'index'));
	
	// Notification
	Router::connect('/notif/hire_accept', array('controller' => 'Notification', 'action' => 'hire_accept'));

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
