<?php
App::uses('AppController', 'Controller');
class AgencyController extends AppController {

	public $uses = array(
		'Agency',
		'User',
		'Admin',
		'AgencyLegalDocument'
	);

	public function beforeFilter() {
		parent::beforeFilter();
	}

	public function index(){

		$agencies = $this->Agency->find('all');
		// myTools::display($agencies);
		// exit;

		$this->set('agencies', $agencies);

	}

	public function detail () {
		$id = isset($this->params['id']) ? $this->params['id'] : null;
		$agency = $this->Agency->find('first', array(
			'conditions' => array('Agency.id' => $id)
		));

		$documents = $this->AgencyLegalDocument->find('all', array(
			'conditions' => array('AgencyLegalDocument.agency_id' => $id)
		));

		$this->set('documents', $documents);
		$this->set('agency', $agency);

		if ($this->request->is('post')) {
			$data = $this->request->data;
			$data_value = isset($data['data_value']) ? $data['data_value'] : 1;
			$this->Agency->clear();
			$this->Agency->read(array('status', 'display_flg'), $id);
			$this->Agency->set(array('status' => $data_value, 'display_flg' => $data_value));
			if ($this->Agency->save()) {
				$this->Session->setFlash('Agency Upated Success', 'default', array(), 'success');
				return $this->redirect('/agencies');
			} else {
				$this->Session->setFlash('Agency Update Fail.', 'default', array(), 'error');
			}
		}

	}

}
