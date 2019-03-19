/** global: yotpSettings */
window.addEventListener('DOMContentLoaded', function() {
	var $         = document.getElementById.bind(document);
	var submit    = $('wp-submit');
	var container = $('yotp-block');
	var input     = $('yotp');

	submit.addEventListener('click', yotpCallback);
	container.setAttribute('hidden', '');
	input.setAttribute('disabled', '');
	input.setAttribute('required', '');

	function yotpCallback(e)
	{
		e.preventDefault();
		e.stopPropagation();
		e.stopImmediatePropagation();

		var username = $('user_login').value;
		if (!username) {
			return;
		}

		submit.removeEventListener('click', yotpCallback);

		var req = new XMLHttpRequest();
		req.addEventListener('load', function() {
			if (null === this.response || this.status !== 200 || !this.response.status) {
				submit.click();
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
