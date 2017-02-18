$(document).ready(function() {
    // hide the forms when page is ready
    $('#login').show();
    $('#signup').hide();

    $('#login-button').click(function(){ 
        $('#login').show();
		$('#signup').hide(); 
    });
    $('#signup-button').click(function(){ 
		$('#login').hide();
        $('#signup').show(); 
    });
});