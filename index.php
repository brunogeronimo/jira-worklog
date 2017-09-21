<?php require_once(__DIR__."/back/settings.php"); ?>
<!DOCTYPE html>
<html>
<head>
	<title>JIRA Worklog</title>
	<script src="js/jquery.min.js"></script>
	<script src="js/jquery.min.js"></script>
	<script src="js/jquery-ui/jquery-ui.min.js"></script>
	<script src="js/code.js"></script>
	<link rel="stylesheet" type="text/css" href="css/site.css">
	<link rel="stylesheet" type="text/css" href="js/jquery-ui/jquery-ui.min.css">
</head>
<body>
	<form class="loginForm">
		<label>Os dados abaixo serão excluídos assim que esta aba for fechada.</label>
		<div>
			<label>URL do JIRA:</label>
			<input type="text" name="url" errorname="URL" value="<?=DEFAULT_JIRA_URL?>">
		</div>
		<div>
			<label>Usuário:</label>
			<input type="text" name="username" errorname="Username">
			<label>Senha:</label>
			<input type="password" name="password" errorname="Password">
		</div>
		<div>
			<input type="submit" value="Login">
		</div>
	</form>
	<form class="search hide">
		<div>
			<label>Digite a query JQL:</label>
			<input type="text" name="jql" placeholder="Ex.: Sprint = 'Sprint 8'" value="Sprint = 'Sprint 8'">
		</div>
		<div>
			<label>Worklogs</label>
			<label>de</label>
			<input type="date" name="from" class="date-picker">
			<label>até</label>
			<input type="date" name="to" class="date-picker">
		</div>
		<div>
			<label>Feitos pelo usuário: </label>
			<input type="text" name="worklogUsername">
		</div>
		<input type="submit" value="Run search">
	</form>
	<div class="status hide">
		<label>Executando query...</label>
	</div>
	<div class="results hide"></div>
</body>
</html>