<?php

abstract class APILink {

	protected $errors = array(); // curl errors
	protected $method = 'get'; // get or post
	protected $format = 'json'; // Response format. json or xml
	protected $connecttimeout = 10; // How many seconds to wait for successful connection to resource before bailing
	protected $timeout = 20; // How many seconds to wait for operation to complete before bailing
	protected $staticparams = array(); // Params to include in all requests. Should be set in the constructor of the extending class
	protected $endpoint = NULL; // The endpoint for the current request. Should be set in the specific API methods within the extending calss
	protected $params = array(); // The params for this request
	protected $request = NULL; // The constructed request url
	protected $success = NULL; // boolean for the curl operation status
	protected $response = NULL; // The response from the request

	public function __get($property) {
        return $this->$property;
	}
	
	protected function makeRequest() {
		list($this->success, $this->response) = array(NULL, NULL); // Allow for multiple calls on this object
		$ch = curl_init(); // Initiate curl session
		if ($this->method == 'get') { // We are performing a get request
			$this->request = $this->endpoint.'?'; // Start building the request url with the endpoint
			foreach ($this->staticparams as $key => $value)
				$this->request .= $key.'='.urlencode($value).'&'; // Add the static params to the request
			foreach ($this->params as $key => $value)
				$this->request .= $key.'='.urlencode($value).'&'; // Add the specific params for this request
			$this->request = trim($this->request, '&?'); // Remove a possible trailing char we don't want
			curl_setopt_array($ch, array( // Setup some curl options
				CURLOPT_URL => $this->request,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_CONNECTTIMEOUT => $this->connecttimeout,
				CURLOPT_TIMEOUT => $this->timeout
			));
		} else { // We are performing a post request
			$this->request = $this->endpoint; // The endpoint to post to
			curl_setopt_array($ch, array( // Setup some curl options
				CURLOPT_URL => $this->request,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_CONNECTTIMEOUT => $this->connecttimeout,
				CURLOPT_TIMEOUT => $this->timeout,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => array_merge($this->staticparams, $this->params)
			));
		}
		$this->response = curl_exec($ch); // Perform the curl operation
		if (curl_errno($ch)) { // The curl operation failed
			$errors[] = curl_error($ch); // Store curl errors
		} else { // Curl operation succeeded
			$this->success = curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200 ? true : false; // The status of the reponse
			if ($this->success && $this->method == 'get') {
				if ($this->format == 'json')
					$this->response = json_decode($this->response, true); // Store nice json
				elseif ($this->format == 'xml')
					$this->response = $this->makeXML($this->response); // Setup our xml string
			}
		}
		list($this->endpoint, $this->params) = array(NULL, array()); // Prepare for another call
		curl_close($ch); // Close the curl connection
	}
	
	protected function makeXML($xml) {
		libxml_clear_errors();
		libxml_use_internal_errors(true); // Disable standard libxml errors and enable user error handling
		$doc = simplexml_load_string($xml); // Load the XML into the dom document
		if ($doc === false) { // XML was not loaded
			foreach (libxml_get_errors() as $error)
				$this->errors[] = 'XML Error #'.$error->code.' on line '.$error->line.': '.trim($error->message);
		}
		return $doc;
	}
	
}

?>