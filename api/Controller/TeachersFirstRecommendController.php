<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');

class TeachersFirstRecommendController extends AppController {
	public $uses = array(
		'Teacher', 
		'User', 
		'LessonOnair',
		'LessonOnairsLog',
		'PhoneVerifyCheckLog',
		'LessonSchedule'
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('index'));
		$this->autoRender = false;
		$this->layout = false;
	}
	
	/** 
	* return the recommended teacher for first time lesson  
	* @param json - [string] users_api_token 
	* @return json - [int] recommended_teacher_type, [int] teacher_id				 
	*/
	public function index() {
		if ($user = $this->validateUser()) {
			return $this->getRecommendedTeacher($user['id']);
		} else {
			return json_encode(array(
				"recommend" => 0
			));
		}
	}

	/**
	* validate user 
	* @param array [users_api_token]
	* @return boolean
	*/
	private function validateUser() {
		//validates data
		if ($this->request->is('post') && $this->request->input()) {
			$data = json_decode($this->request->input(),true);
			$apiCommon = new ApiCommonController();
			if (!$data) {
				return false;
			} else if (empty($data['users_api_token'])) {
				return false;
			} else {
				$user = $apiCommon->validateToken($data['users_api_token']);
				//NC-2778_2 check if user has previous lesson, check if user is authed and if user can start a lesson.
				if (!$user) {
					return false;
				} else {
					$newUserObj = new UserTable($user);
					$membership = $newUserObj->getUserMembership();
					$isAuthed = $this->PhoneVerifyCheckLog->userIsAuthed($user);
					$hasLessons = $this->LessonOnairsLog->getLatestLesson($user['id']);
					$hasReservations = $this->LessonSchedule->countAllLessonSchedules($user['id']);
					if(!$isAuthed || count($hasLessons) > 0 || $membership != 'paid_user' || $hasReservations > 0) {
						return ($user['admin_flg'] && count($hasLessons) == 0 && $isAuthed && $hasReservations == 0) ? $user : false;
					} else {
						return $user;
					}
				}
			}
		} else {
			return false;
		}
	}

	/**
	* get recommended teacher 
	* @return int recommend_teacher_type , int teacher_id
	*/
	private function getRecommendedTeacher($userId) {
		if (empty($userId) && ctype_digit($userId)) {
			return json_encode(array(
				'recommend' => 0
			));
		}

		//get teacher_id
		$blockList = BlockListTable::getBlocks($userId);

		//recommend_teacher_type - 1 : counselors available for lesson
		$teacherData = $this->LessonOnair->find('first', array(
			'fields' => 'Teacher.id',
			'conditions' => array(
				'Teacher.status = 1',
				'Teacher.stealth_flg = 0',
				'LessonOnair.status = 1',
				'LessonOnair.connect_flg = 1',
				'NOT' => array('Teacher.id' => $blockList)
			),
			'joins' => array(
					array(
						'type' => 'inner',
						'table' => 'teachers',
						'alias' => 'Teacher',
						'conditions' => array(
								'LessonOnair.teacher_id = Teacher.id',
								'Teacher.status = 1',
								'Teacher.stealth_flg = 0',
								'Teacher.counseling_flg = 1' 
							)
						)
					)
			));
		if (isset($teacherData['Teacher']['id'])) {
			return json_encode(array(
				"recommend" => 1,
				"recommend_teacher_type" => 1,
				"id" => intval($teacherData['Teacher']['id']) 
			));
		}

		//recommend_teacher_type - 2 : teacher available for lesson with above 4.6 ratings

		// eval 4.8 and can lesson 25 mins
		$teacherData = $this->LessonOnair->find('first', array(
				'fields' => 'Teacher.id',
				'conditions' => array(
						'Teacher.status = 1',
						'Teacher.stealth_flg = 0',
						'LessonOnair.status = 1',
						'LessonOnair.connect_flg = 1',
						'TeacherBadge.textbook_category_id  = 45',
						'OR' => array(
							'(SELECT TIME_TO_SEC(TIMEDIFF(lesson_time, NOW()))
						     FROM lesson_schedules 
						     WHERE teacher_id = LessonOnair.teacher_id AND lesson_time > NOW() 
						     AND status = 1 ORDER BY lesson_time ASC LIMIT 1) > 1830',		
						     '(SELECT COUNT(*) FROM lesson_schedules
						     WHERE teacher_id = LessonOnair.teacher_id AND lesson_time > NOW() 
						     AND status = 1) = 0'
						),
						'NOT' => array('Teacher.id' => $blockList)
					),
				'joins' => array(
					array(
						'type' => 'INNER',
						'table' => 'teacher_ratings_lessons',
						'alias' => 'TeacherRatingsLesson',
						'conditions' => array(
							'LessonOnair.status = 1',
							'LessonOnair.connect_flg = 1',
							'LessonOnair.teacher_id = TeacherRatingsLesson.teacher_id',
							'TeacherRatingsLesson.ratings >= 4.8'
							)
						),
					array(
						'type' => 'INNER',
						'table' => 'teachers',
						'alias' => 'Teacher',
						'conditions' => array(
							'Teacher.status = 1',
							'LessonOnair.teacher_id = Teacher.id',
							'Teacher.counseling_flg = 0' 
							)
						),
					array(
						'type' => 'INNER',
						'table' => 'teacher_badges',
						'alias' => 'TeacherBadge',
						'conditions' => array(
							'Teacher.id = TeacherBadge.teacher_id'
							)
						)						
					),
				'order' => array(
					'TeacherRatingsLesson.ratings DESC'
			)));
		
		if (isset($teacherData['Teacher']['id'])) {
			return json_encode(array(
				"recommend" => 1,
				"recommend_teacher_type" => 2,
				"id" => intval($teacherData['Teacher']['id']) 
			));
		}


		// eval 4.8 and can lesson for 10mins
		$teacherData = $this->LessonOnair->find('first', array(
				'fields' => 'Teacher.id',
				'conditions' => array(
						'Teacher.status = 1',
						'Teacher.stealth_flg = 0',
						'LessonOnair.status = 1',
						'LessonOnair.connect_flg = 1',
						'TeacherBadge.textbook_category_id = 45',
						'OR' => array(
							'(SELECT TIME_TO_SEC(TIMEDIFF(lesson_time, NOW()))
						     FROM lesson_schedules 
						     WHERE teacher_id = LessonOnair.teacher_id AND lesson_time > NOW() 
						     AND status = 1 ORDER BY lesson_time ASC LIMIT 1) > 930',		
						     '(SELECT COUNT(*) FROM lesson_schedules
						     WHERE teacher_id = LessonOnair.teacher_id AND lesson_time > NOW() 
						     AND status = 1) = 0'
						),
						'NOT' => array('Teacher.id' => $blockList)
					),
				'joins' => array(
					array(
						'type' => 'INNER',
						'table' => 'teacher_ratings_lessons',
						'alias' => 'TeacherRatingsLesson',
						'conditions' => array(
							'LessonOnair.status = 1',
							'LessonOnair.connect_flg = 1',
							'LessonOnair.teacher_id = TeacherRatingsLesson.teacher_id',
							'TeacherRatingsLesson.ratings >= 4.8'
							)
						),
					array(
						'type' => 'INNER',
						'table' => 'teachers',
						'alias' => 'Teacher',
						'conditions' => array(
							'Teacher.status = 1',
							'Teacher.stealth_flg = 0',
							'LessonOnair.teacher_id = Teacher.id',
							'Teacher.counseling_flg = 0' 
							)
						),
					array(
						'type' => 'INNER',
						'table' => 'teacher_badges',
						'alias' => 'TeacherBadge',
						'conditions' => array(
							'Teacher.id = TeacherBadge.teacher_id'
							)
						)						
					),
				'order' => array(
					'TeacherRatingsLesson.ratings DESC'
			)));
		
		if (isset($teacherData['Teacher']['id'])) {
			return json_encode(array(
				"recommend" => 1,
				"recommend_teacher_type" => 2,
				"id" => intval($teacherData['Teacher']['id']) 
			));
		}

		//eval 4.6 and can lesson 25 mins
		$teacherData = $this->LessonOnair->find('first', array(
				'fields' => 'Teacher.id',
				'conditions' => array(
						'Teacher.status = 1',
						'Teacher.stealth_flg = 0',
						'LessonOnair.status = 1',
						'LessonOnair.connect_flg = 1',
						'TeacherBadge.textbook_category_id = 45',
						'OR' => array(
							'(SELECT TIME_TO_SEC(TIMEDIFF(lesson_time, NOW()))
						     FROM lesson_schedules 
						     WHERE teacher_id = LessonOnair.teacher_id AND lesson_time > NOW() 
						     AND status = 1 ORDER BY lesson_time ASC LIMIT 1) > 1830',		
						     '(SELECT COUNT(*) FROM lesson_schedules
						     WHERE teacher_id = LessonOnair.teacher_id AND lesson_time > NOW() 
						     AND status = 1) = 0'
						),
						'NOT' => array('Teacher.id' => $blockList)
					),
				'joins' => array(
					array(
						'type' => 'INNER',
						'table' => 'teacher_ratings_lessons',
						'alias' => 'TeacherRatingsLesson',
						'conditions' => array(
							'LessonOnair.status = 1',
							'LessonOnair.connect_flg = 1',
							'LessonOnair.teacher_id = TeacherRatingsLesson.teacher_id',
							'TeacherRatingsLesson.ratings >= 4.6'
							)
						),
					array(
						'type' => 'INNER',
						'table' => 'teachers',
						'alias' => 'Teacher',
						'conditions' => array(
							'Teacher.status = 1',
							'Teacher.stealth_flg = 0',
							'LessonOnair.teacher_id = Teacher.id',
							'Teacher.counseling_flg = 0' 
							)
						),
						array(
						'type' => 'INNER',
						'table' => 'teacher_badges',
						'alias' => 'TeacherBadge',
						'conditions' => array(
							'Teacher.id = TeacherBadge.teacher_id'
							)
						)						
					),
				'order' => array(
					'TeacherRatingsLesson.ratings DESC'
			)));
		
		if (isset($teacherData['Teacher']['id'])) {
			return json_encode(array(
				"recommend" => 1,
				"recommend_teacher_type" => 2,
				"id" => intval($teacherData['Teacher']['id']) 
			));
		}

		//eval 4.6 and can lesson 10 mins
		$teacherData = $this->LessonOnair->find('first', array(
				'fields' => 'Teacher.id',
				'conditions' => array(
						'Teacher.status = 1',
						'Teacher.stealth_flg = 0',
						'LessonOnair.status = 1',
						'LessonOnair.connect_flg = 1',
						'TeacherBadge.textbook_category_id = 45',
						'OR' => array(
							'(SELECT TIME_TO_SEC(TIMEDIFF(lesson_time, NOW()))
						     FROM lesson_schedules 
						     WHERE teacher_id = LessonOnair.teacher_id AND lesson_time > NOW() 
						     AND status = 1 ORDER BY lesson_time ASC LIMIT 1) > 930',		
						     '(SELECT COUNT(*) FROM lesson_schedules
						     WHERE teacher_id = LessonOnair.teacher_id AND lesson_time > NOW() 
						     AND status = 1) = 0'
						),
						'NOT' => array('Teacher.id' => $blockList)
					),
				'joins' => array(
					array(
						'type' => 'INNER',
						'table' => 'teacher_ratings_lessons',
						'alias' => 'TeacherRatingsLesson',
						'conditions' => array(
							'LessonOnair.status = 1',
							'LessonOnair.connect_flg = 1',
							'LessonOnair.teacher_id = TeacherRatingsLesson.teacher_id',
							'TeacherRatingsLesson.ratings >= 4.6'
							)
						),
					array(
						'type' => 'INNER',
						'table' => 'teachers',
						'alias' => 'Teacher',
						'conditions' => array(
							'Teacher.status = 1',
							'Teacher.stealth_flg = 0',
							'LessonOnair.teacher_id = Teacher.id',
							'Teacher.counseling_flg = 0' 
							)
						),
						array(
						'type' => 'INNER',
						'table' => 'teacher_badges',
						'alias' => 'TeacherBadge',
						'conditions' => array(
							'Teacher.id = TeacherBadge.teacher_id'
							)
						)						
					),
				'order' => array(
					'TeacherRatingsLesson.ratings DESC'
			)));
		
		if (isset($teacherData['Teacher']['id'])) {
			return json_encode(array(
				"recommend" => 1,
				"recommend_teacher_type" => 2,
				"id" => intval($teacherData['Teacher']['id']) 
			));
		}

		//recommend_teacher_type - 3 : counselors offline
		$teacherData = $this->Teacher->find('first', array(
			'fields' => 'Teacher.id',
			'conditions' => array(
					'Teacher.status = 1',
					'Teacher.stealth_flg = 0',
					'Teacher.counseling_flg = 1',
					'ShiftWorkOn.lesson_time > NOW()',
					'NOT' => array('Teacher.id' => $blockList)
				),
			'joins' => array(
					array(
						'type' => 'INNER',
						'table' => 'shift_workons',
						'alias' => 'ShiftWorkOn',
						'conditions' => array(
							'Teacher.id = ShiftWorkOn.teacher_id'
							)
						)							
					),
			'order' => 'RAND()',
			'recursive' =>  -1
		));	

		if (isset($teacherData['Teacher']['id'])) {
			return json_encode(array(
				"recommend" => 1,
				"recommend_teacher_type" => 3,
				"id" => intval($teacherData['Teacher']['id']) 
			));
		} else {
			return json_encode(array(
				"recommend" => 0
			));
		}
	}
}