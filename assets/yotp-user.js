/** global: yotpSettings, ajaxurl */
(function() {
	var $, form, spinner, btn_submit, list, key_name, otp;

	function showError(msg, where)
	{
		var node = document.querySelector('#' + where + ' + .notice');
		node && node.parentNode.removeChild(node);
		$(where).insertAdjacentHTML('afterend', '<div class="notice notice-error inline"><p>' + msg + '</p></div>');
	}

	function showSuccess(msg, where)
	{
		var node = document.querySelector('#' + where + ' + .notice');
		node && node.parentNode.removeChild(node);
		$(where).insertAdjacentHTML('afterend', '<div class="notice notice-success inline"><p>' + msg + '</p></div>');
	}

	function hideMessages()
	{
		var node = document.querySelector('#new-key + .notice');
		node && node.parentNode.removeChild(node);
		node = document.querySelector('#registered-keys + .notice');
		node && node.parentNode.removeChild(node);
	}

	function showSpinner(show)
	{
		if (show) {
			spinner.classList.add('is-active');
			btn_submit.setAttribute('disabled', '');
		}
		else {
			spinner.classList.remove('is-active');
			btn_submit.removeAttribute('disabled');
		}
	}

	function haveNewItems()
	{
		var noitems = list.querySelector('tr.no-items')
		if (noitems) {
			noitems.parentNode.removeChild(noitems);
		}
	}

	function keyAdded()
	{
		showSpinner(false);
		if (null === this.response || this.status !== 200) {
			showError(yotpSettings.serverError, 'new-key');
			return;
		}

		if (this.response.ok) {
			haveNewItems();
			list.insertAdjacentHTML('beforeend', this.response.row);
			showSuccess(this.response.message, 'new-key');
			key_name.value = '';
			otp.value      = '';
			$('_wpnonce').value = this.response.nonce;
		}
		else {
			showError(this.response.message, 'new-key');
		}
	}

	function killRow(target)
	{
		while (target !== null && target.tagName.toUpperCase() !== 'TR') {
			target = target.parentNode;
		}

		target && target.parentNode.removeChild(target);
	}

	function maybeNoItems()
	{
		if (!list.getElementsByTagName('tr').length) {
			var tpl = document.getElementById('tpl-empty').textContent;
			list.insertAdjacentHTML('beforeend', tpl);
		}
	}

	function keyRevoked()
	{
		var spinner = this.tgt.querySelector('.spinner');
		spinner.classList.remove('is-active');

		if (null === this.response || this.status !== 200) {
			showError(wwU2F.serverError, 'registered-keys');
			return;
		}

		if (this.response.ok) {
			showSuccess(this.response.message, 'registered-keys');
			killRow(this.tgt);
			maybeNoItems();
		}
		else {
			showError(this.response.message, 'registered-keys');
		}
	}

	function callback() {
		$          = document.getElementById.bind(document);
		form       = $('new-key-form');
		spinner    = form.querySelector('.spinner');
		btn_submit = $('submit-button');
		list       = $('the-list');
		key_name   = $('key-name');
		otp        = $('otp');

		document.querySelector('table.yotp th.column-actions').style.width = 0;

		form.addEventListener('submit', function(e) {
			e.preventDefault();
			if (form.reportValidity()) {
				var name  = key_name.value;
				var code  = otp.value;
				var nonce = $('_wpnonce').value;
				var xhr   = new XMLHttpRequest();

				hideMessages();
				showSpinner(true);
				xhr.open('POST', ajaxurl);
				xhr.addEventListener('load', keyAdded);
				xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				xhr.responseType = 'json';
				xhr.send(
					   'action=wwyotp_register'
					+ '&n=' + encodeURIComponent(name) 
					+ '&o=' + encodeURIComponent(code)
					+ '&_wpnonce=' + encodeURIComponent(nonce)
				);
			}
		});

		document.querySelector('table.yotp > tbody').addEventListener('click', function(e) {
			var target = e.target;
			while (target !== null && (!target.tagName || target.tagName.toUpperCase() !== 'BUTTON' || target.className.indexOf('revoke-button') === -1)) {
				target = target.parentNode;
			}

			if (target && confirm(yotpSettings.revconfirm)) {
				var key    = target.dataset.key;
				var nonce  = target.dataset.nonce;
				var req    = new XMLHttpRequest();
				req.tgt    = target;
				req.addEventListener('load', keyRevoked);
				req.open('POST', ajaxurl);
				req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				req.responseType = 'json';

				var spinner = target.querySelector('.spinner');
				spinner.style.float = 'none';
				spinner.style.margin = 0;
				spinner.classList.add('is-active');
				hideMessages();

				req.send(
					  'action=wwyotp_revoke'
					+ '&key=' + encodeURIComponent(key) 
					+ '&_wpnonce=' + encodeURIComponent(nonce)
				);
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', callback);
	}
	else {
		callback();
	}
})();
