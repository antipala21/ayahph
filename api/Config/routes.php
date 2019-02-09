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
Router::connect('/users/update', array('controller' => 'UsersUpdate', 'action' => 'index'));
Router::connect('/users/show', array('controller' => 'UsersShow', 'action' => 'index'));
Router::connect('/users/register_notification_token', array('controller' => 'UsersRegisterNotification', 'action' => 'index'));
Router::connect('/users/unregister_notification_token', array('controller' => 'UsersUpdateNotification', 'action' => 'unregisterDeviceToken'));
Router::connect('/users/subscribe', array('controller' => 'UsersDeviceSubscription', 'action' => 'subscribeDevice'));
Router::connect('/users/unsubscribe', array('controller' => 'UsersDeviceSubscription', 'action' => 'unsubscribeDevice'));
Router::connect('/users/update_notification', array('controller' => 'UsersUpdateNotification', 'action' => 'index'));
Router::connect('/users/notification_setting', array('controller' => 'UsersNotificationSetting', 'action' => 'index'));
Router::connect('/users/notification_setting_update', array('controller' => 'UsersNotificationSetting', 'action' => 'update'));
Router::connect('/users/check_notification_endpoint', array('controller' => 'UsersUpdateNotification', 'action' => 'checkNotificationEndpoint'));
Router::connect('/users/create_notification_endpoint', array('controller' => 'UsersUpdateNotification', 'action' => 'createNotificationEndpoint'));
Router::connect('/users/questionnare', array('controller' => 'UsersQuestionnare', 'action' => 'index'));
Router::connect('/users/updateCmcode', array('controller' => 'UsersUpdateCmcode', 'action' => 'index'));
Router::connect('/users/timezones', array('controller' => 'UsersTimezones', 'action' => 'index'));
Router::connect('/users/cosmopier', array('controller' => 'UsersShow', 'action' => 'cosmopier'));
Router::connect('/users/cosmopier-check', array('controller' => 'UsersShow', 'action' => 'cosmopier_check'));
Router::connect('/users/country_list', array('controller' => 'UsersCountryList', 'action' => 'index'));
Router::connect('/users/nationality_list', array('controller' => 'UsersNationalityList', 'action' => 'index'));

//Teachers
Router::connect('/teachers/reviews', array('controller' => 'TeachersReviews', 'action' => 'index'));
Router::connect('/teachers/search', array('controller' => 'TeachersSearch', 'action' => 'search'));
Router::connect('/teachers/detail', array('controller' => 'TeachersDetail', 'action' => 'detail'));
Router::connect('/teachers/favorite', array('controller' => 'TeachersFavorite', 'action' => 'index'));
Router::connect('/teachers/ranking', array('controller' => 'TeachersRanking', 'action' => 'index'));
Router::connect('/teachers/slot', array('controller' => 'TeachersSlots', 'action' => 'index'));
Router::connect('/teachers/search/textbook_categories', array('controller' => 'TeachersSearch', 'action' => 'getTextbookCategoriesSearchItems'));
Router::connect('/teachers/first_recommend', array('controller' => 'TeachersFirstRecommend', 'action' => 'index'));
Router::connect('/teachers/search/categories', array('controller' => 'TeachersSearchCategories', 'action' => 'search'));
Router::connect('/teachers/counselor_detail', array('controller' => 'TeachersCounselorDetail', 'action' => 'counselorDetail'));
Router::connect('/teachers/counselor_reviews', array('controller' => 'TeachersCounselorReviews', 'action' => 'index'));
Router::connect('/teachers/recommend', array('controller' => 'TeachersRecommend', 'action' => 'index'));
Router::connect('/lecturer/device_register', array('controller' => 'TeachersDeviceNotification', 'action' => 'index'));
Router::connect('/lecturer/device_unregister', array('controller' => 'TeachersDeviceNotification', 'action' => 'unregister'));
Router::connect('/lecturer/app_login', array('controller' => 'TeacherAppLogin', 'action' => 'index'));
//Counselors
Router::connect('/teachers/counselor_slot', array('controller' => 'CounselorsSlots', 'action' => 'index'));

Router::connect('/reservations/cancel', array('controller' => 'ReservationsCancel', 'action' => 'cancel'));
Router::connect('/reservations/list', array('controller' => 'ReservationsList', 'action' => 'index'));
Router::connect('/reservations/create', array('controller' => 'ReservationsCreate', 'action' => 'index'));
Router::connect('/reservations/textbook_change', array('controller' => 'ReservationsTextbookChange', 'action' => 'index'));
Router::connect('/reservations/couseling_create', array('controller' => 'CounselingReservation', 'action' => 'index'));

//Textbooks
Router::connect('/textbooks/list', array('controller' => 'TextBookLists', 'action' => 'index'));
Router::connect('/textbooks/update_viewed_page', array('controller' => 'TextbookUpdateViewedPage', 'action' => 'index'));
Router::connect('/textbooks/update_preselection_textbook', array('controller' => 'TextbookUpdatePreset', 'action' => 'index'));
Router::connect('/textbooks/reservation_textbook', array('controller' => 'TextBookLists', 'action' => 'reservationTextbook'));
Router::connect('/textbooks/all', array('controller' => 'TextBookLists', 'action' => 'all'));
Router::connect('/textbooks/info', array('controller' => 'TextBookLists', 'action' => 'info'));

