<?php

class NYTimesAPI extends APILink {

	public function __construct($apikey) {
		$this->staticparams['apikey'] = $apikey;
	}

	public function search($query, $begin_date, $end_date, $sort = 'newest') {
		$this->endpoint = 'http://api.nytimes.com/svc/search/v2/articlesearch.json';
		$this->params = array(
			'q' => $query,
			'begin_date' => $begin_date,
			'end_date' => $end_date,
			'sort' => $sort
		);
		$this->makeRequest();
	}

}

?>