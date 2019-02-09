<?php
/***
 * Lesson Review Controller
 * Author : John Robert Jerodiaz (Roy)
 */
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class LessonReviewController extends AppController {

    public $uses = array(
        'User',
        'Teacher',
        'LessonOnair',
        'LessonOnairsLog',
        'UsersClassEvaluation'
    );

    private $rating = array(
        0 => 5,
        1 => 4,
        2 => 3,
        3 => 2,
        4 => 1
    );

    public function beforeFilter() {
        parent::beforeFilter();
        //instantiate slack
        $this->mySlack = new mySlack();
        $this->autoRender = false;
        $this->Auth->allow('index');
    }

    /***
     * Lesson review controller
     * @param
     * @return json array
     * @access url : https://nativecamp.net/api/lesson/review
     */
    public function index() {
        $data = json_decode($this->request->input(), true);
        $response = array();
        $evalSave = false;

        if (!$data) {
            $response['error']['id'] = Configure::read('error.invalid_request');
            $response['error']['message'] = __('Invalid request.');
        } else {
            //api token
            if (empty($data['users_api_token']) || !isset($data['users_api_token'])) {
                $response['error']['id'] = Configure::read('error.users_api_token_is_required');
                $response['error']['message'] = __('The users_api_token is required.');
            } else if (!is_string($data['users_api_token'])) {
                $response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
                $response['error']['message'] = __('The users_api_token must be string request.');
            } else if (isset($data['api_version']) && is_string($data['api_version'])) {
                $response['error']['id'] = Configure::read('error.api_version_must_be_integer');
                $response['error']['message'] = __('api version must not be string');  
            //chat hash
            } else if (empty($data['chat_hash']) || !isset($data['chat_hash'])) {
                $response['error']['id'] = Configure::read('error.chat_hash_is_required');
                $response['error']['message'] = __('The chat_hash is required.');
            } else if (isset($data['review']) && !is_integer($data['review'])) {
              $response['error']['id'] = Configure::read('error.review_must_be_integer');
              $response['error']['message'] = __('The review must be integer.');
            } else if (isset($data['review']) && ($data['review'] < 0 || $data['review'] > 4)) {
              $response['error']['id'] = Configure::read('error.the_review_range_is_invalid');
              $response['error']['message'] = __('The review range is invalid.');
            //review comment
          } else if (isset($data['review']) && ($data['review'] == 4 && (!isset($data['review_comment']) || empty($data['review_comment']) || strlen(trim($data['review_comment'])) == 0))) {
              $response['error']['id'] = Configure::read('error.the_review_comment_is_required_if_review_is_4');
              $response['error']['message'] = __('The review_comment is required if review is 4.');
            //system_trouble_flg
            } else if (isset($data['system_trouble_flg']) && !is_integer($data['system_trouble_flg'])) {
              $response['error']['id'] = Configure::read('error.system_trouble_flg_must_be_integer');
              $response['error']['message'] = __('The system_trouble_flg must be integer.');
            } else {
                $api = new ApiCommonController();
                //get user id by users_api_token
                $userData = $api->validateToken($data['users_api_token']);

                if (!$userData) {
                    $response['error']['id'] = Configure::read('error.invalid_api_token');
                    $response['error']['message'] = 'Invalid users_api_token';
                } else {
                    $user = new UserTable($userData);
                    $lessonData = LessonOnairTable::findLessonData(array(
                        'fields' => array(
                            'id',
                            'teacher_id',
                            'user_id',
                            'chat_hash',
                            'lesson_system_trouble',
                            'user_agent',
                            'lesson_type',
                            'start_time',
                            'end_time',
                            'lesson_finish'
                        ),
                        'conditions' => array(
                            'chat_hash' => $data['chat_hash'],
                            'user_id' => $user->id
                        )
                    ));

                    if (!$lessonData) {
                        $response['error']['id'] = Configure::read('error.the_lesson_is_not_found');
                        $response['error']['message'] = 'The lesson is not found.';
                    } else {
                        if (isset($data['system_trouble_flg']) && $data['system_trouble_flg'] == 1) {
                          //update trouble
                          $model = $this->$lessonData['model'];
                          if ($model->read(null, $lessonData['data']['id'])) {
                            $model->set('lesson_system_trouble', 1);
                            $model->save();
                          }
                          return json_encode(array('result' => true));
                        }
                        //assign lesson data
                        $lessonData = $lessonData['data']; 
                        //check if evaluation aleady exist then return false
                        $checkEvaluation = $this->UsersClassEvaluation->find('first', array(
                            'fields' => 'UsersClassEvaluation.id',
                            'conditions' => array(
                                'UsersClassEvaluation.chat_hash' => $lessonData['chat_hash']
                            ),
                            'recursive' => -1
                        ));
                        $now = date('Y-m-d H:i:s');
                        $last2months = date('Y-m-d H:i:s', strtotime("-2 months"));
                        $pastratings = $this->UsersClassEvaluation->useReplica()->find('count', array(
                                'conditions' => array(
                                    'UsersClassEvaluation.rate' => array(1,2),
                                    'UsersClassEvaluation.teacher_id' => $lessonData['teacher_id'],
                                    'UsersClassEvaluation.user_id' => $user->id,
                                    'UsersClassEvaluation.created <=' => $now,
                                    'UsersClassEvaluation.created >=' => $last2months
                                ),
                                'recursive' => -1
                            )
                        );
                        
                        if ($checkEvaluation) {
                          $response['error']['id'] = Configure::read('error.the_lesson_has_a_review_already');
                          $response['error']['message'] = __('The lesson has a review already.');
                        // check if number of bad ratings is 2 or more in total then result false
                        } elseif ((isset($pastratings) && $pastratings >= 2) && (isset($data['review']) && $data['review'] >= 3)) {
                            $response['error']['id'] = Configure::read('error.many_low_evaluations');
                            $response['error']['message'] = __('The instructor has too many low evaluations.');
                        } else {
                            //do nothing no review and textbook review
                            if (!isset($data['review']) && !isset($data['textbook_review'])) {
                                return json_encode(array('result' => true));
                            }
                            $this->log('review data ' . json_encode($data), 'debug');
                          $lessonReview = isset($data['review_comment']) ? $data['review_comment'] : '';
                          //prepare data to save the reveiw
                          $toSave = array(
                              'UsersClassEvaluation' => array(
                                  'lesson_id' => $lessonData['id'],
                                  'teacher_id' => $lessonData['teacher_id'],
                                  'chat_hash' => $lessonData['chat_hash'],
                                  'user_id' => $user->id,
                                  'age' => $user->getAgeRange(),
                                  'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                                  'rate' => isset($data['review']) && isset($this->rating[$data['review']]) ? $this->rating[$data['review']] : null,
                                  'user_comment' => $lessonReview,
                                  'calculate_flag' => 1,
                                  'approve_flag' => 0,
                                  'textbook_review' => isset($data['textbook_review']) ? $data['textbook_review'] : '',
                                  'text_comment' => isset($data['textbook_review_comment']) ? $data['textbook_review_comment'] : '',
                                  'created_ip' => $this->request->clientIp(),
                                  'modified_ip' => $this->request->clientIp()
                              )
                          );
                          //find teacher and user name
                          $teacher_name = $this->Teacher->useReplica()->find('first',
                              array(
                                  'fields' => array(
                                    'Teacher.name',
                                    'Teacher.home_flg',
                                    'Teacher.rank_coin_id',
                                    'Teacher.promote_date',
                                    'Teacher.first_lesson_date',
                                    'TeacherDetail.referrer_id'
                                  ),
                                  'joins' => array(
                                    array(
                                      'table' => 'teacher_details',
                                      'alias' => 'TeacherDetail',
                                      'type' => 'LEFT',
                                      'conditions' => 'TeacherDetail.teacher_id = Teacher.id'
                                    )
                                  ),
                                  'conditions' => array(
                                      'Teacher.id' => $lessonData['teacher_id']
                                  )
                              )
                          );

                          if (is_null($teacher_name['Teacher']['first_lesson_date'])) {
                            $teacher_name['Teacher']['first_lesson_date'] = $lessonData['end_time'];
                          }

                          $user_name = $this->User->useReplica()->find('first',
                              array(
                                  'fields' => array('nickname'),
                                  'conditions' => array(
                                      'User.id' => $user->id
                                  )
                              )
                          );
                          if(!empty($data['review']) && mb_strlen(trim($data['review_comment'])) > 0) {
							  
							// NC-2987
							$this->mySlack->sendSlackVoice(array(
									"category" => "レビュー(APP)",
									"url" => $_SERVER['HTTP_HOST'].'/admin/lesson-review?teachersId='.$lessonDetail->teacher_id.'&usersId='.$user->id,
									"content" => $lessonReview,
									"review" => isset($this->rating[$data['review']]) ? $this->rating[$data['review']] : 3,
									"teacher_name" => $teacher_name['Teacher']['name'],
									"user_name" => $user_name['User']['nickname'],
									"channel" => "#nc-voice-review"
								)
							);
                          
                          }
                          $this->UsersClassEvaluation->clear();
                          $evalSave = $this->UsersClassEvaluation->save($toSave);
                          if (isset($data['review'])) {
                            /** NC-3824: save home based teacher salary and incentives **/
                            if ($lessonData['lesson_type'] == 1 && $teacher_name['Teacher']['home_flg'] && strpos(strtoupper($user->nickname), '%%%TEST%%%') === false) {

                              // NC-4502 get start and end time from lesson_onairs_logs directly.
                              $timefromLol = $this->LessonOnairsLog->find('first', array(
                                'fields' => array(
                                  'start_time',
                                  'end_time',
                                  'lesson_finish'
                                ),
                                'conditions' => array('chat_hash' => $lessonData['chat_hash'])
                              ));

                              if (!isset($lessonData['lesson_finish']) && isset($timefromLol['LessonOnairsLog']['lesson_finish'])) {
                                $lessonData['lesson_finish'] = $timefromLol['LessonOnairsLog']['lesson_finish'];
                              }

                              $endTimeInSec = strtotime(isset($timefromLol['LessonOnairsLog']['end_time']) ? $timefromLol['LessonOnairsLog']['end_time'] : $lessonData['end_time']);
                              $startTimeInSec = strtotime(isset($timefromLol['LessonOnairsLog']['start_time']) ? $timefromLol['LessonOnairsLog']['start_time'] : $lessonData['start_time']);

                              $point = $endTimeInSec - $startTimeInSec;
                              $date = date('Y-m-d', $endTimeInSec);
                              $ratingsToSuddenSlots = Configure::read('home_based.ratings_to_sudden_slots_equivalent');
                              $rate = $ratingsToSuddenSlots[$this->rating[$data['review']]];

                              //new spec for lesson_finish pattern 
                              if ($lessonData['lesson_finish'] !== 1) {
                                  $endDate = isset($timefromLol['LessonOnairsLog']['end_time']) ? date_create($timefromLol['LessonOnairsLog']['end_time']) : date_create($lessonData['end_time']);
                                  $startDate = isset($timefromLol['LessonOnairsLog']['start_time']) ? date_create($timefromLol['LessonOnairsLog']['start_time']) : date_create($lessonData['start_time']);
                                  $duration = date_diff($endDate, $startDate); 
                                  //check duration
                                  if ($duration->format("
                                    %i") >= 26) {
                                    $lessonData['lesson_finish'] = 1;
                                  }
                              }
                              
                              // save salary data if lesson time is greater than or equal to 60 secs/1 minute
                              if ($point >= 60) {
                                // get basic_amount_type home_based_basic_amounts_logs id
                                $this->loadModel('HomeBasedBasicAmountLog');
                                $hbbalData = $this->HomeBasedBasicAmountLog->useReplica()->find('first', array(
                                  'fields' => array('id'),
                                  'conditions' => array(
                                    'rank_coin_id' => $teacher_name['Teacher']['rank_coin_id'],
                                    'basic_amount_type' => $rate,
                                    'active' => 1
                                  )
                                ));

                                $hbsData = array(
                                  'teacher_id' => $lessonData['teacher_id'],
                                  'rank_coin_id' => $teacher_name['Teacher']['rank_coin_id'],
                                  'basic_amount_type' => $rate, 
                                  'point' => $point,
                                  'chat_hash' => $lessonData['chat_hash'],
                                  'date' => $date,
                                  'hbbal_id' => $hbbalData ? $hbbalData['HomeBasedBasicAmountLog']['id'] : null
                                );
                                $this->loadModel('HomeBasedSalaryData');
                                $this->HomeBasedSalaryData->saveSalaryData($hbsData);
                              }

                              $firstLesson = false;
                              if (strtotime($lessonData['end_time']) <= strtotime($teacher_name['Teacher']['first_lesson_date'])) {
                                $firstLesson = true;
                              }

                              // update incentive
                              $hbiData = array(
                                'teacherId' => $lessonData['teacher_id'],
                                'promoteDate' => $teacher_name['Teacher']['promote_date'],
                                'referrerId' => $teacher_name['TeacherDetail']['referrer_id'],
                                'basicAmountType' => $rate,
                                'rankId' => $teacher_name['Teacher']['rank_coin_id'],
                                'firstLesson' => $firstLesson,
                                'finishPatternType' => $lessonData['lesson_finish'],
                                'date' => $date,
                                'chatHash' => $lessonData['chat_hash']
                              );
                              $this->loadModel('HomeBasedIncentive');
                              $this->HomeBasedIncentive->updateIncentives($hbiData);

                            }
                            /** NC-3824 end **/


                              $response['result'] = true;
                          } elseif ($evalSave) {
                              $response['result'] = true;
                          } else {
                              $response['result'] = false;
                          }
                      }
                    }
                }
            }
        }
        return json_encode($response);
    } // end of function

}
