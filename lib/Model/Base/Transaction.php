<?php
App::uses('AppModel', 'Model');

class Transaction extends AppModel {
	public $useTable = 'transactions';

	public function hire_request_count ($id, $status) {
		return $this->find('count', array(
			'conditions' => array(
				'Transaction.status' => $status,
				'Transaction.agency_id' => $id
			),
			'order' => 'Transaction.id DESC'
		));
	}
}