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
			)
		));

		$announcement_id = isset($this->params['id']) ? $this->params['id'] : null;
		$status = isset($this->params['status']) ? $this->params['status'] : null;

		if ($announcement_id) {
			$this->Announcement->clear();
			$this->Announcement->read(array('status'), $announcement_id);
			$this->Announcement->set(array('status' => $status));
			$this->Announcement->save();
			return $this->redirect('/announcements');
		}

		$this->set('announcements', $announcements);
	}

}
