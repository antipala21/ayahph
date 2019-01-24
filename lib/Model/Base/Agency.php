<?php
App::uses('AppModel', 'Model');

class Agency extends AppModel {

	public $validate = array(
		// 'username' => array(
		// 	'nonEmpty' => array(
		// 		'rule' => array('notBlank'),
		// 		'message' => 'A username is required',
		// 		'allowEmpty' => false
		// 	),
		// 	'between' => array( 
		// 		'rule' => array('between', 5, 15), 
		// 		'required' => true, 
		// 		'message' => 'Usernames must be between 5 to 15 characters'
		// 	),
		// 	 'unique' => array(
		// 		'rule'    => array('isUniqueUsername'),
		// 		'message' => 'This username is already in use'
		// 	),
		// 	'alphaNumericDashUnderscore' => array(
		// 		'rule'    => array('alphaNumericDashUnderscore'),
		// 		'message' => 'Username can only be letters, numbers and underscores'
		// 	),
		// ),
		'_password' => array(
			'notBlank' => array(
				'rule' => 'notBlank',
				'message' => 'A password is required' // Password is required item.
			),
			'min_length' => array(
				'rule' => array('minLength', '6'),  
				'message' => 'Password must have a mimimum of 6 characters'
			)
		),
		
		'password_confirm' => array(
			'required' => array(
				'rule' => array('notBlank'),
				'message' => 'Please confirm your password'
			),
			 'equaltofield' => array(
				'rule' => array('equaltofield','_password'),
				'message' => 'Both passwords must match.'
			)
		),
		
		'email' => array(
			'required' => array(
				'rule' => array('email', true),
				'message' => 'Please provide a valid email address.'
			),
			 'unique' => array(
				'rule'    => array('isUniqueEmail'),
				'message' => 'This email is already in use',
			),
			'between' => array( 
				'rule' => array('between', 6, 60), 
				'message' => 'Usernames must be between 6 to 60 characters'
			),
			'code' => array(
				'rule' => '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/i',
				'message' => 'Email contains invalid character!'
			)
		),

		'user_type' => array(
			'valid' => array(
				'rule' => array('inList', array('1', '2',)),
				'message' => 'Please enter a valid user type',
				'allowEmpty' => false
			)
		),

		'user_role' => array(
			'valid' => array(
				'rule' => array('inList', array('1', '2', '3')),
				'message' => 'Please enter a valid role',
				'allowEmpty' => false
			)
		),

		'address' => array(
			'required' => array(
				'rule' => array('notBlank'),
				'message' => 'Please Enter Address'
			),
		),

		'birthdate' => array(
			'empty' => array(
				'rule' => 'notBlank',
				'message' => 'Birthday is required'
			),
			'future' => array(
				'rule' => 'checkbirthday',
				'message' => 'Please enter your birthdate'
			),
		),
		
		'password_update' => array(
			'min_length' => array(
				'rule' => array('minLength', '6'),
				'message' => 'Password must have a mimimum of 6 characters',
				'allowEmpty' => true,
				'required' => false
			)
		),
		'password_confirm_update' => array(
			 'equaltofield' => array(
				'rule' => array('equaltofield','password_update'),
				'message' => 'Both passwords must match.',
				'required' => false,
			)
		),
		'email_confirm_hash' => array(
			'required' => array(
				'rule' => array('notBlank'),
				'message' => 'Please Enter Address'
			),
		)
	);
	
		/**
	 * Before isUniqueUsername
	 * @param array $options
	 * @return boolean
	 */
	function isUniqueUsername($check) {

		$username = $this->find(
			'first',
			array(
				'fields' => array(
					'User.id',
					'User.username'
				),
				'conditions' => array(
					'User.username' => $check['username']
				)
			)
		);

		if(!empty($username)){
			if($this->data[$this->alias]['id'] == $username['User']['id']){
				return true; 
			}else{
				return false; 
			}
		}else{
			return true; 
		}
	}

	/**
	 * Before isUniqueEmail
	 * @param array $options
	 * @return boolean
	 */
	function isUniqueEmail($check) {

		$email = $this->find(
			'first',
			array(
				'fields' => array(
					'User.id'
				),
				'conditions' => array(
					'User.email' => $check['email']
				)
			)
		);

		if(!empty($email)){
			if($this->data[$this->alias]['id'] == $email['User']['id']){
				return true; 
			}else{
				return false; 
			}
		}else{
			return true; 
		}
	}
	
	public function alphaNumericDashUnderscore($check) {
		// $data array is passed using the form field name as the key
		// have to extract the value to make the function generic
		$value = array_values($check);
		$value = $value[0];

		return preg_match('/^[a-zA-Z0-9_ \-]*$/', $value);
	}
	
	public function equaltofield($check,$otherfield) { 
		//get name of field 
		$fname = ''; 
		foreach ($check as $key => $value){ 
			$fname = $key; 
			break; 
		}
		return $this->data[$this->name][$otherfield] === $this->data[$this->name][$fname]; 
	}

	// CHeck birthday
	public function checkbirthday($check) {
		if(0 <= (strtotime(date('Y-m-d'))-strtotime($check['birthday']))){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * Before Save
	 * @param array $options
	 * @return boolean
	 */
	 public function beforeSave($options = array()) {
		// hash our password
		if (isset($this->data[$this->alias]['password']) && !empty($this->data[$this->alias]['password'])) {
			$this->data[$this->alias]['password'] = AuthComponent::password($this->data[$this->alias]['password']);
		}
		
		// if we get a new password, hash it
		if (isset($this->data[$this->alias]['password_update']) && !empty($this->data[$this->alias]['password_update'])) {
			$this->data[$this->alias]['password'] = AuthComponent::password($this->data[$this->alias]['password_update']);
		}
	
		// fallback to our parent
		return parent::beforeSave($options);
	}

	/**
	 * Get User account info
	 */
	public function getUserAccount ($id = null) {
		$result = $this->find('first', array(
			'fields' => array(
				'*'
			),
			'conditions' => array(
				'id' => $id
			)
		));
		return $result;
	}

	public function getUserByEmailHash($hash = false) {
		if (!$hash) {
			return false;
		}
		return $this->find('first', array(
			'fields' => array(
				'User.*'
			),
			'conditions' => array(
				'User.points' => $hash
			)
		));
	}
	
	public function updateStatus($id, $status) {
		if (!isset($id)) {
			return false;
		}

		$this->read(null, $id);
		$this->saveField('status', 1);
		$this->saveField('points', '');

		return true;
	}

	// public $virtualFields = array(
	// 	'rating' => 'sum(User.rate / User.rate_count)'
	// );
}