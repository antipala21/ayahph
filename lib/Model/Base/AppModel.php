<?php
/**
 * Application model for CakePHP.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright	 Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link		  http://cakephp.org CakePHP(tm) Project
 * @package	   app.Model
 * @since		 CakePHP(tm) v 0.2.9
 * @license	   http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Model', 'Model');
App::uses('myMemcached', 'Lib');
App::uses('ConnectionManager', 'Model');

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package	   app.Model
 */
class AppModel extends Model {
	// available replica
	private $replicas = array();
	
	// default replica
	private $selectedReplica = 'replica1';
	
	// data source				
	private $dataSource = null;
	
	// memcached object					
	private $memcached = null;
	
	// construct function for model
	public function __construct($id = false, $table = null, $ds = null){
		parent::__construct($id, $table ,$ds);
		$this->memcached = class_exists('myMemcached') ? new myMemcached() : null;
	}
  	
	// inform model to use replica
	public function useReplica () {
		$this->openDBReplica();
		return $this;
	}
	
	// open database replica
	public function openDBReplica(){
		// select database replica
		$this->selectDBReplica();
		
		// if config key name is default
		if ($this->selectedReplica == 'default') {
			return true;
		}
		
		// change database		
		$this->changeDatabase($this->selectedReplica);
		
		// return true
		return true;
	}

	// close database replica, by pointing to the master database
	public function closeDBReplica(){
		$this->changeDatabase('default');
		return true;
	}
	
	// @override 'find' method,
	// catch any errors that may happen, 
	public function find($type = 'first', $params = array()) {
		// try execution through replica
		try {
			return call_user_func_array('parent::find', func_get_args());
		
		// catch errors
		} catch (Exception $e) {
			$this->catchDatabaseErrors($e);
		}
	}
	
	// @override 'query' method,
	// catch any errors that may happen, 
	public function query($sql = '') {
		// try execution through replica
		try {
			return call_user_func_array('parent::query', func_get_args());
		
		// catch errors
		} catch (Exception $e) {
			$this->catchDatabaseErrors($e);
		}
	}
	
	// after every find, close database
	public function afterFind ($results, $primary = false) {
		// trigger parent after find
		call_user_func_array('parent::afterFind', func_get_args());

		// if replica, close the database
		if ($this->getDataSource()->configKeyName != 'default') {
			$this->closeDBReplica();
		}

		// return results
		return $results;
	}

	// before every save, use master database
	public function beforeSave ($options = array()) {
		// trigger parent after find
		call_user_func_array('parent::beforeSave', func_get_args());

		// if replica, close the database
		if ($this->getDataSource()->configKeyName != 'default') {
			$this->closeDBReplica();
		}

		// return true to enable saving
		return true;
	}

	// before every delete, use master database
	public function beforeDelete($cascade = true) {
		// trigger parent after find
		call_user_func_array('parent::beforeDelete', func_get_args());

		// if replica, close the database
		if ($this->getDataSource()->configKeyName != 'default') {
			$this->closeDBReplica();
		}

		// return false to allow deletion
		return true;
	}

	// trigger change database
	public function changeDatabase($datasource = 'default'){

		// set database source
		$this->useDbConfig = $datasource;
		
		// database associations
		$dbRelation = array('hasOne', 'hasMany', 'belongsTo', 'hasAndBelongsToMany');
		
		// loop through each relation
		foreach($dbRelation as $dbAssoc){
			if ($this->{$dbAssoc} && count($this->{$dbAssoc}) != 0) {
				foreach($this->{$dbAssoc} as $btModelName => $btModelData){
					$this->{$btModelName}->useDbConfig = $datasource;		
				}
			}
		}
		
		// return true
		return true;
	}
	
