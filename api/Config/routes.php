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

//Users
Router::connect('/users/login', array('controller' => 'UsersLogin', 'action' => 'index'));
Router::connect('/users/agencies', array('controller' => 'Agency', 'action' => 'index'));
Router::connect('/users/agency/detail', array('controller' => 'Agency', 'action' => 'detail'));
Router::connect('/users/nursemaids', array('controller' => 'NurseMaid', 'action' => 'index'));
Router::connect('/users/nursemaid/detail', array('controller' => 'NurseMaid', 'action' => 'detail'));
Router::connect('/users/schedules', array('controller' => 'Schedule', 'action' => 'index'));

// Acount
Router::connect('/users/acount', array('controller' => 'Account', 'action' => 'index'));
Router::connect('/users/acount/update', array('controller' => 'Account', 'action' => 'update'));
Router::connect('/users/acount/update_image', array('controller' => 'Account', 'action' => 'update_image'));


Router::connect('/users/address', array('controller' => 'Lungsod', 'action' => 'index')); // wala
Router::connect('/users/request_hire', array('controller' => 'Transaction', 'action' => 'index'));

// add rate
Router::connect('/users/transaction/add_rate', array('controller' => 'Transaction', 'action' => 'add_rate'));

// notification
Router::connect('/users/notification', array('controller' => 'Notification', 'action' => 'index'));

// Announcements
Router::connect('/users/announcements', array('controller' => 'Announcement', 'action' => 'index'));

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
