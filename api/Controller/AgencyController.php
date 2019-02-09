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

		$this->Agency->virtualFields['total_nursemaid'] = "SELECT COUNT(*) FROM `nurse_maids` WHERE `agency_id` = `Agency`.`id`";
		$this->Agency->virtualFields['male_nursemaid'] = "SELECT COUNT(*) FROM `nurse_maids` WHERE `agency_id` = `Agency`.`id` AND `gender` = 1";
		$this->Agency->virtualFields['female_nursemaid'] = "SELECT COUNT(*) FROM `nurse_maids` WHERE `agency_id` = `Agency`.`id` AND `gender` = 0";
		$this->Agency->virtualFields['current_available'] = "SELECT COUNT(*) FROM `nurse_maids` WHERE `agency_id` = `Agency`.`id` AND `status` = 1";

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
			),
			'conditions' => array(
				'Agency.status' => 1,
				'Agency.display_flg' => 1
			)
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
