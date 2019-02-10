<?php
App::uses('AppController', 'Controller');
class AnnouncementController extends AppController {

	public $uses = array(
		'Announcement'
	);

	public function beforeFilter() {
		parent::beforeFilter();
	}

	public function index() {
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
			'conditions' => array('Announcement.status' => 0)
		));

		$this->set('announcements', $announcements);
		// myTools::display($announcements);
		// exit;
	}

}