//Lessons
Router::connect('/lesson/translate', array('controller' => 'LessonTranslate', 'action' => 'translate'));
Router::connect('/lesson/start', array('controller' => 'LessonStart', 'action' => 'index'));
Router::connect('/lesson/counseling_start', array('controller' => 'LessonStart', 'action' => 'counselingStart'));
Router::connect('/lesson/nowtime', array('controller' => 'LessonNowtime', 'action' => 'nowtime'));
Router::connect('/lesson/messages', array('controller' => 'LessonMessages', 'action' => 'index'));
Router::connect('/lesson/messages_finish_read', array('controller' => 'LessonMessagesFinishRead', 'action' => 'index'));
Router::connect('/lesson/endtime', array('controller' => 'LessonEndTime', 'action' => 'index'));
Router::connect('/lesson/review', array('controller' => 'LessonReview', 'action' => 'index'));
Router::connect('/lesson/update_onair_textbooks', array('controller' => 'LessonUpdateOnair', 'action' => 'index'));
Router::connect('/lesson/post_memo', array('controller' => 'LessonPostMemo', 'action' => 'index'));
Router::connect('/lesson/force_terminate', array('controller' => 'LessonForceTerminate', 'action' => 'index'));
Router::connect('/lesson/useful_phrases', array('controller' => 'LessonUsefulPhrases', 'action' => 'index'));


// Memo
Router::connect('/memos/update', array('controller' => 'MemoUpdate', 'action' => 'index'));
Router::connect('/memos/create', array('controller' => 'MemoCreate', 'action' => 'index'));
Router::connect('/memos/list', array('controller' => 'MemoList', 'action' => 'index'));
Router::connect('/memos/delete', array('controller' => 'MemoDelete', 'action' => 'index'));

Router::connect('/users/login', array('controller' => 'userslogin', 'action' => 'index'));
Router::connect('/users/update', array('controller' => 'usersupdate', 'action' => 'index'));
Router::connect('/users/check_unique_time', array('controller' => 'CheckUniqueTime', 'action' => 'index'));

Router::connect('/coins/review', array('controller' => 'UsersCoin', 'action' => 'review'));
Router::connect('/coins/reviewed', array('controller' => 'UsersCoin', 'action' => 'reviewed'));
Router::connect('/coins/show', array('controller' => 'UsersCoin', 'action' => 'show'));

Router::connect('/teachers/cronjob', array('controller' => 'TeachersCronJob', 'action' => 'execute'));

Router::connect('/notifications/cancel', array('controller' => 'NotificationCancelList', 'action' => 'index'));

/* twilio */
Router::connect('/twilio/servers', array('controller' => 'Twilio', 'action' => 'index'));

/* Port */
Router::connect('/port/info', array('controller' => 'Port', 'action' => 'show'));

// Payment
Router::connect('/payment/purchase', array('controller' => 'PaymentPurchase', 'action' => 'index'));
Router::connect('/payment/application_plan', array('controller' => 'PaymentApplicationPlan', 'action' => 'index'));

/* Lesson History */
Router::connect('/lesson/history', array('controller' => 'LessonHistory', 'action' => 'index'));
Router::connect('/lesson/historycount', array('controller' => 'LessonHistory', 'action' => 'historyCount'));


/* Lesson Chat Log List */
Router::connect('/lesson/chatlog', array('controller' => 'LessonChatLog', 'action' => 'index'));
Router::connect('/lesson/chatlog_list', array('controller' => 'LessonChatLog', 'action' => 'chatLogList'));

// Wordbooks
Router::connect('/wordbooks/list', array('controller' => 'WordBooks', 'action' => 'lists'));
Router::connect('/wordbooks/create', array('controller' => 'WordBooks', 'action' => 'create'));
Router::connect('/wordbooks/update', array('controller' => 'WordBooks', 'action' => 'update'));
Router::connect('/wordbooks/delete', array('controller' => 'WordBooks', 'action' => 'delete'));

// Announcement
Router::connect('/announcement/important_info', array('controller' => 'AnnouncementImportantInfo', 'action' => 'index'));
Router::connect('/announcement/info_finish_read', array('controller' => 'AnnouncementInfoFinishRead', 'action' => 'index'));
Router::connect('/announcement/info_count', array('controller' => 'AnnouncementInfoCount', 'action' => 'index'));

//slack trouble
Router::connect('/slack/lesson_trouble', array('controller' => 'SlackTrouble', 'action' => 'index'));

Router::connect('/announcement/banner', array('controller' => 'AnnouncementBanner', 'action' => 'banner'));

Router::connect('/users/purchase_done', array('controller' => 'PurchaseDone', 'action' => 'index'));

// NC- 5399 Campaign Settings
Router::connect('/users/campaign', array('controller'=> 'UserCampaign', 'action'=>'index'));
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