	// select the database replica based on count
	private function selectDBReplica() {
		$selectedDBR = $currentReplica = false;
		$target = rand(1, 100);
		
		// defined replicas
		$this->replicas = Configure::read('database.replicas');
		
		// if has no replicas
		if (!$this->replicas || !$this->memcached) {
			// set the selected replica as default
			$this->selectedReplica = 'default';
			
			// return
			return true;
		}
		
		// valid replicas
		$validReplicas = array_keys(ConnectionManager::enumConnectionObjects());
		
		// randomise replicas
		shuffle($this->replicas);
		
		// loop through the replica
		foreach ($this->replicas as $dbReplica) {
			// get if each replica has relevant info
			if (!$replicaInfo = $this->memcached->get('DB_REPLICA_' . $dbReplica)){
				$replicaInfo = array(
					'NAME' => $dbReplica,
					'ACCESS_CNT' => 1,
					'STATUS' => 'INACTIVE',
					'LAST_ERROR_DATE' => null,
					'USAGE_RATE' => 50,
					'ERROR_CNT' => 0,
					'ERROR_TRESHOLD' => 0
				);
				
				// set memcached information
				$this->memcached->set(array('key' => 'DB_REPLICA_' . $dbReplica, 'value' => $replicaInfo, 'expire' => 604800));
			}
			
			// if current replica is inactive
			if (
				$replicaInfo['STATUS'] == 'INACTIVE' || 
				$replicaInfo['STATUS'] == 'ERROR' || 
				!in_array($dbReplica, $validReplicas)
			) {
				continue;
			}
			
			// check usage rate
			if (!$replicaInfo['USAGE_RATE']) {
				continue;
			}
			
			// get by usage rate
			if ($target <= $replicaInfo['USAGE_RATE'])  {
				$this->selectedReplica = $replicaInfo['NAME'];
				$currentReplica = $replicaInfo;
				$selectedDBR = true;
				
			// deduct usage rate
			} else {
				$target -= $replicaInfo['USAGE_RATE'];
			}
		}
		
		// if nothing was selected, 
		// or selected replica information has no contennts
		// or selected replica is inactive
		// or selected replica is in 'error' state
		// fall back to master
		if (
			!$selectedDBR || 
			!$currentReplica || 
			(isset($currentReplica['STATUS']) && ($currentReplica['STATUS'] == 'INACTIVE' || $currentReplica['STATUS'] == 'ERROR'))
		) {
			// set the selected replica as default
			$this->selectedReplica = 'default';
			
			// return
			return true;
		}
		
		// increment
		$currentReplica['ACCESS_CNT']++;
		
		// set
		$this->memcached->set(array(
			'key' => 'DB_REPLICA_' . $currentReplica['NAME'], 
			'value' => $currentReplica, 
			'expire' => 604800
		));
	}
	
	// catch database errors,
	private function catchDatabaseErrors($e){
		$databaseConfig = $this->getDataSource()->configKeyName;
		
		// if master database, do nothing
		if ($databaseConfig == 'default' || !$this->memcached) {
			return true;	
		}
		
		// get replica memcached
		$replicaDB = $this->memcached->get('DB_REPLICA_' . $databaseConfig);
		$replicaIdentifiers = Configure::read('database.replica.identifiers');
		
		// stop catching request if already in "error" status
		if (isset($replicaDB['STATUS']) && $replicaDB['STATUS'] == 'ERROR') {
			return true;
		}

		// if error treshold does not exist or
		// if error treshold exists, but contains a value less than or equal to 0,
		// don't catch error
		if (
			!isset($replicaDB['ERROR_TRESHOLD']) ||
			(isset($replicaDB['ERROR_TRESHOLD']) && $replicaDB['ERROR_TRESHOLD'] <= 0)
		) {
			return true;
		}
		
		// if last error and error count exists		
		if (
			isset($replicaDB['LAST_ERROR_DATE']) && 
			isset($replicaDB['ERROR_CNT']) &&
			$replicaDB['LAST_ERROR_DATE']
		) {
			$dateDiff = strtotime(date("Y-m-d H:i:s")) - strtotime($replicaDB['LAST_ERROR_DATE']);
			
			// if more than 2 minutes
			if ($dateDiff > 120) {
				// reset error
				$replicaDB['ERROR_CNT'] = 0;
				$replicaDB['LAST_ERROR_DATE'] = null;
				
			// if has more than the set error treshold within 2 minutes
			} else if ($dateDiff >= 0 && $dateDiff <= 120 && $replicaDB['ERROR_CNT'] >= $replicaDB['ERROR_TRESHOLD']) {
				$replicaClone = $replicaDB;
				
				// disable database
				$replicaDB['STATUS'] = 'ERROR';
				
				// set immediately
				$this->memcached->set(array(
					'key' => 'DB_REPLICA_' . $databaseConfig, 
					'value' => $replicaDB, 
					'expire' => 604800
				));
				
				// if replica identifiers exist
				if (isset($replicaIdentifiers[$replicaClone['NAME']])) {
					// send reboot request to database
					$rdsUrl = "https://2n88b6wd62.execute-api.ap-northeast-1.amazonaws.com/prod/rds-rebooter";
					$postFields = new stdClass();
					$postFields->action = "reboot";
					$postFields->identifier = $replicaIdentifiers[$replicaClone['NAME']];
					
					// perform database reboot request
					$ch = curl_init($rdsUrl);
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postFields));
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/plain')); 
					$result = curl_exec($ch);
					
					// parse json data
					$result = json_decode($result);

					// use my slack library
					App::uses('mySlack', 'Lib');

					// declare mySlack instance
					$mySlack = new mySlack();
					$mySlack->channel = myTools::checkChannel('#nc-system');
					$mySlack->username = "NC - Database Status";
					$mySlack->text = "<@nc.fukutomi> <@fdc.yun> <@akahoshi.hn> <@fdc.tsuchiya> <@yamaaki.hn> <@onishi>";
					$mySlack->text .= "```";
					$mySlack->text .= $replicaClone['NAME'] . " received " . $replicaClone['ERROR_CNT'] . " errors since " . date("Y-m-d H:i:s", strtotime($replicaClone['LAST_ERROR_DATE'])) . "\n";
					$mySlack->text .= $replicaClone['NAME'] . " has been deactivated\n";

					// set http code
					$mySlack->text .= $replicaClone['NAME'] . " response status code : " . (isset($result->success) ? $result->success : 'FAILED_REQUEST');
					
					// close
					$mySlack->text .= "```";
					
					// send slack message
					$mySlack->sendSlack();
				}
				
				// return
				return true;
				
			// increment error count 
			} else {
				$replicaDB['ERROR_CNT'] = $replicaDB['ERROR_CNT'] + 1;
			}

