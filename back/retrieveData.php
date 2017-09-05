<?php
	require __DIR__.'/../vendor/autoload.php';
	define('JIRA_DEFAULT_API_URL', '/rest/api/2');
	header("Content-Type: application/json");

	$requestContent = file_get_contents('php://input');
	$requestContent = json_decode($requestContent);

	$username = $requestContent->username;
	$password = $requestContent->password;
	$url = $requestContent->url;
	$jql = $requestContent->jql;

	$jiras = array();
	$timePerUser = array();
	$timePerUser['total'] = 0;
	$timePerUser['averageTimePerUser'] = 0;
	$timePerUser['averageTimePerJira'] = 0;
	$timePerUser['users'] = array();



	$client = new GuzzleHttp\Client();
	try{
		$res = $client->get(
				($url.JIRA_DEFAULT_API_URL.'/search'),
				[
					'query' => [
						'jql' => $jql,
						'startAt' => 0,
						'maxResults' => 3500
					],
					'auth' => [$username, $password],
					'debug' => false
					
				]
			);
	}catch(\GuzzleHttp\Exception\ClientException $e){
		die(json_encode(array("status" => "ERR_USERNAME_PASSWORD")));
	}
	
	$content = json_decode($res->getBody());
	$jiraLinks = array();
	foreach ($content->issues as $issue) {
		$jiraLinks[] = $issue->self;
	}



	
	foreach ($jiraLinks as $link) {
		$resIssue = json_decode($client->get($link,
					[
						'auth' => [$username, $password]
					]
				)->getBody());
		$resIssueAux = $resIssue->fields;
		if ($resIssueAux->worklog->total <> 0){
			$auxJira = array();
			$auxJira['key'] = $resIssue->key;
			$auxJira['summary'] = $resIssueAux->summary;
			$auxJira['priority'] = $resIssueAux->priority->name;
			$auxJira['type'] = $resIssueAux->issuetype->name;
			$auxJira['status'] = $resIssueAux->status->name;
			$sprintAux = array();
			$max = count($resIssueAux->customfield_10991) - 1;
			preg_match('/(name=)([a-zA-Z0-9 ]*)/', $resIssueAux->customfield_10991[$max], $sprintAux);
			$auxJira['sprint'] = $sprintAux[2];
			$auxJira['worklogs'] = array();
			foreach ($resIssueAux->worklog->worklogs as $worklog) {
				$created = new DateTime($worklog->started);
				$auxJira['worklogs'][] = array(
						"author" => ($worklog->author->displayName . " [{$worklog->author->name}]"),
						"created" => $created->format("d/m/Y"),
						"timeSpent" => $worklog->timeSpent,
						"timeSpentSeconds" => $worklog->timeSpentSeconds
					);
				if (!isset($timePerUser['users'][$worklog->author->name])){
					$timePerUser['users'][$worklog->author->name] = 0;
				}
				$timePerUser['users'][$worklog->author->name] += $worklog->timeSpentSeconds;
				$timePerUser['total'] += $worklog->timeSpentSeconds;
			}
			$jiras[] = $auxJira;
		}
	}
	$jiras['size'] = count($jiras);

	$totalJiras = count($jiras);
	$totalUsers = count($timePerUser['users']);
	if ($timePerUser['total'] > 0){
		$timePerUser['averageTimePerUser'] = $timePerUser['total'] / $totalUsers;
		$timePerUser['averageTimePerJira'] = $timePerUser['total'] / $totalJiras;
	}

	$body = array();
	$body['jiras'] = $jiras;
	$body['timePerUser'] = $timePerUser;

	echo (json_encode($body));
?>