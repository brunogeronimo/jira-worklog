<?php
	require_once __DIR__.'/../vendor/autoload.php';
	require_once __DIR__.'/Filter.php';
	require_once __DIR__.'/JiraResult.php';
	class JiraInterface{

		private $username;
		private $password;
		private $url;
		private $client;
		private $debug = false;

		const JIRA_DEFAULT_API_URL = '/rest/api/2';

		public function __construct($config = []){
			if (!is_array($config)){
				throw new Exception("Config variable must be an array");
			}
			if (isset($config['username'])){
				$this->username = $username;
			}
			if (isset($config['password'])){
				$this->password = $password;
			}
			if (isset($config['url'])){
				$this->url = $url;
			}
			if (isset($config['debug'])){
				$this->debug = $debug;
			}
			$this->prepare();
			return $this;
		}

		public static function create(){
			$instance = new self();
			return $instance;
		}

		public function prepare($force = false){
			$this->instantiateClient($force);
		}

		public function instantiateClient($force = false){
			if ($force || $this->client === null){
				$this->client = new GuzzleHttp\Client();
			}
		}

		public function setUsername($username){
			$this->prepare();
			$this->username = $username;
			return $this;
		}

		public function setPassword($password){
			$this->prepare();
			$this->password = $password;
			return $this;
		}

		public function setUrl($url){
			$this->prepare();
			$this->url = $url;
			return $this;
		}

		public function setDebug($debug = false){
			if (!is_bool($debug)){
				throw new Exception("Debug variable must be a boolean");
			}
			$this->debug = $debug;
			return $this;
		}

		private function getClient(){
			$this->instantiateClient();
			return $this->client;
		}

		private function getAuthentication(){
			return [$this->username, $this->password];
		}

		private function retrieveJiraUrlByJql($jql = ""){
			if ($jql == ""){
				throw new Exception("JQL must be set");
			}
			$jiraLinks = array();
			try{
				$res = $this->getClient()->get(
							($this->url . self::JIRA_DEFAULT_API_URL . '/search'),
							[
								'query' => [
									'jql' => $jql,
									'startAt' => 0,
									'maxResults' => 3500
								],
								'auth' => $this->getAuthentication(),
								'debug' => $this->debug
							]
						);
				$content = json_decode($res->getBody());
				
				foreach ($content->issues as $issue) {
					$jiraLinks[] = $issue->self;
				}

			}catch(\GuzzleHttp\Exception\ClientException $e){
				return array("status" => "ERR_USERNAME_PASSWORD");
			}

			return $jiraLinks;
		}

		private function formatJiraInformation($jira = null){
			if ($jira === null){
				throw new Exception("A jira must be set");
			}
			$resIssueAux = $jira->fields;
			$formattedJira = null;
			if ($resIssueAux->worklog->total <> 0){
				$formattedJira = array();
				$formattedJira['key'] = $jira->key;
				$formattedJira['summary'] = $resIssueAux->summary;
				$formattedJira['priority'] = $resIssueAux->priority->name;
				$formattedJira['type'] = $resIssueAux->issuetype->name;
				$formattedJira['status'] = $resIssueAux->status->name;
				$sprintAux = array();
				$max = count($resIssueAux->customfield_10991) - 1;
				preg_match('/(name=)([a-zA-Z0-9 ]*)/', $resIssueAux->customfield_10991[$max], $sprintAux);
				$formattedJira['sprint'] = $sprintAux[2];
				$formattedJira['worklogs'] = array();
				foreach ($resIssueAux->worklog->worklogs as $worklog) {
					$created = new DateTime($worklog->started);
					$formattedJira['worklogs'][] = array(
							"author" => "{$worklog->author->displayName} [{$worklog->author->name}]",
							"user" => $worklog->author->name,
							"created" => $created->format("d/m/Y"),
							"timeSpent" => $worklog->timeSpent,
							"timeSpentSeconds" => $worklog->timeSpentSeconds
						);
				}
			}

			return $formattedJira;
		}

		private function retrieveJiraInformation($links = []){
			if (!is_array($links)){
				throw new Exception("Links variable must be an array");
			}
			$jiras = array();
			foreach ($links as $link) {
				$resIssue = json_decode($this->getClient()->get($link,
								[
									'auth' => $this->getAuthentication()
								]
							)->getBody());
				$formattedJira = $this->formatJiraInformation($resIssue);
				if ($formattedJira !== null){
					$jiras[] = $formattedJira;
				}
			}
			return JiraResult::create($jiras);
		}

		public function runQuery($jql = '', &$statistics = null){
			if ($statistics !== null && !is_array($statistics)){
				throw new Exception("If statistics variable is set, it must be an array");
			}
			$urlList = $this->retrieveJiraUrlByJql($jql);
			if ($statistics !== null){
				$statistics['results'] = count($urlList);
			}
			return $this->retrieveJiraInformation($urlList);
		}


	}