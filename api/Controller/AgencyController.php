<?php
App::uses('AppController', 'Controller');
class AgencyController extends AppController {

	public $uses = array(
		'Agency'
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('index');
	}

	public function index () {
		$this->autoRender = false;
		$response = array();

		$request_data = json_decode(stripslashes($this->request->data['params']));
		$data = (array) $request_data;

		$order_by = 'id DESC';

		if (isset($data['order']) && !empty($data['order']) && in_array($data['order'], Configure::read('sort_key'))) {
			$order_by = $data['order'] . ' DESC';
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

		if ($agencies) {
			$_agencies = array();
			foreach ($agencies as $key => $value) {
				$_agencies[] = $value['Agency'];
			}
			$response['agencies'] = $_agencies;
		}
		return json_encode($response);
	}

}
