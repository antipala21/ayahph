<?php
App::uses('AppController', 'Controller');
App::uses('ApiCommonController', 'Controller');
App::import('Helper', 'Xml');
App::uses('ApiCommonController', 'Controller');
class PortController extends AppController {

	public $uses = array();

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('show'));
	}

	public function show(){
		$signalHost = '';
		$signalPort = '';
		$externalSiteArray = $internalSiteArray = array();

		//initialize Api validation controller
		$Validate = new ApiCommonController();
		$this->autoRender = false;

		$data = @json_decode($this->request->input(), true);

		if ($this->request->is('post')) {
			// this will retrieve and read the version xml file	in the directory
			if (file_exists('../port.xml')) {
				$filexml = file_get_contents('../port.xml');
				$xml = simplexml_load_string($filexml); // load xml to simplexmlelement object
				if (empty($xml))  return false;
			} else {
				return false;
			}

			// set port and hsot
			$signalHost = (string) $xml->{'signal'}->{'signalHost'};
			$signalPort = (int) $xml->{'signal'}->{'signalPort'};
			$paymentAxes = "axes-payment";
			
			// set default textbook
			$defaultTextbook = (int) $xml->{'defaultTextbook'}->{'id'};
			$defaultTextbook = ($defaultTextbook) ? $defaultTextbook : 438;
			
			// check if payment configuration exists
			if (file_exists('../externalsite.xml')) {
				$externalsXml = file_get_contents('../externalsite.xml');
				$exXml = simplexml_load_string($externalsXml); // load xml to simplexmlelement object
				$externalUrls = $exXml->{'external-urls'}->{'site-url'};
				
				// fetch external urls
				if ($externalUrls) {
					foreach ($externalUrls as $url) {
						$externalSiteArray[] = array(
							'site-name' => (string) $url->{'site-name'},
							'site-domain' => (string) $url->{'site-domain'}
						);
					}
				}
			}

			// check if internal xml file exist
			if (file_exists('../internalsite.xml')) {
				$internalsXml = file_get_contents('../internalsite.xml');
				$inXml = simplexml_load_string($internalsXml); // load xml to simplexmlelement object
				$internalUrls = $inXml->{'internal-urls'}->{'site-url'};
				
				// fetch external urls
				if ($internalUrls) {
					foreach ($internalUrls as $url) {
						$internalSiteArray[] = array(
							'site-name' => (string) $url->{'site-name'},
							'site-domain' => (string) $url->{'site-domain'}
						);
					}
				}
			}
			
			// return as array
			$json['signalHost'] =  $signalHost;
			$json['signalPort'] =  $signalPort;
			$json['defaultTextbook'] =  $defaultTextbook;
			$json['externalSites'] = $externalSiteArray;
			$json['internalSites'] = $internalSiteArray;

			
			// return data
			echo json_encode($json);
		}
	}
}
