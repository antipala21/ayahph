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
		// $this->Auth->allow(
		// 	'calendar',
		// 	'getCalendarData'
		// );
	}

	public function index() {
		$schedules = $this->Transaction->find('all', array(
			'fields' => array(
				'Transaction.*',
				'Agency.id',
				'Agency.name',
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
				'Transaction.status' => 1,
				'Transaction.user_id' => $this->Auth->user('id')
			),
			'order' => 'Transaction.id DESC'
		));

		$this->set('schedules', $schedules);

	}

	public function detail () {
		$schedule_id = isset($this->params['schedule_id']) ? $this->params['schedule_id'] : null;

		$schedule = $this->Transaction->find('first', array(
			'fields' => array(
				'Transaction.*',
				'Agency.name',
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
				'Transaction.id' => $schedule_id,
				'Transaction.status' => 1
			)
		));

		if (!$schedule) {
			return $this->redirect('/schedules');
		}
		$this->set('schedule', $schedule);
	}

	public function completeTransaction () {
		$this->autoRender = false;
		if ($this->request->is('post')) {
			$data = $this->request->data;
			if ($data['value_transaction'] == 'Complete') {
				$this->Transaction->clear();
				$this->Transaction->read(array('status'), $data['Schedule']['id']);
				$this->Transaction->set(array('status' => 2));
				if ($this->Transaction->save()) {
					return $this->redirect('/to_rate');
				}
			}
		}
	}

	public function to_rate () {

		if ($this->request->is('post')) {
			$data = $this->request->data;
			$rate = isset($data['rate']) ? $data['rate'] : 5;
			$data['Rating']['rate'] = $rate;
			$data['Rating']['user_id'] = $this->Auth->user('id');
			$this->NurseMaidRating->create();
			$this->NurseMaidRating->set($data['Rating']);
			if ($this->NurseMaidRating->save()) {
				// updat transaction status
				$this->Transaction->clear();
				$this->Transaction->read(array('status'), $data['Rating']['transaction_id']);
				$this->Transaction->set(array('status' => 3));
				$this->Transaction->save();
			}
		}

		$to_rate = $this->Transaction->find('all', array(
			'fields' => array(
				'Transaction.*',
				'Agency.*',
				'NurseMaid.id',
				'NurseMaid.phone_number',
				'NurseMaid.first_name',
				'NurseMaid.last_lname',
				'NurseMaid.address',
				'NurseMaid.image_url',
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
				'Transaction.status' => 2,
				'Transaction.user_id' => $this->Auth->user('id')
			)
		));
		$this->set('to_rate', $to_rate);

	}

	public function calendar () {

	}

	public function getCalendarData () {
		$this->autoRender = false;
		$result = array();

		$calendar_data = $this->Transaction->find('all', array(
			'fields' => array(
				'Transaction.*',
				'Agency.name',
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
				'Transaction.status' => 1,
				'Transaction.user_id' => $this->Auth->user('id')
			),
			'order' => 'Transaction.id DESC'
		));

		if ($calendar_data) {
			$_calendar_data = array();
			foreach ($calendar_data as $key => $value) {
				$_calendar_data[] = $value['Transaction'];

				$_calendar_data[$key]['start'] = date('Y-m-d', strtotime($value['Transaction']['transaction_start']));
				$_calendar_data[$key]['end'] = date('Y-m-d', strtotime($value['Transaction']['transaction_end']));
				$_calendar_data[$key]['url'] = '/schedule/detail/' . $value['Transaction']['id'];

				if (isset($value['NurseMaid']['first_name'])) {
					$_calendar_data[$key]['title'] = $value['NurseMaid']['first_name'];
				} else {
					$_calendar_data[$key]['title'] = 'Nursemaid';
				}
			}
			$result = $_calendar_data;
		}
		return json_encode($result);
	}

}
