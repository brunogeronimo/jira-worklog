<?php
	require_once __DIR__.'/settings.php';
	require_once __DIR__.'/../vendor/autoload.php';
	require_once __DIR__.'/JiraInterface.php';
	require_once __DIR__.'/Filter.php';
	define('JIRA_DEFAULT_API_URL', '/rest/api/2');
	define('BR_DATE_REGEXP', '/^([0-9]{2}\/){2}\/[0-9]{4}$/');
	header("Content-Type: application/json");

	$requestContent = file_get_contents('php://input');
	$requestContent = json_decode($requestContent);

	$jql = $requestContent->jql;
	$from = "";
	$to = "";
	$worklogUsername = "";
	
	if (isset($requestContent->from)){
		$from = $requestContent->from;
	}
	if (isset($requestContent->to)){
		$to = $requestContent->to;
	}	
	if (isset($requestContent->worklogUsername)){
		$worklogUsername = $requestContent->worklogUsername;
	}

	$jiraInterface = JiraInterface::create()
						->setUsername($requestContent->username)
						->setPassword($requestContent->password)
						->setUrl($requestContent->url);
	
	$jiraFilter = Filter::create()
					->setFrom($from)
					->setTo($to)
					->setWorklogUsername($worklogUsername);

	$statistics = array();
	$queryResult = $jiraInterface->runQuery($jql, $statistics)->filter($jiraFilter);

	$body = array();
	$body['jiras'] = $queryResult->getResultList();
	$body['jiras']['size'] = $queryResult->count();
	$body['statistics'] = $queryResult->totalize();
	$body['statistics']['jqlResult'] = $statistics['results'];

	echo (json_encode($body));
?>