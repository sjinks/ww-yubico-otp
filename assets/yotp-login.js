/** global: yotpSettings */
window.addEventListener('DOMContentLoaded', function() {
	var $         = document.getElementById.bind(document);
	var form      = $('loginform');
	var container = $('yotp-block');
	var input     = $('yotp');

	form.addEventListener('submit', yotpCallback);
	container.setAttribute('hidden', '');
	input.setAttribute('disabled', '');
	input.setAttribute('required', '');

	function yotpCallback(e)
	{
		e.preventDefault();
		form.removeEventListener('submit', yotpCallback);

		var username = $('user_login').value;
		if (!username) {
			return true;
		}

		var req = new XMLHttpRequest();
		req.addEventListener('load', function() {
			if (null === this.response || this.status !== 200 || !this.response.status) {
				form.submit();
				return;
			}

			input.removeAttribute('disabled');
			container.removeAttribute('hidden');
			input.focus();
		});

		req.open('POST', yotpSettings.ajaxurl);
		req.responseType = 'json';
		req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		req.send('action=yotp-check&l=' + encodeURIComponent(username));
		return false;
	}
});
