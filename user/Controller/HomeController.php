<?php
App::uses('AppController', 'Controller');
class HomeController extends AppController {

	public $uses = array(
		'User',
		'Agency',
		'Announcement'
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('index');
	}

	public function index () {

		if ($this->request->is('get')) {
			$get = array_map('trim',$this->request->query);
			foreach($get as $key => $val){
				$this->set($key,$val);
			}

			$this->set('sort_value', array_flip(Configure::read('sort_key')));
			$this->set('get',$get);
		}

		$order_key = Configure::read('sort_key');
		$order_by = 'id DESC';

		if (isset($get['order']) && !empty($get['order']) && in_array($get['order'], Configure::read('sort_key'))) {
			$order_by = $get['order'] . ' DESC';
		}

		$this->Agency->virtualFields['total_nursemaid'] = "SELECT COUNT(*) FROM `nurse_maids` WHERE `agency_id` = `Agency`.`id`";
		$this->Agency->virtualFields['male_nursemaid'] = "SELECT COUNT(*) FROM `nurse_maids` WHERE `agency_id` = `Agency`.`id` AND `gender` = 1";
		$this->Agency->virtualFields['female_nursemaid'] = "SELECT COUNT(*) FROM `nurse_maids` WHERE `agency_id` = `Agency`.`id` AND `gender` = 0";
		$this->Agency->virtualFields['current_available'] = "SELECT COUNT(*) FROM `nurse_maids` WHERE `agency_id` = `Agency`.`id` AND `status` = 1";
		$this->Agency->virtualFields['total_transaction'] = "SELECT COUNT(*) FROM `transactions` WHERE `agency_id` = `Agency`.`id`";

		$this->Agency->virtualFields['rating'] = "SELECT AVG(`rate`) FROM `nurse_maid_ratings` WHERE `nurse_maid_ratings`.`agency_id` = `Agency`.`id`";
		$this->Agency->virtualFields['rating_count'] = "SELECT COUNT(`rate`) FROM `nurse_maid_ratings` WHERE `nurse_maid_ratings`.`agency_id` = `Agency`.`id`";

		$agencies = $this->Agency->find('all', array(
			'fields'=> array(
				'Agency.id',
				'Agency.email',
				'Agency.name',
				'Agency.description',
				'Agency.short_description',
				'Agency.representative_name',
				'Agency.address',
				'Agency.phone_number',
				'Agency.image_url',
				'Agency.total_nursemaid',
				'Agency.male_nursemaid',
				'Agency.female_nursemaid',
				'Agency.current_available',
				'Agency.total_transaction',
				'Agency.rating',
				'Agency.rating_count',
			),
			'conditions' => array(
				'Agency.status' => 1,
				'Agency.display_flg' => 1
			),
			'order' => $order_by
		));

		$this->set('agencies', $agencies);

	}

}