		// catch first database error
		} else {
			$replicaDB['ERROR_CNT'] = 1;
			$replicaDB['LAST_ERROR_DATE'] = date("Y-m-d H:i:s");
		}
		
		//MARK: - declare path
		$filePath = ROOT . DS . 'user' . DS . 'tmp' . DS . 'logs' . DS . 'exception' . DS;

		//MARK: - catch path exception
		if (is_dir($filePath) && function_exists('error_log')) {
			error_log(date("Y-m-d H:i:s") . ": " . json_encode($e) . "\r\n", 3, $filePath . date("Y-m-d") . "_replica_database_exception.log");
		}

		// set immediately
		$this->memcached->set(array(
			'key' => 'DB_REPLICA_' . $databaseConfig, 
			'value' => $replicaDB, 
			'expire' => 604800
		));

		// return
		return true;
	}
	
	// add save
	function save($data = null, $validate = true, $fieldList = array()) {
		try {
			if (!isset($this->data[$this->name]) || !isset($this->data[$this->name]['id'])) {
				$data[$this->name]['created_ip'] = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "0.0.0.0";
			}
			$data[$this->name]['modified'] = date('Y-m-d H:i:s');
			$data[$this->name]['modified_ip'] = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "0.0.0.0";
			return parent::save($data, $validate, $fieldList);
		} catch (Exception $e) {
			CakeLog::write('error', $e->getMessage());
			CakeLog::write('error', isset($e->queryString)? $e->queryString : null);
		}
	}
	
	// save all at once
	public function saveAllAtOnce($data) {
		try {
			if(count($data) > 0 && !empty($data[0])) {
				$value_array = array();
				$fields = array_keys($data[0][$this->name]);
				foreach ($data as $key => $value) {
					$value_array[] = "('" . implode('\',\'', $value[$this->name]) . "')";
				}
				$sql = "INSERT INTO " . $this->table . " (" . implode(', ', $fields) . ") VALUES " . implode(',', $value_array);
				$this->query($sql, false);
				return true;
			}
			return false;
		} catch (Exception $e) {
			CakeLog::write('error', $e->getMessage());
			CakeLog::write('error', $e->queryString);
		}
	}
	
	// alphanumeric validator
	public function alphaNumeric($check) {
		$value = array_values($check);  // 配列の添字を数値添字に変換
		$value = $value[0];	 // 最初の値を取る
		return preg_match('/^[a-zA-Z0-9]+$/', $value);
	}
}
