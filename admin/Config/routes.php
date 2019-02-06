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

	Router::connect('/login', array('controller' => 'Login', 'action' => 'index'));
	Router::connect('/transactions', array('controller' => 'Transaction', 'action' => 'index'));
	Router::connect('/users', array('controller' => 'User', 'action' => 'index'));
	Router::connect('/agencies', array('controller' => 'Agency', 'action' => 'index'));
	Router::connect('/nursemaid_ratings', array('controller' => 'NurseMaidRating', 'action' => 'index'));

	Router::connect('/agency-detail/:id', array('controller' => 'Agency', 'action' => 'detail'));
	Router::connect('/user-detail/:id', array('controller' => 'User', 'action' => 'detail'));
	Router::connect('/nursemaid_raing-detail/:id', array('controller' => 'NurseMaidRating', 'action' => 'detail'));

	Router::connect('/logout', array('controller' => 'Login', 'action' => 'logout'));

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
