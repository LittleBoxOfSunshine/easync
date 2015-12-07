$('#register_button').on('click', function() {
	console.log("registering");
	console.log($('#reg_email').val(), $('#reg_pwd').val());
});

$('#login_button').on('click', function() {
	console.log("logging in");
	console.log($('#login_email').val(), $('#login_pwd').val());
	if ($('#login_email').val() === "test@smu.edu" && $('#login_pwd').val()=== "pass") {
		$("#loginModal").modal("hide");
		$('#loginModal .error').clear();

	} else {
		$('#loginModal .error').text("Error signing in!");
	}
	
});