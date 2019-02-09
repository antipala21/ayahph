<?php

App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class PaymentPurchaseController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('index');
    }
    public $uses = array(
        'User',
        'DefineMaster',
        'PaymentMobileTransactions',
        'Payment',
        'UsersIosReceipt',
        'ContinuationCampaign'
    );

    public $error;
    public $autoRender = false;
    public $autoLayout = false;

    public function index(){

        //get posted data
        $data = json_decode($this->request->input(),true);

        $dateNowWithTime = date("Y-m-d H:i:s");

        //get user info
        $apiCommon = new ApiCommonController();
        $user = $apiCommon->validateToken($data['users_api_token']);
        if(empty($user)){
            $apiResponse = array('error' => array('id' => Configure::read('error.invalid_api_token'), 'message' => 'Invalid Api token'));
            return json_encode($apiResponse);
        }

        if(!isset($data['application_plan']) || empty($data['application_plan'])){
            $apiResponse = array('error' => array('id' => Configure::read('error.application_plan_is_required'), 'message' => 'application_plan is required'));
            return json_encode($apiResponse);
        }elseif (!in_array($data['application_plan'], array('A','B','C','D'))) {
            $apiResponse = array('error' => array('id' => Configure::read('error.invalid_application_plan'), 'message' => 'Invalid application_plan'));
            return json_encode($apiResponse);
        }elseif (!isset($data['receipt']) || empty($data['receipt'])) {
            $apiResponse = array('error' => array('id' => Configure::read('error.receipt_is_required'), 'message' => 'receipt is required'));
            return json_encode($apiResponse);
        }elseif (!isset($data['device']) || empty($data['device'])) {
            $apiResponse = array('error' => array('id' => Configure::read('error.device_is_required'), 'message' => 'device is required'));
            return json_encode($apiResponse);
        }elseif (!is_int($data['device'])) {
            $apiResponse = array('error' => array('id' => Configure::read('error.device_must_be_integer'), 'message' => 'device must be integer'));
            return json_encode($apiResponse);
        }elseif (!in_array($data['device'], array(1,2))) {
            $apiResponse = array('error' => array('id' => Configure::read('error.invalid_device'), 'message' => 'device must be 1 or 2'));
            return json_encode($apiResponse);
        }

        $tier8 = $this->DefineMaster->getDefineDetail('31','8');
        $tier41 = $this->DefineMaster->getDefineDetail('31','41');

        $db = ConnectionManager::getDataSource('default');
        //iOS
        if($data['device'] == 1){
            if ($db->isConnected()) {
                $db->close();
            }
            $receiptData = $this->verifyAppleReceipt($data['receipt'], Configure::read('ios_shared_secret'));
            $db->reconnect();

            if(isset($receiptData['result'])){
                $result = $receiptData['result'];

                //ここに正常処理を記述
                if($result['receipt']['bundle_id'] === 'net.nativecamp.nativecamp'){
                    if (!isset($result['latest_receipt_info'])) {
                        $apiResponse = array('error' => array('id' => Configure::read('error.latest_receipt_info_not_found'), 'message' => 'receipt latest_receipt_info not found'));
                        return json_encode($apiResponse);
                    }
                    $latestReceiptInfo = end($result['latest_receipt_info']);

                    //check if subscription has been cancelled
                    if (isset($latestReceiptInfo['cancellation_date'])) {
                        $cancellationDate = date('Y-m-d H:i:s', strtotime($latestReceiptInfo['cancellation_date']));
                        if (strtotime($cancellationDate) <= strtotime($dateNowWithTime)) {
                            $apiResponse = array('error' => array('id' => Configure::read('error.subscription_has_been_cancelled'), 'message' => 'Subscription has been cancelled'));
                            return json_encode($apiResponse);
                        }
                    }

                    //check if subscription has expired
                    $expiresDate = date('Y-m-d H:i:s', strtotime($latestReceiptInfo['expires_date']));
                    if (strtotime($expiresDate) <= strtotime($dateNowWithTime)) {
                        $apiResponse = array('error' => array('id' => Configure::read('error.subscription_has_expired'), 'message' => 'Subscription has expired'));
                        return json_encode($apiResponse);
                    }

                    if($this->PaymentMobileTransactions->checkTransaction($latestReceiptInfo['transaction_id'])){
                        $purchaseDate = date('Y-m-d H:i:s', strtotime($latestReceiptInfo['purchase_date']));
                        $TransactionData['PaymentMobileTransactions'] = array(
                            'user_id' => $user['id'],
                            'original_transaction_id' => $latestReceiptInfo['transaction_id'],
                            'product_id' => $latestReceiptInfo['product_id'],
                            'quantity' => $latestReceiptInfo['quantity'],
                            'original_purchase_date' => $purchaseDate,
                            'application_version' => $result['receipt']['application_version']
                        );
                        $this->PaymentMobileTransactions->clear();
                        $this->PaymentMobileTransactions->create();
                        if (!$this->PaymentMobileTransactions->save($TransactionData)) {
                            $apiResponse = array('error' => array('id' => Configure::read('error.save_to_payment_mobile_transactions_table_failure'), 'message' => 'Transaction did not successfully save.'));
                            return json_encode($apiResponse);
                        }

                        if(strpos($latestReceiptInfo['product_id'],'net.nativecamp.nativecamp.') !== false){
                            $item = str_replace('net.nativecamp.nativecamp.','',$latestReceiptInfo['product_id']);
                            $validItem = true;
                            //決済履歴に残す
                            switch ($item) {
                                case '1month':
                                case '1month.trial':
                                case '1month2':
                                case '1month.trial2':
                                case '1month_plan':
                                case '1month_plan_trial':
                                case 'monthly':
                                case 'monthly.trial':
                                    //change Plan A and Plan B to Plan C and Plan D
                                    if ($data['application_plan'] == Configure::read('application_plan_A')) {
                                        $data['application_plan'] = Configure::read('application_plan_C');
                                    } elseif ($data['application_plan'] == Configure::read('application_plan_B')) {
                                        $data['application_plan'] = Configure::read('application_plan_D');
                                    }

                                    //Plan C
                                    if ($data['application_plan'] == Configure::read('application_plan_C')) {
                                        $amount = 0;
                                        $form_type = Configure::read('payment_credit_authentication');

                                    //Plan D
                                    } elseif ($data['application_plan'] == Configure::read('application_plan_D')) {
                                        $amount = $tier41;
                                        $form_type = Configure::read('payment_credit_monthly_payment');
                                    }
                                    break;
                                case '1week':
                                case '1week.trial':
                                case '1week_plan':
                                case '1week_plan_trial':
                                    //change Plan C and Plan D to Plan A and Plan B
                                    if ($data['application_plan'] == Configure::read('application_plan_C')) {
                                        $data['application_plan'] = Configure::read('application_plan_A');
                                    } elseif ($data['application_plan'] == Configure::read('application_plan_D')) {
                                        $data['application_plan'] = Configure::read('application_plan_B');
                                    }

                                    //Plan A
                                    if ($data['application_plan'] == Configure::read('application_plan_A')) {
                                        $amount = 0;
                                        $form_type = Configure::read('mobile_payment_credit_free');

                                    //Plan B
                                    } elseif ($data['application_plan'] == Configure::read('application_plan_B')) {
                                        $amount = $tier8;
                                        $form_type = Configure::read('mobile_payment_credit_paid');
                                    }
                                    break;
                                default:
                                    $validItem = false;
                                    $apiResponse = array('error' => array('id' => Configure::read('error.undefined_item'), 'message' => '内部エラー: 未定義のアイテム'));
                                    break;
                            }

                            if ($validItem) {
                                $this->updateUser($user, Configure::read('card_company.apple'), $data['application_plan'], Configure::read('platform.iphone'), $expiresDate);

                                $apiResponse = $this->Payment->saveApplePayment($user['id'], $latestReceiptInfo['transaction_id'], $amount, $receiptData['response'], $receiptData['base_url'], $form_type);

                                $res = $this->UsersIosReceipt->saveReceipt($user['id'], $data['receipt'], $data['device']);
                                if (!$res) {
                                    $apiResponse = array('error' => array('id' => Configure::read('error.save_to_users_ios_receipt_table_failure'), 'message' => 'Failed to save receipt'));
                                }
                            }
                        }
                    }else{
                        $apiResponse = array('error' => array('id' => Configure::read('error.transaction_already_exists'), 'message' => 'Transaction already exists'));
                    }
                }
            }else{
                $apiResponse = $receiptData;

                //check if subscription has expired
                if ($apiResponse['error']['status'] === 21006) {
                    $apiResponse['error']['id'] = Configure::read('error.subscription_has_expired');
                    $apiResponse['error']['message'] = 'Subscription has expired';
                }
            }

        //Android
        }elseif($data['device'] == 2){
            $receiptData = json_decode($data['receipt'],true);

            $authResult = $this->getAuthAccess();

            if (isset($authResult['error'])) {
                $apiResponse = array('error' => array('id' => Configure::read('error.oauth_error'), 'message' => 'OAuth Error: ' . $authResult['error']));
                return json_encode($apiResponse);
            }

            $verifyReceipt = $this->verifyGoogleSubscription($receiptData['packageName'], $receiptData['productId'], $receiptData['purchaseToken'], $authResult['access_token']);
            $result = $verifyReceipt['result'];

            if (isset($result['error'])) {
                $apiResponse = array('error' => array('id' => Configure::read('error.verify_google_subscription_error'), 'message' => $result['error']['message']));
                return json_encode($apiResponse);
            } elseif($result['developerPayload'] != $receiptData['developerPayload']) {
                $apiResponse = array('error' => array('id' => Configure::read('error.invalid_receipt'), 'message' => 'Invalid receipt'));
                return json_encode($apiResponse);
            }

            $expiryTimeSeconds = intval($result['expiryTimeMillis']) / 1000; //convert milliseconds to seconds
            //check if subscription has expired
            $expiresDate = date('Y-m-d H:i:s', $expiryTimeSeconds);
            if (strtotime($expiresDate) <= strtotime($dateNowWithTime)) {
                $apiResponse = array('error' => array('id' => Configure::read('error.subscription_has_expired'), 'message' => 'Subscription has expired'));
                return json_encode($apiResponse);
            }

            if (isset($receiptData['orderId'])) {
                $orderId = $receiptData['orderId'];
            } else {
                //Test purchases don't have an orderId field. To track test purchases, you use the purchaseToken field instead.
                //https://developer.android.com/google/play/billing/billing_testing.html
                $orderId = 'TEST-' . $receiptData['purchaseToken'];
            }

            if(!$this->PaymentMobileTransactions->checkTransaction($orderId)){
                $apiResponse = array('error' => array('id' => Configure::read('error.transaction_already_exists'), 'message' => 'Transaction already exists'));
                return json_encode($apiResponse);                
            }

            $purchaseTimeSeconds = intval($receiptData['purchaseTime']) / 1000; //convert milliseconds to seconds
            $purchaseDate = date('Y-m-d H:i:s', $purchaseTimeSeconds);
            $TransactionData['PaymentMobileTransactions'] = array(
                'user_id' => $user['id'],
                'original_transaction_id' => $orderId,
                'product_id' => $receiptData['productId'],
                'quantity' => 1,
                'original_purchase_date' => $purchaseDate,
                'application_version' => 'android'
            );
            $this->PaymentMobileTransactions->clear();
            $this->PaymentMobileTransactions->create();
            if (!$this->PaymentMobileTransactions->save($TransactionData)) {
                $apiResponse = array('error' => array('id' => Configure::read('error.save_to_payment_mobile_transactions_table_failure'), 'message' => 'Transaction did not successfully save.'));
                return json_encode($apiResponse);
            }  

            $item = $receiptData['productId'];
            $validItem = true;

            //決済履歴に残す
            switch ($item) {
                case '1month':
                case '1month.trial':
                case '1month2':
                case '1month.trial2':
                case '1month_plan':
                case '1month_plan_trial':
                case 'monthly':
                case 'monthly.trial':
                    //change Plan A and Plan B to Plan C and Plan D
                    if ($data['application_plan'] == Configure::read('application_plan_A')) {
                        $data['application_plan'] = Configure::read('application_plan_C');
                    } elseif ($data['application_plan'] == Configure::read('application_plan_B')) {
                        $data['application_plan'] = Configure::read('application_plan_D');
                    }

                    //Plan C
                    if ($data['application_plan'] == Configure::read('application_plan_C')) {
                        $amount = 0;
                        $form_type = Configure::read('payment_credit_authentication');

                    //Plan D
                    } elseif ($data['application_plan'] == Configure::read('application_plan_D')) {
                        $amount = Configure::read('android_premium_amount');
                        $form_type = Configure::read('payment_credit_monthly_payment');
                    }
                    break;
                case '1week':
                case '1week.trial':
                case '1week_plan':
                case '1week_plan_trial':
                    //change Plan C and Plan D to Plan A and Plan B
                    if ($data['application_plan'] == Configure::read('application_plan_C')) {
                        $data['application_plan'] = Configure::read('application_plan_A');
                    } elseif ($data['application_plan'] == Configure::read('application_plan_D')) {
                        $data['application_plan'] = Configure::read('application_plan_B');
                    }

                    //Plan A
                    if ($data['application_plan'] == Configure::read('application_plan_A')) {
                        $amount = 0;
                        $form_type = Configure::read('mobile_payment_credit_free');

                    //Plan B
                    } elseif ($data['application_plan'] == Configure::read('application_plan_B')) {
                        $amount = Configure::read('android_weekly_amount');
                        $form_type = Configure::read('mobile_payment_credit_paid');
                    }
                    break;
                default:
                    $validItem = false;
                    $apiResponse = array('error' => array('id' => Configure::read('error.undefined_item'), 'message' => 'Undefined subscription.'));
                    break;
            }

            if ($validItem) {
                $this->updateUser($user, Configure::read('card_company.google'), $data['application_plan'], Configure::read('platform.android'), $expiresDate);

                $apiResponse = $this->Payment->saveGooglePayment($user['id'], $orderId, $amount, $verifyReceipt['response'], $form_type);

                $res = $this->UsersIosReceipt->saveReceipt($user['id'], $data['receipt'], $data['device']);
                if (!$res) {
                    $apiResponse = array('error' => array('id' => Configure::read('error.save_to_users_ios_receipt_table_failure'), 'message' => 'Failed to save receipt'));
                }
            }
        }
        return json_encode($apiResponse);
    }

    public function updateUser($user, $cardCompany, $applicationPlan, $platform, $nextChargeDate) {
        # user array to be saved
        $saveUserArr = array(
            'User' => array(
                'modified' => date('YmdHis'),
                'fail_flg' => 0,
                'charge_flg' => 1,
                'card_company' => $cardCompany,
                'platform' => $platform,
                'hash16' => $user['id'],
                'last_charge_date' => date('YmdHis')
            )
        );

        # check user charge_flg
        if ( $user['charge_flg'] == 0 ) {
            $saveUserArr['User']['first_charge_date'] = date('YmdHis');
        }

        # get and set next charge date
        $saveUserArr['User']['next_charge_date'] = $nextChargeDate;

        //Plan B or Plan D
        if ($applicationPlan == Configure::read('application_plan_B') || $applicationPlan == Configure::read('application_plan_D')) {
            $saveUserArr['User']['double_check_flg'] = 1;

            # give points if user join campaign and success payment
            $this->ContinuationCampaign->givePoints(array(
                'user_id' => $user['id']
            ));
        }

        # update the user information
        $this->User->validate = array();
        $this->User->read(null, $user['id']);
        $this->User->set($saveUserArr);

        if ($this->User->save()) {
            # check if user is temporary
            if ($user['status'] == 0) {
                # update user's status
                $this->User->validate = array();
                $this->User->read(null, $user['id']);
                $this->User->set('status', 1);
                $this->User->save();
            }
        }
    }

    public function verifyAppleReceipt($receipt, $password) {
        $apiCommon = new ApiCommonController();
        return $apiCommon->verifyAppleReceipt($receipt, $password);
    }

    public function getAuthAccess() {
        $apiCommon = new ApiCommonController();
        return $apiCommon->getAuthAccess();
    }

    public function verifyGoogleSubscription($packageName, $productId, $purchaseToken, $accessToken) {
        $apiCommon = new ApiCommonController();
        return $apiCommon->verifyGoogleSubscription($packageName, $productId, $purchaseToken, $accessToken);
    }
}
