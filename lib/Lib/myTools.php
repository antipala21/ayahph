<?php

$vendors = array(
	'UserAgent',
	'DetectorInterface',
	'Browser',
	'BrowserDetector'
);

if($vendors){
	foreach($vendors as $vendor){
		$file = CAKE_CORE_INCLUDE_PATH.'/Vendor/sinergi/browser-detector/src/'.$vendor.'.php';
		if(is_file($file)){ require_once($file); }
	}
}
use Sinergi\BrowserDetector\Browser;
class myTools{

	public static function display($var) {
		echo '<pre>';
		print_r($var);
		echo '</pre>';
	}

	public static function outputSqlLogs($model){
		$array = $model->getDataSource()->getLog();
		foreach ($array['log'] as $log) {
			myTools::display($log['query']);
		}
	}

	public static function getCurrentBrowser($userAgent = null) {
		if (is_null($userAgent)) {
			return $userAgent;
		}

		$browser = new Browser($userAgent);
		return $browser->getName();
	}

	public static function getDevice() {
		App::import('Vendor','MobileDetect');
		$MobileDetect = new MobileDetect();
		if (!$MobileDetect->isMobile()) {
			return 1; //PC
		} else {
			if ($MobileDetect->isAndroidOS()) {
				if ($MobileDetect->isKindle()) {
					return 4; //Kindle //NC-3699
				}
				return 2; //Android
			} elseif($MobileDetect->isiOS()) {
				return 3; //iOS
			} else {
				return 0; //unknown
			}
		}
	}

	public static function getUrl() {
		if (isset($_SERVER['HTTPS']) &&
			($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
			isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
			$_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
			$protocol = 'https://';
		}
		else {
			$protocol = 'http://';
		}
		return $protocol.$_SERVER['HTTP_HOST'];
	}

	public static function getUrlProtocol() {
		if (isset($_SERVER['HTTPS']) &&
			($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
			isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
			$_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
			$protocol = 'https://';
		} else {
			$protocol = 'http://';
		}
		return $protocol;
	}

	/**
	* @param $params - array
	* @return list of number upto $n - array
	*/
	public function getOptions ($params = array()) {
		$max = 30;
		if ($params['nmax'] < 30 && $params['nmax'] > 0) {
			$max = $params['nmax'];
		} elseif ($params['nmax'] == 0) {
			$max = 1;
		}

		$result = array();
		for ($i=0; $i <= $max ; $i++) { 
			$result[] = $i;
		}
		unset($result[0]);
		return $result;
	}

	public static function getProfileImgSrc ($filename) {
		if (empty($filename)) {
			return '/images/picture.jpg';
		}
		if (file_exists(ROOT . '/user/webroot/images/' . $filename)) {
			return '/images/' . $filename;
		}
		return '/images/picture.jpg';
	}

	/**
		check server host
	*/
	public static function checkHost () {
		
		if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === 'localhost:8012') {
			return '/ayahph';
		}
		return '';
	}


}
