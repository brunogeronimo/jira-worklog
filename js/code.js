var url, username, password, base64Login;
const JIRA_API_URL = '/rest/api/2';
($(document).ready(function(){
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
		searchInfo($(this).find('[name="jql"]').val());
		$('.status').show();
	});
}))

function searchInfo(jql){
	$(".results").html("");
	$('.status').hide();
	$(".search :input").prop('disabled', true);

	$.ajax({
		method: 'POST',
		url: "/back/retrieveData.php",
		contentType: 'application/json',
		dataType: 'json',
		data: JSON.stringify({
					'jql': jql,
					'url': url,
					'username': username,
					'password': password
				}),
		success:function(data){
			if (data.status !== undefined){
				handleStatus(data.status);
				return;
			}

			makeTable(data.jiras);
			//totalInfo(data.timePerUser);
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
	if (jiras.size === 0){
		$(selector).html("Nenhum resultado encontrado.");
		$(".search :input").prop('disabled', false);
		return;
	}
	html += "<table style='width: 100%'>";
	html += "<tr>";
	html += 	"<td>Key</td>";
	html += 	"<td>Priority</td>";
	html += 	"<td>Type</td>";
	html += 	"<td>Status</td>";
	html += 	"<td>Sprint</td>";
	html += 	"<td>Worklog</td>";
	html += 	"<td>Usuário</td>";
	html += 	"<td>Data</td>";
	html += "</tr>"
	$.each(jiras, function(index, obj){
		console.log(obj);
		console.log("---");
		$.each(obj.worklogs, function(index, worklog){
			console.log(worklog);
		 	html += "<tr>";
		 	html += 	"<td>" + obj.key + "</td>";
		 	html += 	"<td>" + obj.priority + "</td>";
		 	html += 	"<td>" + obj.type + "</td>";
		 	html += 	"<td>" + obj.status + "</td>";
		 	html += 	"<td>" + obj.sprint + "</td>";
		 	html += 	"<td>" + secondsToHms(worklog.timeSpentSeconds) + "</td>";
		 	html += 	"<td>" + worklog.author + "</td>";
		 	html += 	"<td>" + worklog.created + "</td>";
		 	html += "</tr>";
		});
	});
	html += "</table>";
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
	html += "<br/><br/><br/><br/><br/><br/><table>";
		html += "<tr>";
		html += 	"<td>Total de horas neste sprint:</td>";
		html +=		"<td>" + secondsToHms(totals.total) + "</td>";
		html += "</tr>";
		html += "<tr>";
		html += 	"<td>Total médio de horas por pessoa:</td>"
		html += 	"<td>" + secondsToHms(totals.averageTimePerUser) + "</td>";
		html += "</tr>";
		html += "<tr>";
		html += 	"<td>Total médio de horas por JIRA:</td>";
		html += 	"<td>" + secondsToHms(totals.averageTimePerJira) + "</td>";
		html += "</tr>";
	html += "</table><br/><br/><br/><br/><br/><br/>";
	html += "<div><label>Horas por usuário</label>";
	html += "<table>";
	html += 	"<tr>";
	html += 		"<td>Usuário</td>";
	html += 		"<td>Total de Horas</td>";
	html += 	"</tr>";

	$.each(totals.users, function(index, val){
		html += "<tr>";
		html += 	"<td>" + index + "</td>";
		html += 	"<td>" + secondsToHms(val) + "</td>";
		html += "</tr>";
	});
	html += "</table>";
	html += "</div>";
	$(selector).html(html);
}