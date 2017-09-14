var url, username, password, base64Login;
const JIRA_API_URL = '/rest/api/2';
($(document).ready(function(){
	if (navigator.userAgent.search("Firefox") >= 0){
		$('.date-picker').datepicker({ dateFormat: 'dd/mm/yy'}); 
	}
	

	$('.loginForm').submit(function(e){
		e.preventDefault();
		var goOn = true;
		$(this).find('[type="text"], [type="password"]').each(function(){
			if ($(this).val() === ''){
				alert($(this).attr('errorname') + ' is a required field');
				$(this).focus();
				goOn = false;
				returnfalse;
			}
		})
		if (!goOn){
			return;
		}
		username = $(this).find('[name="username"]').val();
		password = $(this).find('[name="password"]').val();
		url = $(this).find('[name="url"]').val();
		$(this).hide();
		$('.search').show();
	});

	$('.search').submit(function(e){
		e.preventDefault();
		var data = {};
		data.jql = $(this).find('[name="jql"]').val();
		data.from = $(this).find('[name="from"]').val();
		data.to = $(this).find('[name="to"]').val();
		data.worklogUsername = $(this).find('[name="worklogUsername"]').val();
		searchInfo(data);
		$('.status').show();
	});
}))

function searchInfo(data){
	$(".results").html("");
	$('.status').hide();
	$(".search :input").prop('disabled', true);
	data.username = username;
	data.password = password;
	data.url = url;

	$.ajax({
		method: 'POST',
		url: "/back/retrieveData.php",
		contentType: 'application/json',
		dataType: 'json',
		data: JSON.stringify(data),
		success:function(data){
			$(".status").hide();
			if (data.status !== undefined){
				handleStatus(data.status);
				return;
			}

			makeTable(data.jiras);
			totalInfo(data.statistics);
			$(".search :input").prop('disabled', false);
		},
		error: function(data, textStatus){
			console.log(textStatus);
			console.log(data);
			handleStatus("ERR_RUNNING_QUERY");
		}
	});
}

function handleStatus(status){
	switch(status){
		case "ERR_USERNAME_PASSWORD":
			$(".status").html("Usuário e/ou senha incorreto(s). Faça login <a href='"+url+"' target='_blank'>aqui</a> antes de continuar.");
		break;
		case "ERR_RUNNING_QUERY":
			$(".status").html("Ocorreu um erro ao executar a query");
		break;
	}
	$(".search :input").prop('disabled', false);
	$(".loginForm").show();
	$(".search, .results").hide();
}

function makeTable(jiras){
	var selector = $(".results");
	var html = "";
	var i = 0;
	if (jiras.size === 0){
		$(selector).html("Nenhum resultado encontrado.");
		$(".search :input").prop('disabled', false);
		return false;
	}
	html += "<table style='width: 100%'>";
	html += "<tr>";
	html += 	"<td></td>";
	html += 	"<td>JIRA</td>";
	html += 	"<td>Prioridade</td>";
	html += 	"<td>Tipo</td>";
	html += 	"<td>Status</td>";
	html += 	"<td>Sprint</td>";
	html += 	"<td>Data do Worklog</td>";
	html += 	"<td>Worklog</td>";
	html += 	"<td>Responsável</td>";
	html += "</tr>"
	$.each(jiras, function(index, obj){
		console.log(obj);
		console.log("---");
		$.each(obj.worklogs, function(index, worklog){
			console.log(worklog);
		 	html += "<tr>";
		 	html += 	"<td>" + ++i + "</td>";
		 	html += 	"<td>" + obj.key + "</td>";
		 	html += 	"<td>" + obj.priority + "</td>";
		 	html += 	"<td>" + obj.type + "</td>";
		 	html += 	"<td>" + obj.status + "</td>";
		 	html += 	"<td>" + obj.sprint + "</td>";
		 	html += 	"<td>" + worklog.created + "</td>";
		 	html += 	"<td>" + secondsToHms(worklog.timeSpentSeconds) + "</td>";
		 	html += 	"<td>" + worklog.author + "</td>";
		 	html += "</tr>";
		});
	});
	html += "</table>";
	html += "<div>" + i + " linha(s) retornada(s)</div>";
	$(selector).html(html);
	$(selector).show();
}

function secondsToHms(d) {
    d = Number(d);

    var h = Math.floor(d / 3600);
    var m = Math.floor(d % 3600 / 60);
    var s = Math.floor(d % 3600 % 60);

    return ('0' + h).slice(-2) + ":" + ('0' + m).slice(-2) + ":" + ('0' + s).slice(-2);
}

function totalInfo(totals){
	var selector = $(".results");
	var html = $(".results").html();
	var totalizer = 0;
	html += "<br/><br/><br/><br/><br/><br/>";
	html += "<table>";
	html += 	"<tr><td>Total de JIRAs retornados: </td><td>" + totals.jiras.total + "</td></tr>"
	html += "</table>";
	html += "<br/><br/><br/>";
	html += "JIRAs por Usuário";
	html += "<table>";
	html += 	"<tr>";
	html += 		"<td>Usuário</td>";
	html += 		"<td>JIRAs</td>";
	html += 	"</tr>";
	$.each(totals.worklogPerUser, function(index, jiraList){
		totalizer += jiraList.total;
		html += 	"<tr>";
		html += 		"<td>" + index + "</td>";
		html += 		"<td>" + jiraList.total + "</td>";
		html += 	"</tr>";
	});
	html += 	"<tr>";
	html += 		"<td>Total</td>";
	html += 		"<td>" + totalizer + "</td>";
	html += 	"</tr>";
	html += "</table>";

	html += "</div>";
	$(selector).html(html);
}