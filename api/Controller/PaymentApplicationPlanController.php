<?php

App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class PaymentApplicationPlanController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('index');
    }

    public $error;
    public $autoRender = false;
    public $autoLayout = false;

    public function index(){

        //get posted data
        $data = json_decode($this->request->input(),true);

        //get user info
        $apiCommon = new ApiCommonController();
        $user = $apiCommon->validateToken($data['users_api_token']);
        if(empty($user)){
            $apiResponse = array('error' => array('id' => Configure::read('error.invalid_api_token'), 'message' => 'Invalid Api token'));
            return json_encode($apiResponse);
        }

        $defaultPlan = Configure::read('app_plan');

        //failed users or users that have double_check_flg = 2
        if ($user['fail_flg'] == 1 || $user['double_check_flg'] == 2) {
            if ($defaultPlan == Configure::read('application_plan_A') || $defaultPlan == Configure::read('application_plan_B')) {
                $usePlan = Configure::read('application_plan_B');
            } else {
                $usePlan = Configure::read('application_plan_D');
            }
        } else {
            $usePlan = $defaultPlan;
        }

        return json_encode(array('application_plan' => $usePlan));
    }
}
