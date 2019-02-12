<?php
App::uses('AppController', 'Controller');
class LungsodController extends AppController {

	public $uses = array(
		'Lungsod'
	);

	public function beforeFilter() {
		parent::beforeFilter();
	}

	public function index(){

		$lungsod = $this->Lungsod->find('all');
		$this->set('lungsod', $lungsod);
	}

	public function add () {
		if ($this->request->is('post')) {
			$data = $this->request->data;
			$search_key = strtolower(str_replace(array(' ', '-', '/'), '_', $data['Lungsod']['name']));
			$check = $this->Lungsod->find('count', array(
				'conditions' => array('Lungsod.search_key' => $search_key)
			));
			if (!$check) {
				$data['Lungsod']['search_key'] = $search_key;
				$this->Lungsod->create();
				$this->Lungsod->set($data);
				$this->Lungsod->save();
			}
			return $this->redirect('/municipal');
		}
	}
}
