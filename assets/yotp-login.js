/** global: yotpSettings */
(function(){
	function callback() {
		var $         = document.getElementById.bind(document);
		var submit    = $('wp-submit');
		var container = $('yotp-block');
		var input     = $('yotp');
		var login     = $('user_login');

		submit.addEventListener('click', yotpCallback);
		container.setAttribute('hidden', '');
		input.setAttribute('disabled', '');

		function loginChangeCallback(e)
		{
			submit.addEventListener('click', yotpCallback);
			login.removeEventListener('change', loginChangeCallback);
		}

		function yotpCallback(e)
		{
			e.preventDefault();
			e.stopPropagation();
			e.stopImmediatePropagation();

			var username = login.value;
			if (!username) {
				return;
			}

			submit.removeEventListener('click', yotpCallback);
			login.addEventListener('change', loginChangeCallback);

			var req = new XMLHttpRequest();
			req.addEventListener('load', function() {
				var r = (typeof this.response === "string") ? JSON.parse(this.response) : this.response;
				if (null === r || this.status !== 200 || !r.status) {
					input.setAttribute('disabled', '');
					container.setAttribute('hidden', '');
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
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', callback);
	}
	else {
		callback();
	}
})();
