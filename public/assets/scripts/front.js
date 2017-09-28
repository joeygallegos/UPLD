$loginForm = $('form#login');

$loginForm.on('submit', function(event) {

	// Stop it from submitting the lame way
	event.preventDefault();

	// Vars
	$username = $('form#login input[type=text][name=username]').val()
	$password = $('form#login input[type=password][name=password]').val();
	
	// Check for actual input
	var bothFilled = $username.length > 0 && $password.length > 0
	if (!bothFilled) {
		swal('Field error', 'Login fields can\'t be empty, they must both have values', 'error');
		return;
	}

	// Validation check
	var matches = /^[a-zA-Z\-]+$/.test($username);
	if (!matches) {
		swal('Field error', 'Username must be alphabetical only and can\'t have spaces', 'error');
		return;
	}

	// Bundle data into array
	var formData = {
		'action': 'login',
		'username': $username.trim(),
		'password': $password.trim()
	}

	// Send the array of info off to the server for checks
	$.ajax({
		url: $(this).attr(‘action’),
		type: 'POST',
		dataType: 'json',
		data: formData,
		cache: false,
		success: function(data) {
			// If we can log them in, do it here
			if (data.response.code !== 1) {
				swal("We couldn't log you in!", data.response.message, "error");
			}
			else {
				window.location.reload(false);
			}
		},
		error: function (request, status, error) {
			// Make a better error handler right here
			swal("Something stupid happened", "There was a problem while trying to log you in..", "error");
		}
	});
})

$(document).ready(function() {
	
})

