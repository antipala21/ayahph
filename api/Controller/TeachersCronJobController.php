<?php
App::uses('AppController', 'Controller');
class TeachersCronJobController extends AppController{
	public $uses = array('Teacher','TeacherRatingsLesson');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('execute');
	}

	public function execute() {
		$this->autoRender = false;

		$teachers = $this->Teacher->find('list',array(
			'fields'			=> array('Teacher.id'),
			'conditions'	=> array('Teacher.status'	=> 1),
			'limit'				=> 50
			)
		);

		$insertedData = array();

		$this->TeacherRatingsLesson->deleteAll(array(1=>1));

		foreach($teachers as $teacher) {
			
			$exist = $this->TeacherRatingsLesson->find('first',array(
				'fields'			=> array(
					'TeacherRatingsLesson.id',
					'TeacherRatingsLesson.created_date'
				),
				'conditions'	=> array('TeacherRatingsLesson.id')
				)
			);

			$data = array(
				'teacher_id'			=> $teacher,
				'rating'					=> 0,
				'lessons'					=> 10,		
				'modified_date'		=> date('Y-m-d H:i:s'),
				'created_date'		=> date('Y-m-d H:i:s')
			);
			
			if ($exist) {

				$data['created_date']	= $exist['TeacherRatingsLesson']['created_date'];
				pr($data);

			}

			$insertedData[] = $data;

		}

		//$this->TeacherRatingsLesson->SaveAll($insertedData);

	}

}