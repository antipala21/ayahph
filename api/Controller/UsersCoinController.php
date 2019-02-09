<?php

/**
 * Users Coin
 * Author : John Robert Jerodiaz ( Roy )
 */
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');

class UsersCoinController extends AppController {

  public $uses = array(
    'User',
    'UsersPoint',
    'UsersPointHistorie',
    'ReviewedUser'
  );

    public function beforeFilter() {
      parent::beforeFilter();
      //instantiate slack
      $this->mySlack = new mySlack();
      $this->slackChannel = myTools::checkChannel('#nc-coin','#nc-coin-dev');
      $this->Auth->allow('review','reviewed','show');
      $this->autoRender = false;
    }


    /**
     * API for user create coin
     * @return type json Users coin created
     */
    public function review() {
      $response = array();
      $data = @json_decode($this->request->input(), true);
      if (!$data) {
        $response['error']['id'] = Configure::read('error.invalid_request');
        $response['error']['message'] = __('Invalid request.');
      } else {
        if (empty($data['users_api_token']) || !isset($data['users_api_token'])) {
            $response['error']['id'] = Configure::read('error.users_api_token_is_required');
            $response['error']['message'] = __('users_api_token is required');
        } else if (!is_string($data['users_api_token'])) {
            $response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
            $response['error']['message'] = __('users_api_token must be string');
        } else {

          $api = new ApiCommonController();
          $user['User'] = $api->validateToken($data['users_api_token']);

          if (!$user['User']){
              $response['error']['id'] = Configure::read('error.invalid_api_token');
              $response['error']['message'] = __('Invalid users_api_token');
          } else {
            $userId = $user['User']['id'];

            //add record for app regist user
            $recordCheck = UsersPointTable::add($userId, 0);
            if (!$recordCheck) {
                $response['error']['id'] = Configure::read('error.save_to_users_point_table_failure');
                $response['error']['message'] = __('cant create record');
            }else {
                //Check the userId if exist in revieweduser table
                $checkReviewedExist = $this->ReviewedUser->useReplica()->find('first', array(
                  'conditions' => array(
                      'user_id' => $userId
                  )
                ));

                //get amount of present coin
                $reviewedPresentCoin = Configure::read('reviewedPresentCoin');

                if ($checkReviewedExist){
                   $response['message'] = __('Coin has already been given.');
                } else {
                  // Prepare the data, ready to insert in revieweduser table
                  $prepareReview = array(
                      'ReviewedUser' => array(
                      'user_id' => $userId,
                      'point' => $reviewedPresentCoin,
                      'created' => date('Y-m-d H:i:s'),
                      'modified' => date('Y-m-d H:i:s'),
                      'created_ip' => $this->request->clientIp(),
                      'modified_ip' => $this->request->clientIp()
                    )
                  );
                  //Insert data
                  $this->ReviewedUser->create($prepareReview);
                  $this->ReviewedUser->save($prepareReview);

                  //Get old current user point
                  $getPoint = $this->UsersPoint->useReplica()->find('first', array(
                    'fields' => 'point',
                    'conditions' => array(
                        'user_id' => $userId
                    )
                  ));

                  $oldPoint = isset($getPoint['UsersPoint']['point']) ? $getPoint['UsersPoint']['point'] : 0;
                  $newPoint = $oldPoint + $reviewedPresentCoin;

                    //Prepare data coin for logs
                    $readyLogs = array(
                      'UsersPointHistorie' => array(
                        'user_id' => $userId,
                        'kbn' => '12',
                        'point' => $newPoint,
                        'point_old' => $oldPoint,
                        'created' => date('Y-m-d H:i:s'),
                        'modified' => date('Y-m-d H:i:s'),
                        'created_ip' => $this->request->clientIp(),
                        'modified_ip' => $this->request->clientIp()
                      )
                    );

                    //Insert prepared data for point logs
                    $this->UsersPointHistorie->set($readyLogs);
                    $this->UsersPointHistorie->save($readyLogs);

                    // send slack
                    $this->mySlack->channel = $this->slackChannel;
                    $this->mySlack->username = "NC-Coin Bot";
                    $this->mySlack->text = "```";
                    $this->mySlack->text .= date("Y/m/d") . " auto レビュー(APP)\n";
                    $this->mySlack->text .= "UserID : " . $userId . "\n";
                    $this->mySlack->text .= "APPレビュー 200コイン追加";
                    $this->mySlack->text .= "```";
                    $this->mySlack->sendSlack();


                    // update user memo
        			$memoData = array(
        				'user_id' => $userId,
        				'memo' => date("Y-m-d"). " APPレビューによる200コイン追加"
        			);
        			$this->User->updateMemo($memoData);

                    //Prepare point data for the new user point
                    $pointData = array(
                    'UsersPoint' => array(
                        'point' => $newPoint,
                        'modified' => date('Y-m-d H:i:s'),
                        'modified_ip' => $this->request->clientIp()
                    ));
                    //Update userpoint
                    $this->UsersPoint->id = $userId;
                    $this->UsersPoint->set($pointData);
                    $this->UsersPoint->save($pointData);

                    $response['created'] = true;
                }
            }
          }
        }
      }
      return json_encode($response);
    } //end of function



