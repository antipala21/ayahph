<?php
App::uses('AppController', 'Controller');
class RequesterController extends AppController {
	
    public $uses = array('User', 'CampaignMaster');
        
    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow();
        //$this->autoRender = false;
        $this->notFound = __('Page you are looking for can not be either access not found.');
    }
    public function index(){
        
    }
}