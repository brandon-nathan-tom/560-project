$( document ).ready(function() {
	$('#account-form').on('submit', validateAccount);
});

function validateAccount(event)
{
	let errorMsgs = [];
	let acctForm = event.target;
	console.log(event.target);
	if(acctForm['password'].value !== acctForm['confirmpassword'].value)
	{
		errorMsgs.push('Passwords do not match');
	}
	console.log(errorMsgs);
	if(errorMsgs.length === 0)
	{
		return true;
	}
	event.preventDefault();
	
	let divOutput = $('#validation-output');
	divOutput.empty();
	for(msg of errorMsgs)
	{
		divOutput.append($('<p></p>')
							.addClass('error')
							.text(msg));
	}
	return false;
}