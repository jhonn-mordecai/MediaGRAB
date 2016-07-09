<?php 
	
	class GuardianAPI extends APILink {
		
		public function __construct($api_key) {
			$this->staticparams['api-key'] = $api_key;
		}
		
		public function search($query, $from_date, $to_date) {
			$this->endpoint = 'http://content.guardianapis.com/search';
			$this->params = array(
				'q' => $query,
				'from-date' => $from_date,
				'to-date' => $to_date,
				//'order-by' => $order_by
			);
			$this->makeRequest();
			//echo '<pre>';
			//print_r($this->request);
			//echo '</pre>';
			//exit;
		}
				
	}
	
	
?>