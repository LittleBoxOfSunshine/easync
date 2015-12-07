$('#register_button').on('click', function() {
	console.log("registering");
	$('#reg_err, #reg_auth_err').remove();
	var email = $('#reg_email').val();
	var pass = $('#reg_pwd').val();
	var firstname = $('#reg_fname').val();
	var lastname = $('#reg_lname').val();

	if (email === '' || pass === '' || firstname === '' || lastname === '') {
		$('#registerModal .modal-body').append('<div id="reg_err" style="color:red;">Please fill out all of the fields</div>');
	}
	var payload = {
		'email' : email,
		'password' : pass,
		'firstname' : firstname,
		'lastname' : lastname
	};
	$.ajax({
      type: 'POST',
      url: 'api/v1.0/User/register',
      data: payload,
      success: function (data) {
        alert('Register successful!');
        $("#registerModal").modal("hide");
      },
      error: function (error) {
      	$('#registerModal .modal-body').append('<div id="reg_auth_err" style="color:red;">Unable to register account</div>');
      }
    });
});

$('#login_button').on('click', function() {
	console.log("logging in");
	$('#auth_err, #login_err').remove();
	var email = $('#login_email').val();
	var pass = $('#login_pwd').val();
	if (email === '' || pass === '') {
		$('#loginModal .modal-body').append('<div id="login_err" style="color:red;">Please fill out all of the fields</div>');
	}
	var payload = {
		'email' : email,
		'password' : pass
	};
	$.ajax({
      type: 'POST',
      url: 'api/v1.0/User/login',
      data: payload,
      success: function (data) {
        alert('Login successful!');
        $("#loginModal").modal("hide");
      },
      error: function (data) {
      	$('#loginModal .modal-body').append('<div id="auth_err" style="color:red;">Incorrect password or email provided</div>');
      }
    });
	
});

//get rid of the errors once the modals are closed
$('#loginModal, #registerModal').on('hidden.bs.modal', function() {
	$('#reg_err, #auth_err, #login_err, #reg_auth_err').remove();
	$(this).find('input').val('');
});