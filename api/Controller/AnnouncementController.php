<?php
App::uses('AppController', 'Controller');
class AnnouncementController extends AppController {

	public $uses = array(
		'Announcement'
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('index');
	}

	public function index() {
		$this->autoRender = false;
		$response = array();

		$announcements = $this->Announcement->find('all', array(
			'fields' => array(
				'Announcement.*',
				'Agency.name',
				'Agency.image_url'
			),
			'joins' => array(
				array(
					'table' => 'agencies',
					'alias' => 'Agency',
					'type' => 'LEFT',
					'conditions' => 'Agency.id = Announcement.agency_id'
				)
			),
			'conditions' => array('Announcement.status' => 1)
		));

		$this->log('[announcements] ' . json_encode($announcements), 'debug');

		if ($announcements) {
			$_announcements = array();
			foreach ($announcements as $key => $value) {
				$_announcements[] = $value['Announcement'];
				if (isset($value['Agency']['name'])) {
					$_announcements[$key]['agency_name'] = $value['Agency']['name'];
				}
				if (isset($value['Agency']['image_url'])) {
					$_announcements[$key]['agency_image_url'] = $value['Agency']['image_url'];
				} else {
					$_announcements[$key]['agency_image_url'] = 'picture.jpg';
				}
			}
			$response['announcements'] = $_announcements;
		}
		return json_encode($response);
	}
}
