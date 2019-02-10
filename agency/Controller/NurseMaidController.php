<?php
App::uses('AppController', 'Controller');
class NurseMaidController extends AppController {

	public $uses = array('NurseMaid');

	public function beforeFilter() {
		parent::beforeFilter();
		$member = $this->Agency->find('count', array(
			'conditions' => array(
				'Agency.id' => $this->Auth->user('id'),
				'Agency.status' => 0
			)
		));
		if ($member) {
			$this->Session->setFlash('Please enter payment card first.', 'default', array(), 'payment_msg');
			return $this->redirect('/account/payment');
		}
	}

	public function index(){
	$nursemaids = $this->NurseMaid->find('all', array(
			'conditions' => array('NurseMaid.agency_id' => $this->Auth->user('id'))
		));

		$this->set('nursemaids', $nursemaids);
	}

	public function add () {
		if ($this->request->is('post')) {
			$data = $this->request->data;

			$data['NurseMaid']['agency_id'] = $this->Auth->user('id');

			$this->NurseMaid->set($data);

			$save = $this->NurseMaid->save();
			if ($save) {
				$this->Session->setFlash('Adding NurseMaid Success', 'default', array(), 'nurse-maid-add');
				return $this->redirect('/nursemaid/detail/' . $save['NurseMaid']['id']);
			}

			$this->Session->setFlash('Adding NurseMaid Fail', 'default', array(), 'nurse-maid-add-error');

		}
	}

	public function detail () {

		header("Pragma-directive: no-cache");
		header("Cache-directive: no-cache");
		header("Cache-control: no-cache");
		header("Pragma: no-cache");
		header("Expires: 0");

		$nursemaid_id = isset($this->params['nursemaid_id']) ? $this->params['nursemaid_id'] : null;

		$nursemaid = $this->NurseMaid->find('first', array(
			'conditions' => array('NurseMaid.id' => $nursemaid_id)
		));

		if (!$nursemaid) {
			return $this->redirect('/nursemaid');
		}

		if ($this->request->is('post')) {
			$data = $this->request->data;

			if (isset($data['value_transaction']) && $data['value_transaction'] == 'Delete') {
				$this->NurseMaid->delete($data['NurseMaid']['id']);
				return $this->redirect('/nursemaid');
			} else {
				$new_status = isset($data['value_transaction']) && $data['value_transaction'] == 'Available' ? 1 : 0;
				$this->log('[new_status] ' . $new_status, 'debug');

				$this->NurseMaid->clear();
				$this->NurseMaid->read(array('status'), $data['NurseMaid']['id']);
				$this->NurseMaid->set(array('status' => $new_status)); // on hire , not active
				$this->NurseMaid->save();

				return $this->redirect('/nursemaid/detail/' . $nursemaid_id);
			}
		}

		$this->set('nurse_maid', $nursemaid['NurseMaid']);
	}

	public function edit () {

		header("Pragma-directive: no-cache");
		header("Cache-directive: no-cache");
		header("Cache-control: no-cache");
		header("Pragma: no-cache");
		header("Expires: 0");

		$nursemaid_id = isset($this->params['nursemaid_id']) ? $this->params['nursemaid_id'] : null;

		$nursemaid = $this->NurseMaid->find('first', array(
			'conditions' => array('NurseMaid.id' => $nursemaid_id)
		));

		if (!$nursemaid) {
			return $this->redirect('/nursemaid');
		}

		if ($this->request->is('post')) {
			$data = $this->request->data;

			$this->NurseMaid->clear();
			$this->NurseMaid->read(null, $data['NurseMaid']['id']);
			$this->NurseMaid->set($data);
			if ($this->NurseMaid->save()) {
				$this->Session->setFlash('NurseMaid Updated', 'default', array(), 'nurse-maid-edit');
			}
			return $this->redirect('/nursemaid');
		}

		$this->set('nurse_maid', $nursemaid['NurseMaid']);
	}

	public function ajax_nursemaid_image_upload () {
		$this->layout = false;
		$this->autoRender = false;

		if ($this->request->is('ajax')) {

			$data = $this->request->data['profile-image'];
			$nurse_maid_id = $this->request->data['nurse_maid_id'];

			list($type, $data) = explode(';', $data);
			list(, $data)      = explode(',', $data);
			$data = base64_decode($data);

			$fileName = $nurse_maid_id . '_' . 'nursemaid_profile' . '.jpg';

			file_put_contents('images/'. $fileName, $data);

			$this->NurseMaid->clear();
			$this->NurseMaid->read(null, $nurse_maid_id);
			$this->NurseMaid->saveField('image_url', $fileName);
			return true;
		}
	}

}
