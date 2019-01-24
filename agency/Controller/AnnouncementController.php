<?php
App::uses('AppController', 'Controller');
class AnnouncementController extends AppController {

	public $uses = array('Announcement');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('index');
	}

	public function index(){

		$announcements = $this->Announcement->find('all', array(
			'conditions' => array('Announcement.agency_id'=> $this->Auth->user('id'))
		));

		$this->set('announcements', $announcements);



		// exit('adf');
		// $data = $this->Auth->user('id');
		// $data = $this->Session->read('Auth.User');
		// myTools::display($data);
		// exit;
	}

	public function add () {
		if ($this->request->is('post')) {
			$data = $this->request->data;

			$data['Announcement']['agency_id'] = $this->Auth->user('id');

			$this->Announcement->set($data);

			if ($this->Announcement->save()) {
				$this->Session->setFlash('Adding Announcement Success', 'default', array(), 'announcement-maid-add');
				return $this->redirect('/announcement');
			}

			$this->Session->setFlash('Adding Announcement Fail', 'default', array(), 'announcement-maid-add-error');

		}
	}

}