    /**
     * API for user show available coin
     * @return type json  Coin
     */
    public function show() {
        $data = json_decode($this->request->input(), true);
        $count = 1;
        $api = new ApiCommonController();
        $user['User'] = $api->validateToken($data['users_api_token']);
        if (isset($data['users_api_token'])) {
            $split = explode(" ", $data['users_api_token']);
            $count = count($split);
        }
        if (!$data) {
            $response['error']['id'] = Configure::read('error.invalid_request');
            $response['error']['message'] = __('Invalid request');
        } else if (empty ($data['users_api_token']) || !isset ($data['users_api_token'])) {
            $response['error']['id'] = Configure::read('error.users_api_token_is_required');
            $response['error']['message'] = __('users_api_token is required');
        } else if (!is_string($data['users_api_token'])) {
            $response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
            $response['error']['message'] = __('users_api_token must be string');
        } else if ($count > 1) {
            $response['error']['id'] = Configure::read('error.invalid_api_token');
            $response['error']['message'] = __('Invalid users_api_token');
        } else {
            if (!$user['User']) {
                $response['error']['id'] = Configure::read('error.invalid_api_token');
                $response['error']['message'] = __('Invalid users_api_token');
            } else {
              $getCoin = $this->UsersPoint->useReplica()->find('first',
                array(
                  'fields' => 'point',
                  'conditions' => array(
                      'user_id' =>  $user['User']['id']
                  )
                ));
              $response['coin'] = intval(@$getCoin['UsersPoint']['point']);
            }
        }

    return json_encode($response);
  } //end of function


  public function reviewed() {
    if ($this->request->is('post')) {
      $data = @json_decode($this->request->input(), true);
      $api = new ApiCommonController();
      if (!$data) {
        $response['error']['id'] = Configure::read('error.invalid_request');
        $response['error']['message'] = __('Invalid request');
      } else if (!isset($data['users_api_token']) || empty($data['users_api_token'])) {
        $response['error']['id'] = Configure::read('error.users_api_token_is_required');
        $response['error']['message'] = __('users_api_token is required');
      } else if (isset($data['users_api_token']) && !is_string($data['users_api_token'])) {
        $response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
        $response['error']['message'] = __('users_api_token must be string');
      } else {
        $user['User'] = $api->validateToken($data['users_api_token']);
        if ( !$user['User'] ) {
          $response['error']['id'] = Configure::read('error.invalid_api_token');
          $response['error']['message'] = __('Invalid users_api_token');
        } else {
          $this->ReviewedUser->openDBReplica();
          $reviewed = $this->ReviewedUser->findByUserId($user['User']['id']);
          $this->ReviewedUser->closeDBReplica();
          $response['reviewed'] = isset($reviewed['ReviewedUser']) ? true : false;
        }
      }
    } else {
      $response['error']['id'] = Configure::read('error.invalid_request');
      $response['error']['message'] = __('Invalid request');
    }
    return json_encode($response);
  }

  /**
   * Function use to check a valid user token
   * @param type $token requested user api_token
   * @return type array
   */
  private function user($token) {
      $this->User->recursive = -1;
      $check = $this->User->useReplica()->find('first', array(
          'fields' => 'id',
          'conditions' => array(
              'api_token' => $token
          )
      ));
      return $check;
  } //end of function


}
