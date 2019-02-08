<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
class UsersUpdateController extends AppController {

	public $uses = array(
		'User',
		'FileStorage',
		'Timezone',
		'CountryCode',
		'SettlementCurrency',
		'Country',
		'Nationality'
	);
	public $components = array('Image');

	private $id = 0;
	private $notFound = "Invalid request.";
	private $imgFile = "";

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('index'));
		$this->notFound = __($this->notFound);
		$this->autoRender = false;
	}

	public function index() {
		$data = json_decode($this->request->input(),true);

		// For Testing
		if($this->request->data) {
			foreach($this->request->data as $key => $value) {
				$data[$key] = $value;
			}
		}
		// End

		$response = array();
		if(!($data)) {
			$response['error']['id'] = Configure::read('error.invalid_request');
			$response['error']['message'] = __('Invalid request');
		} else {
			$api = new ApiCommonController();
		$user['User'] = $api->validateToken($data['users_api_token']);
			if (isset($data['users_api_token']) && !empty($data['users_api_token'])) {
				if ($user['User']) {

					$this->id = $user['User']['id'];
					$updateData = array();
					if ($user['User']['status'] != 1) {
						// $response['error']['message'] = __('Invalid user'); // user is inactive
					}

					if (isset($data['users_username'])) {
						if (!preg_match("/^[.-a-zA-Z\-0-9 ]+$/i",$data['users_username'])) {
							$response['error']['id'] = Configure::read('error.please_enter_alphanumeric_characters');
							$response['error']['message'] = __('半角英数字でご入力ください');
						} else {
							$updateData['nickname'] = $data['users_username'];
						}
					}

					if (isset($data['users_email'])) {
						if ($data['users_email'] == $user['User']['email']) {
							unset($this->User->validate['email']);
						} else if (UserTable::checkEmail($data['users_email'], $this->id)) {
							$response['error']['id'] = Configure::read('error.user_validation_error');
							$response['error']['message'] = __('入力されたメールアドレスは使用できません。');
						} else {
							$this->User->validate['email']['notBlank']['message'] = __('Invalid users_email');
						}
						$updateData['email'] = $data['users_email'];
					}
					if (isset($data['users_birthday'])) {
						$this->User->validate['birthday'] = array('rule' => array('date'));
						$this->request->data['birthday'] = $data['users_birthday'];
      			$this->User->set(array('birthday' => $data['users_birthday']));
						if (preg_match("/[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/", $data['users_birthday'])) {
							$updateData['birthday'] = $data['users_birthday'];
						} else {
							$response['error']['id'] = Configure::read('error.invalid_date_format');
							$response['error']['message'] = __('Invalid date format');
						}
					}

					if (isset($data['users_gender'])) {
						if (!is_int($data['users_gender'])) {
							$response['error']['id'] =  Configure::read('error.users_gender_must_be_integer');
							$response['error']['message'] =  __('users_gender must be integer');
						} else if (in_array($data['users_gender'],array(1,2,3))) {
							$updateData['gender'] = $data['users_gender'];
						} else {
							$response['error']['id'] =  Configure::read('error.invalid_users_gender');
							$response['error']['message'] =  __('users_gender must be 1 to 3');
						}
					}

					if (isset($_FILES['profile_image'])) {
						if (!$this->validateImage($_FILES['profile_image'])) {
							$response['error']['id'] =  Configure::read('error.invalid_image_type');
							$response['error']['message'] = __("Invalid image type.");
						} else {
							$this->imgFile = $_FILES['profile_image'];
						}
					}

					if(isset($data['users_password'])) {
						if(empty($data['users_password'])) {
							$response['error']['id'] = Configure::read('error.users_password_must_not_be_empty');
							$response['error']['message'] = __('users_password must not empty');
						} elseif(!is_string($data['users_password'])) {
							$response['error']['id'] = Configure::read('error.users_password_must_be_string');
							$response['error']['message'] = __('users_password must be string');
						} elseif(!in_array(strlen($data['users_password']), range(8,16))) {
							$response['error']['id'] = Configure::read('error.invalid_users_password_length');
							$response['error']['message'] = __('users_password must be 8 to 16 characters');
						}
						$updateData['password'] = $data['users_password'];
						$newHashedPassword = AuthComponent::password($data['users_password']);
					}

					if(isset($data['users_status'])) {
						if(!is_int($data['users_status'])) {
							$response['error']['id'] = Configure::read('error.users_status_must_be_integer');
							$response['error']['message'] = __('users_status must be integer');
						} elseif(in_array($data['users_status'], array(1,2,3))) {
							$statusSelect = array(
								1 => 0,
								2 => 1,
								3 => 9
							);
							$updateData['status'] = $statusSelect[$data['users_status']];
						} else {
							$response['error']['id'] = Configure::read('error.invalid_users_status');
							$response['error']['message'] = __('users_status must be 1 to 3');
						}
					}

					//put idfa to give the cmcode  (if user have registered by app)
					if (isset($data['idfa'])) {
						$updateData['idfa'] = $data['idfa'];
					}

					//for magazine_flg
					if (isset($data['magazine_flg'])) {
						if (!in_array($data['magazine_flg'], array(1, 2))) {
							$response['error']['id'] = Configure::read('error.magazine_flg_invalid');
							$response['error']['message'] = __('magazine_flg must be 1 or 2');
						} else {
							$updateData['magazine_flg'] = $data['magazine_flg'];
						}
					}		

					//for reservation_mail_flg
					if (isset($data['reservation_mail_flg'])) {
						if (!in_array($data['reservation_mail_flg'], array(1, 2))) {
							$response['error']['id'] = Configure::read('error.reservation_mail_flg_invalid');
							$response['error']['message'] = __('reservation_mail_flg must be 1 or 2');
						} else {
							$updateData['reservation_mail_flg'] = $data['reservation_mail_flg'];
						}
					}					

					//for reservation_cancel_mail_flg
					if (isset($data['reservation_cancel_mail_flg'])) {
						if (!in_array($data['reservation_cancel_mail_flg'], array(1, 2))) {
							$response['error']['id'] = Configure::read('error.reservation_cancel_mail_flg_invalid');
							$response['error']['message'] = __('reservation_cancel_mail_flg must be 1 or 2');
						} else {
							$updateData['reservation_cancel_mail_flg'] = $data['reservation_cancel_mail_flg'];
						}
					}
					// NC-4857
					//for users_language
					if (isset($data['users_language'])) {						
						$default = $this->SettlementCurrency->getDefaultCurrencyInfo();
						$userCurrency = $user['User']['settlement_currency_id'] != '' ? $user['User']['settlement_currency_id'] : $default['id'];
						if ($userCurrency == $default['id']) {
							if ($data['users_language'] == 'ja' || $data['users_language'] == 'en') {							
								$updateData['native_language2'] = $data['users_language'];
							} else {
								$response['error']['id'] = Configure::read('error.users_language_invalid');
								$response['error']['message'] = __('users_language is not supported');
							}
						} else {
							$response['error']['id'] = Configure::read('error.invalid_request');
							$response['error']['message'] = __('Invalid request');
						}						
					}

					//for users_timezone_id
					if (isset($data['users_timezone_id']) && trim($data['users_timezone_id']) != null){
						$cond = array('id' => $data['users_timezone_id'], 'status' => 1);
						if(!$this->Timezone->checkIfTimezoneExist($cond)) {
							$response['error']['id'] = Configure::read('error.users_timezone_id_invalid');
							$response['error']['message'] = __('users_timezone_id is invalid');
						} else {
							$updateData['timezone_id'] = $data['users_timezone_id'];
						}
					}

					//NC-5067 for residence_id
					if (isset($data['residence_id'])) {
						if (!is_int($data['residence_id'])) {
							$response['error']['id'] =  Configure::read('error.residence_id_must_be_integer');
							$response['error']['message'] =  __('residence_id must be integer');
						} else if (!$this->Country->checkCountryExist($data['residence_id'])) {
							$response['error']['id'] = Configure::read('error.residence_id_invalid');
							$response['error']['message'] = __('residence_id is invalid');
						}
						$updateData['residence_id'] = $data['residence_id'];
					}

					//for nationality_id
					if (isset($data['nationality_id'])) {
						if (!is_int($data['nationality_id'])) {
							$response['error']['id'] =  Configure::read('error.nationality_id_must_be_integer');
							$response['error']['message'] =  __('nationality_id must be integer');
						} else if (!$this->Nationality->checkCountryExist($data['nationality_id'])) {
							$response['error']['id'] = Configure::read('error.nationality_id_invalid');
							$response['error']['message'] = __('nationality_id is invalid');
						}
						$updateData['nationality_id'] = $data['nationality_id'];
					}

					//for residence_show_flg
					if (isset($data['residence_show_flg'])) {
						if (!is_int($data['residence_show_flg'])) {
							$response['error']['id'] =  Configure::read('error.residence_show_flg_must_be_integer');
							$response['error']['message'] =  __('residence_show_flg must be integer');
						} else if (!(in_array($data['residence_show_flg'], array(0,1)))) {
							$response['error']['id'] = Configure::read('error.residence_show_flg_invalid');
							$response['error']['message'] = __('residence_show_flg must be 0 or 1');
						}
						$updateData['residence_show_flg'] = $data['residence_show_flg'];
					}

					//for nationality_show_flg
					if (isset($data['nationality_show_flg'])) {
						if (!is_int($data['nationality_show_flg'])) {
							$response['error']['id'] =  Configure::read('error.nationality_show_flg_must_be_integer');
							$response['error']['message'] =  __('nationality_show_flg must be integer');
						} else if (!(in_array($data['nationality_show_flg'], array(0,1)))) {
							$response['error']['id'] = Configure::read('error.nationality_show_flg_invalid');
							$response['error']['message'] = __('nationality_show_flg must be 0 or 1');
						}
						$updateData['nationality_show_flg'] = $data['nationality_show_flg'];
					}

					//for users_gender_show_flg
					if (isset($data['users_gender_show_flg'])) {
						if (!is_int($data['users_gender_show_flg'])) {
							$response['error']['id'] =  Configure::read('error.users_gender_show_flg_must_be_integer');
							$response['error']['message'] =  __('users_gender_show_flg must be integer');
						} else if (!(in_array($data['users_gender_show_flg'], array(0,1)))) {
							$response['error']['id'] = Configure::read('error.users_gender_show_flg_invalid');
							$response['error']['message'] = __('users_gender_show_flg must be 0 or 1');
						}
						$updateData['gender_show_flg'] = $data['users_gender_show_flg'];
					}

					//for users_birthday_show_flg
					if (isset($data['users_birthday_show_flg'])) {
						if (!is_int($data['users_birthday_show_flg'])) {
							$response['error']['id'] =  Configure::read('error.users_birthday_show_flg_must_be_integer');
							$response['error']['message'] =  __('users_birthday_show_flg must be integer');
						} else if (!(in_array($data['users_birthday_show_flg'], array(0,1)))) {
							$response['error']['id'] = Configure::read('error.users_birthday_show_flg_invalid');
							$response['error']['message'] = __('users_birthday_show_flg must be 0 or 1');
						}
						$updateData['birthday_show_flg'] = $data['users_birthday_show_flg'];
					}

					$this->User->validate['nickname']['notBlank']['message'] = __('users_username must not empty');
					$this->User->validate['nickname']['code']['message'] = __('Please enter users_username in single-byte letters');
					$this->User->validate['nickname']['maxLength']['message'] = __('users_username should be entered in up to 50 characters');
					$this->User->validate['birthday']['message'] = __('Invalid date format');
					if (empty($response)) {
						if (!empty($updateData) || !empty($this->imgFile)) {
							$this->User->set($updateData);
							$this->User->id = $user['User']['id'];
							if ($this->User->validates() && empty($this->imgFile)) {
								if(isset($updateData['password'])) {
									$this->User->validate['password'] = array();
									$updateData['password'] = $newHashedPassword;
									$this->User->set($updateData);
								}
								if(isset($updateData['nickname'])) {
									$ipAddress = $_SERVER["REMOTE_ADDR"];
									$CompanyIPflg = myTools::checkCompanyIP($ipAddress);
									if($CompanyIPflg){
										$this->User->validate['nickname'] = array();
										$updateData['nickname'] = '%%%TEST%%%'.$updateData['nickname'];
										$this->User->set($updateData);
									}
								}
								$this->User->save();
								$response['updated'] = true;
							} else if ($this->User->validates() && $this->imgFile) {
								$uploaded = $this->uploadFiles(array(
										'folder' => 'img/uploads',
										'formdata' => array($this->imgFile),
										'id' => $user['User']['id']
									));
								if(isset($uploaded['urls'])) {
									$updateData['image_url'] = $uploaded['urls'][0];
									$this->User->set($updateData);
									if($this->User->save()) {
										$response['updated'] = true;
										$response['profile_image'] = $uploaded['urls'][0];
									}
								} else {
									$response['error']['id'] = Configure::read('error.upload_file_error');
									$response['error']['message'] = __($uploaded['errors'][0]);
								}
							} else {
								$response = array();
								foreach($this->User->validationErrors as $key => $error) {
									$response['error']['id'] = Configure::read('error.user_validation_error');
									$response['error']['message'] = __($error[0]);
								}
							}
						} else {
							$response['error']['id'] = Configure::read('error.no_updates');
							$response['error']['message'] = __("No updates");
						}
					}
				} else if (isset($data['users_api_token']) && !is_string($data['users_api_token'])) {
					$response['error']['id'] = Configure::read('error.users_api_token_must_be_string');
					$response['error']['message'] = __('users_api_token must be string');
				} else if (!$user['User']) {
					$response['error']['id'] = Configure::read('error.invalid_api_token');
					$response['error']['message'] = __('Invalid users_api_token');
				}
			} else {
				$response['error']['id'] = Configure::read('error.users_api_token_is_required');
				$response['error']['message'] = __('users_api_token is required');
			}
		}
		return json_encode($response);
	}

	function uploadFiles($params = array()) {
		// setup dir names absolute and relative
		$app = '/user/webroot';

		//$folder_url = WWW_ROOT.$folder;
		$folder_url = ROOT.$app.'/'.$params['folder'];
		$rel_url = "$app/" . $params['folder'];

		// create the folder if it does not exist
		if (!is_dir($folder_url)) {
			mkdir($folder_url);
		}

		// list of permitted file types, this is only images but documents can be added
		$permitted = array('image/gif','image/jpeg','image/pjpeg','image/png');

		// loop through and deal with the files

		foreach($params['formdata'] as $file) {

			// replace spaces with underscores
			//$filename = str_replace(' ', '_', $file['name']);
			$now = date('Y_m_d_His');
			$ext = $this->getFileExtension($file['name']);
			$filename = $now.uniqid().'.'.$ext;
			// assume filetype is false
			$typeOK = false;

			// check filetype is ok
			foreach($permitted as $type) {
				if ($type == $file['type']) {
					$typeOK = true;
					break;
				}
			}

			// if file type ok upload the file
			if ($typeOK) {
				// switch based on error code
				switch($file['error']) {
					case 0:
						$full_url = $folder_url.'/'.$filename;
						$url = $rel_url.'/'.$filename;
						$success = move_uploaded_file($file['tmp_name'], $full_url);

						// if upload was successful
						if ($success) {
							$imageCom = new ImageComponent(new ComponentCollection());
							$imageCom->prepare($full_url);
							$imageCom->resize(300,300);//width,height,Red,Green,Blue
							$imageCom->save($folder_url.'/'.$filename);
							//upload to amazon
							$resultUpload = $this->FileStorage->uploadFile(array(
								'uploader_id' => $params['id'],
								'uploader_type' => 1,//user image
								'source' => $folder_url.'/'.$filename,
								'key' => $filename,
								'file' => $file,
								'delete_image' => true
							));
							if ($resultUpload) {
								$result['urls'][] = $resultUpload['FileStorage']['url'];
							} else {
								$result['errors'][] = __("Error uploaded $filename. Please try again.");
							}
						} else {
							$result['errors'][] = __("Error uploaded $filename. Please try again.");
						}
						break;
					case 3:
						// an error occured
						$result['errors'][] = __("Error uploading $filename. Please try again.");
						break;
					default:
						// an error occured
						$result['errors'][] = __("System error uploading $filename. Contact webmaster.");
						break;
				}
			} elseif($file['error'] == 4) {
				// no file was selected for upload
				$result['nofiles'][] = __("No file Selected");
			} else {
				// unacceptable file type
				$result['errors'][] = __("$file[name] cannot be uploaded. Acceptable file types: gif, jpg, png.");
			}
		}
		return $result;
	}

	function deletePrevImage($folder) {

		// setup dir names absolute and relative
		$app = '/user/webroot';

		//$folder_url = WWW_ROOT.$folder;
		$folder_url = ROOT.$app.'/'.$folder;
		$rel_url = "$app/$folder";

		//deleting previous picture
		$this->User->openDBReplica();
		$data = $this->User->findById($this->id);
		$this->User->closeDBReplica();
		if (is_file($folder_url.'/'.$data['User']['image'])) {
			unlink($folder_url.'/'.$data['User']['image']);
		}
	}

	function getFileExtension($fileName) {
		return @strtolower(end(explode(".", basename($fileName))));
	}

	function validateImage($file) {
		$size = GetImageSize($file['tmp_name']);
		if ($size[2] != 1 && $size[2] != 2 && $size[2] != 3) {
			return false;
		}
		return true;
	}

}