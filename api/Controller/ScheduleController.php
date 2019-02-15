<?php
App::uses('AppController', 'Controller');
class ScheduleController extends AppController {

	public $uses = array(
		'NurseMaid',
		'Transaction',
		'NurseMaidRating'
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('index');
	}

	public function index() {
		$this->autoRender = false;

		if (!isset($this->request->data['params'])) {
			$response['success'] = false;
			return json_encode($response);
		}

		$request_data = json_decode(stripslashes($this->request->data['params']));
		$data = (array) $request_data;

		$schedules = $this->Transaction->find('all', array(
			'fields' => array(
				'Transaction.*',
				'Agency.id',
				'Agency.name',
				'Agency.phone_number',
				'NurseMaid.id',
				'NurseMaid.first_name'
			),
			'joins' => array(
				array(
					'table' => 'agencies',
					'alias' => 'Agency',
					'type' => 'LEFT',
					'conditions' => 'Agency.id = Transaction.agency_id'
				),
				array(
					'table' => 'nurse_maids',
					'alias' => 'NurseMaid',
					'type' => 'LEFT',
					'conditions' => 'NurseMaid.id = Transaction.nurse_maid_id'
				)
			),
			'conditions' => array(
				'Transaction.status' => $data['status'],
				'Transaction.user_id' => $data['user_id']
			),
			'order' => 'Transaction.transaction_start'
		));

		$response = array();

		if ($schedules) {
			$_schedules = array();
			foreach ($schedules as $key => $value) {
				$_schedules[] = $value['Transaction'];
				if (isset($value['Agency']['name'])) {
					$_schedules[$key]['agency_name'] = $value['Agency']['name'];
				}
				if (isset($value['Agency']['id'])) {
					$_schedules[$key]['agency_id'] = $value['Agency']['id'];
				}
				if (isset($value['Agency']['phone_number'])) {
					$_schedules[$key]['agency_phone_number'] = $value['Agency']['phone_number'];
				}
				if (isset($value['NurseMaid']['first_name'])) {
					$_schedules[$key]['nursemaid_name'] = $value['NurseMaid']['first_name'];
				}
				if (isset($value['NurseMaid']['id'])) {
					$_schedules[$key]['nurse_maid_id'] = $value['NurseMaid']['id'];
				}
			}
			$response['schedules'] = $_schedules;
		}
		return json_encode($response);

	}

	public function detail () {
		$schedule_id = isset($this->params['schedule_id']) ? $this->params['schedule_id'] : null;

		$schedule = $this->Transaction->find('first', array(
			'fields' => array(
				'Transaction.*',
				'Agency.name'
			),
			'joins' => array(
				array(
					'table' => 'agencies',
					'alias' => 'Agency',
					'type' => 'LEFT',
					'conditions' => 'Agency.id = Transaction.agency_id'
				)
			),
			'conditions' => array(
				'Transaction.id' => $schedule_id,
				'Transaction.status' => 1
			)
		));

		if (!$schedule) {
			return $this->redirect('/schedules');
		}
		$this->set('schedule', $schedule);
	}

}
