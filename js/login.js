$('#register_button').on('click', function() {
	console.log("registering");
	console.log($('#reg_email').val(), $('#reg_pwd').val());
});

$('#login_button').on('click', function() {
	console.log("logging in");
	console.log($('#login_email').val(), $('#login_pwd').val());
});