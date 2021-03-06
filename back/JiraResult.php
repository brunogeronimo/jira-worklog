<?php
	require_once __DIR__.'/../vendor/autoload.php';
	require_once __DIR__.'/Filter.php';
	class JiraResult{
		private $resultList;

		public function __construct(array $resultList = []){
			$this->resultList = $resultList;
		}

		public static function create(array $resultList = []){
			$instance = new self($resultList);
			return $instance;
		}

		public function filter(Filter $filter = null){
			if ($filter === null || !$filter->hasFiltersFilled()){
				return $this;
			}
			$resultList = $this->getResultList();
			$filteredList = array();
			$savedJiras = array();
			$i = 0;
			$jiraKey = "";
			$actPos = $i;
			foreach ($resultList as $result) {
				foreach ($result['worklogs'] as $worklog) {
					if ($filter->validateWorklog($worklog)){
						$auxJira = array();
						$jiraKey = $result['key'];
						if (!isset($savedJiras[$jiraKey])){
							$filteredList[$i] = $result;
							$filteredList[$i]['worklogs'] = array();
							$savedJiras[$jiraKey] = $i++;
						}
						$actPos = $savedJiras[$jiraKey];
						$filteredList[$actPos]['worklogs'][] = $worklog;
					}
				}
			}
			$this->resultList = $filteredList;
			return $this;
		}

		public function count(){
			return count($this->resultList);
		}

		public function totalize(){
			$worklogPerUser = array();
			$jiras = array();
			$jiras['total'] = 0;
			foreach ($this->resultList as $result) {
				$jira = $result['key'];
				if (!isset($jiras[$jira])){
					$jiras[$jira] = 0;
					$jiras['total']++;
				}
				foreach ($result['worklogs'] as $worklog){
					$jiras[$jira]++;
					$user = $worklog['user'];
					if (!isset($worklogPerUser[$user])){
						$worklogPerUser[$user] = array();
						$worklogPerUser[$user]['timeSpent'] = 0;
						$worklogPerUser[$user]['total'] = 0;
					}
					if (!isset($worklogPerUser[$user][$jira])){
						$worklogPerUser[$user][$jira] = 0;
						$worklogPerUser[$user]['total']++;
					}
					$worklogPerUser[$user]['timeSpent'] += $worklog['timeSpentSeconds'];
					$worklogPerUser[$user][$jira]++;
				}
			}
			return array(
				"worklogPerUser" => $worklogPerUser,
				"jiras" => $jiras
			);
		}

		public function getResultList(){
			return $this->resultList;
		}
	}