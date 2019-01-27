<?php
App::uses('AppController', 'Controller');
class RegisterController extends AppController {

	public $uses = array(
		'User',
		'Agency',
		'AgencyLegalDocument'
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(
			'index',
			'token',
			'legalDocuments'
		);
	}

	public function index () {

		if ($this->request->is('post')) {
			$data = $this->request->data;

			$this->Agency->validate = false;
			$this->Agency->create();
			$this->Agency->set($data);
			$save = $this->Agency->save();
			if ($save) {

				$this->Session->write('user_id', $save['Agency']['id']);
				return $this->redirect('/register-legal-documents/');
			} else {
				exit('Fail');
			}
		}
	}

	public function token() {
		$this->autoRender = false;
		// echo 'shit';
		// echo json_encode(Braintree_ClientToken::generate());
	}

	public function legalDocuments () {
		if ($this->Auth->user('id')) {
			$user_id = $this->Auth->user('id');
		} elseif($this->Session->read('user_id')) {
			$user_id = $this->Session->read('user_id');
		} else {
			return $this->redirect('/logout');
		}
		$this->set('user_id', $user_id);

		if ($this->request->is('post')) {
			$data = $this->request->data;

			$files = $_FILES['file'];

			$result['success'] = true;

			if ($user_id) {
				foreach ($files['name'] as $key => $value) {

					$validextensions = array("jpeg", "jpg", "png");
					$ext = explode('.', basename($value));
					$file_extension = end($ext);

					$filename = $user_id . '-' . md5(uniqid()) . $key . '_' . 'file' . "." . $ext[count($ext) - 1];

					$target_path = 'img/agency_permit/' . $filename;

					if (($files["size"][$key] < 900000) && in_array($file_extension, $validextensions)) {
						if (move_uploaded_file($files['tmp_name'][$key], $target_path)) {
							$this->AgencyLegalDocument->create();
							$this->AgencyLegalDocument->set(array(
								'agency_id' => $user_id,
								'filename' => $filename
							));
							$this->AgencyLegalDocument->save();
						} else {
							$result['success'] = false;
						}
					} else {
						$result['success'] = false;
					}
				}
			}

			if ($result['success'] == true) {

				$this->Session->setFlash('Document upload success', 'default', array(), 'success');

				$save = $this->Agency->find('first', array(
					'conditions' => array('Agency.id' => $user_id)
				));

				$agency = $save['Agency'];
				$agency['type'] = 'agency';

				$this->Auth->login($agency);

				$this->Agency->clear();
				$this->Agency->read(array('business_permit_flg'), $user_id);
				$this->Agency->set(array('business_permit_flg' => 1));
				$this->Agency->save();

				return $this->redirect('/account/requirements');

			}

			return json_encode($result);
		}

	}

	private function uploadAgreement ($permit = array(), $filename) {
		move_uploaded_file(
			$permit['tmp_name'], 
			'img/agency_permit/'. $filename
		);
	}

}
