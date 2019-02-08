<?php
/***
 * View Cancelled Reservations by a teacher/admin
 * Author : John Mart Belamide
 */
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class NotificationCancelListController extends AppController {
  public $uses = array(
    'LessonScheduleCancel',
    'TextbookConnect',
    'ShiftWorkOn',
    'Teacher'
  );

  public function beforeFilter() {
    parent::beforeFilter();
    $this->Auth->allow('index');
  }

  /***
   * View reservation list of a user
   * @return type array
  */
  public function index() {
    $this->autoRender = false;
    $data = json_decode($this->request->input(), true);
    $apiCommon = new ApiCommonController();
    $type = null;
    $response = array();
    $counselor = array();

    if (!$data) {
      return null;
    } else if (!isset($data['users_api_token']) || empty($data['users_api_token'])) {
      return null;
    } else {
      $user = $apiCommon->validateToken($data['users_api_token']);

      if (!$user) {
        return null;
      }

      $blockList = BlockListTable::getBlocks($user['id']);
      $cancellationTypes = array(4,5,3,25,26,27,28,29,30,31,32);

      $conditions = array(
        'LessonScheduleCancel.teacher_id NOT IN' => $blockList,
        'LessonScheduleCancel.user_id' => $user['id'],
        'LessonScheduleCancel.lesson_time !=' => '',
        'LessonScheduleCancel.apology_show' => 1,
        'LessonScheduleCancel.status'  => $cancellationTypes // 4 : system cancellation 5 : late cancellation 25-28 : teacher cancellation 29-32 : admin cancellation 
      );
      $sort = array(
        'LessonScheduleCancel.lesson_time' => 'ASC'
      );
      $joins = array(
        array(
          'table' => 'teachers',
          'alias' => 'Teacher',
          'conditions' => array('LessonScheduleCancel.teacher_id = Teacher.id')
        )
      );

      unset($cancellationTypes[0],$cancellationTypes[1]); // unset system and late cancellation

      $hasExistingTeacherOnSched = "
        SELECT COUNT(sw.id) as cnt 
        FROM shift_workons AS sw 
        LEFT JOIN teacher_badges AS tb ON tb.teacher_id = sw.teacher_id
        LEFT JOIN textbook_connects AS tc ON tc.category_id = tb.textbook_category_id
        LEFT JOIN textbook_connects AS tc1 ON tc1.textbook_id = tc.textbook_id 
        LEFT JOIN teachers AS t ON t.id = sw.teacher_id
        WHERE 
          sw.lesson_time = LessonScheduleCancel.lesson_time 
          AND 
          LessonScheduleCancel.status IN (" . implode(',', $cancellationTypes) . ")
          AND
          sw.teacher_id != LessonScheduleCancel.teacher_id 
          AND
          tb.id IS NOT NULL
          AND 
          (
            tc.id = LessonScheduleCancel.connect_id      
            OR tc1.id = LessonScheduleCancel.connect_id 
          )
          AND
          t.counseling_flg = 0  
        LIMIT 1";
      $this->LessonScheduleCancel->virtualFields['hasExistingTeacherOnSched'] = $hasExistingTeacherOnSched; // NC-3998_hotfix
      $reservation = $this->LessonScheduleCancel->find('all', array(
        'fields' => array(
          'Teacher.name',
          'Teacher.jp_name',
          'Teacher.counseling_flg',
          'LessonScheduleCancel.lesson_time',
          'LessonScheduleCancel.connect_id',
          'LessonScheduleCancel.status',
          'LessonScheduleCancel.hasExistingTeacherOnSched',
          'LessonScheduleCancel.reservation_id'
        ),
        'joins' => $joins,
        'conditions'=> $conditions,
        'order' => $sort,
        'recursive' => -1
       ));

      // Update apology_show to 0 to all row
      $this->closeApology($conditions);

      $response['lessons'] = array();
      $counselor = $this->Teacher->getDefaultCounselorDetail();
      $counselor = new TeacherTable($counselor['Teacher']);
      foreach($reservation as $key=>$row) {

        if ( isset($row['LessonScheduleCancel']['status']) ) {
          if ( $row['LessonScheduleCancel']['status'] == 5) {
            $type = 1;
          } elseif ( $row['LessonScheduleCancel']['status'] == 4 || 
            (
              in_array($row['LessonScheduleCancel']['status'], $cancellationTypes) &&
              $row['LessonScheduleCancel']['hasExistingTeacherOnSched'] == 0
            )
          ) {
            $type = 3;
          } elseif ( 
            in_array($row['LessonScheduleCancel']['status'], $cancellationTypes) && 
            $row['LessonScheduleCancel']['hasExistingTeacherOnSched'] > 0 
          ) {
            $type = 2;
          }
        }

        $content = array(
          'teachers_name' => ($row['Teacher']['counseling_flg'] == 1)? $counselor->name."(".$counselor->jp_name.")" : $row['Teacher']['name'],
          'lesson_id' => $row['LessonScheduleCancel']['reservation_id'],
          'connect_id' => $row['LessonScheduleCancel']['connect_id'], // NC-3998
          'date' => $row['LessonScheduleCancel']['lesson_time'], // NC-3998
          'type' => $type
        );
        $response['lessons'][] = $content;
      }
    }
    echo json_encode($response);
  }

  public function closeApology($conditions){
    $this->LessonScheduleCancel->updateAll(array('apology_show' => 0),$conditions);
  }
}
