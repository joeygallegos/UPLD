$loginForm = $('form#login');
$loginForm.on('submit', function(event) {
	event.preventDefault();

	// form input
	$username = $('form#login input[type=text][name=username]').val()
	$password = $('form#login input[type=password][name=password]').val();
	
	// length checks
	var bothFilled = $username.length > 0 && $password.length > 0
	if (!bothFilled) {
		swal('Field error', 'Login fields can\'t be empty, they must both have values', 'error');
		return false;
	}

	// alphanumerical check
	var matches = /^[a-zA-Z\-]+$/.test($username);
	if (!matches) {
		swal('Field error', 'Username must be alphabetical only and can\'t have spaces', 'error');
		return false;
	}

	// data to array
	var formData = {
		'action': 'login',
		'username': $username.trim(),
		'password': $password
	}

	// push data to server
	$.ajax({
		url: $(this).attr('action'),
		type: $(this).attr('method'),
		dataType: 'json',
		data: formData,
		cache: false,
		success: function(data) {
			console.log(data);

			if (!data.response.responseSuccess) {
				swal("We couldn't log you in!", data.response.message, "error");
			}
			else {
				// reload once authed
				swal("Welcome back!", data.response.message, "success");
				window.location.reload(false);
			}
		},
		error: function (request, status, error) {
			swal("Something stupid happened", "There was a problem while trying to log you in..", "error");
		}
	});
});

$(document).ready(function() {
	
});