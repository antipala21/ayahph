<?php
App::uses('AppController', 'Controller');
class AgencyDetailController extends AppController {

	public $uses = array(
		'User',
		'Agency',
		'Announcement'
	);

	public function beforeFilter() {
		parent::beforeFilter();
	}

	public function detail () {

		$id = isset($this->params['id']) ? $this->params['id'] : null;

		// trap if no id
		if (!isset($id)) {
			return $this->redirect('/');
		}

		$this->Agency->virtualFields['total_nursemaid'] = "SELECT COUNT(*) FROM `nurse_maids` WHERE `agency_id` = $id";
		$this->Agency->virtualFields['male_nursemaid'] = "SELECT COUNT(*) FROM `nurse_maids` WHERE `agency_id` = $id AND `gender` = 1";
		$this->Agency->virtualFields['female_nursemaid'] = "SELECT COUNT(*) FROM `nurse_maids` WHERE `agency_id` = $id AND `gender` = 0";
		$this->Agency->virtualFields['current_available'] = "SELECT COUNT(*) FROM `nurse_maids` WHERE `agency_id` = $id AND `status` = 1";
		$this->Agency->virtualFields['total_transaction'] = "SELECT COUNT(*) FROM `transactions` WHERE `agency_id` = `Agency`.`id`";
		$this->Agency->virtualFields['total_announcements'] = "SELECT COUNT(*) FROM `announcements` WHERE `agency_id` = `Agency`.`id`";
		$this->Agency->virtualFields['rating'] = "SELECT AVG(`rate`) FROM `nurse_maid_ratings` WHERE `nurse_maid_ratings`.`agency_id` = `Agency`.`id`";
		$this->Agency->virtualFields['rating_count'] = "SELECT COUNT(`rate`) FROM `nurse_maid_ratings` WHERE `nurse_maid_ratings`.`agency_id` = `Agency`.`id`";

		$agency = $this->Agency->find('first', array(
			'fields'=> array(
				'Agency.id',
				'Agency.name',
				'Agency.email',
				'Agency.address',
				'Agency.image_url',
				'Agency.description',
				'Agency.phone_number',
				'Agency.short_description',
				'Agency.representative_name',
				'Agency.total_nursemaid',
				'Agency.male_nursemaid',
				'Agency.female_nursemaid',
				'Agency.current_available',
				'Agency.total_transaction',
				'Agency.total_announcements',
				'Agency.rating',
				'Agency.rating_count',
			),
			'conditions' => array(
				'Agency.id' => $id,
				'Agency.status' => 1,
				'Agency.display_flg' => 1
			)
		));

		// trap if agency not found
		if (!isset($agency['Agency'])) {
			return $this->redirect('/');
		}
		$this->set('agency', $agency['Agency']);
	}


	public function announcement () {
		$id = isset($this->params['id']) ? $this->params['id'] : null;

		// trap if no id
		if (!isset($id)) {
			return $this->redirect('/');
		}

		$agency = $this->Agency->find('first', array(
			'fields'=> array(
				'Agency.id',
				'Agency.name',
				'Agency.email',
			),
			'conditions' => array(
				'Agency.id' => $id,
				'Agency.status' => 1,
				'Agency.display_flg' => 1
			)
		));


		$announcements = $this->Announcement->find('all', array(
			'conditions' => array(
				'Announcement.agency_id' => $id,
				// 'Announcement.status' => 1
			)
		));

		$this->set('announcements', $announcements);
		$this->set('agency', $agency);
	}

}
