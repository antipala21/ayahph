<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class UsersTimezonesController extends AppController {
    public $uses = array('User','Timezone','CountryCode');
    
    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow(array('index'));
        $this->autoRender = false;
    }

    public function index(){
        $response = array();
        $data = json_decode($this->request->input(),true);
        if(!$this->request->is('post')) {
            $response['error']['id'] = Configure::read('error.invalid_request');
            $response['error']['message'] = __('Invalid request');
        } else {
            $continents = Configure::read('continents');
            $mem = new myMemcached();
            $response = $mem->get('ApiTimezones');

            // check if has no memcache
            //TODO : this is in dev only
            //if (!$response) {
            if (true) {
                $ctr = 0;
                $data = array();
                $con['args'] = array(
                        'fields' => array('id', 'continent_id', 'jp_time_diff', 'city_eng', 'utc_offset'),
                        'conditions' => array('status' => 1),
                        'order' => array('jp_time_diff' => 'ASC'),
                        'recursive' => -1
                    );

                //fetch timezones from db
                $timezones = $this->Timezone->getTimezones($con);
            	foreach ($timezones as $value) {
                    $tz = $value['Timezone'];
                    if (empty($continents[$tz['continent_id']])) {
                        continue;
                    }

                    $tzname = $continents[$tz['continent_id']];            
                    $tzname .= !empty($tz['city_eng']) ? '/' . $tz['city_eng'] : '';

                    //get time diff and utc
                    $jpTimeDiff = $this->Timezone->computeTimeDiff(array(
                            'continent_id' => $tz['continent_id'],
                            'city' => $tz['city_eng']
                        ));

                    if ($jpTimeDiff['success']) {
                        $utcOffset = $tz['utc_offset'];
                        $data[$ctr]['id'] = (int)$tz['id'];                    
                        $data[$ctr]['city_name'] = $jpTimeDiff['timezoneName'];
                        $data[$ctr]['utc_offset'] = $jpTimeDiff['utc'];                    
                        $data[$ctr]['jp_time_diff'] = (int)$jpTimeDiff['timeDiff'];
                        $temp[$ctr]['jp_time_diff'] = (int)$jpTimeDiff['timeDiff'];
                        $ctr++;
                    }
                }
                
                array_multisort($temp, SORT_ASC,$data);
                $response = array('timezone' => $data);
                $mem->set(array(
                    'key' => 'ApiTimezones',
                    'value' => $response,
                    'expire' => 86400 // 1 day
                ));
            }
        }
        return json_encode($response);
    }
}