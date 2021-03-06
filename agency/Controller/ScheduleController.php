<?php
App::uses('AppController', 'Controller');
class ScheduleController extends AppController {

	public $uses = array(
		'NurseMaid',
		'Transaction'
	);

	public function beforeFilter() {
		parent::beforeFilter();
	}

	public function index() {
		$schedules = $this->Transaction->find('all', array(
			'fields' => array(
				'Transaction.*',
				'User.display_name'
			),
			'joins' => array(
				array(
					'table' => 'users',
					'alias' => 'User',
					'type' => 'LEFT',
					'conditions' => 'User.id = Transaction.user_id'
				)
			),
			'conditions' => array(
				'Transaction.status' => 1,
				'Transaction.agency_id' => $this->Auth->user('id')
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
				'User.display_name'
			),
			'joins' => array(
				array(
					'table' => 'users',
					'alias' => 'User',
					'type' => 'LEFT',
					'conditions' => 'User.id = Transaction.user_id'
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
