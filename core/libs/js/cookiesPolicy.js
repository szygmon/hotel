if (typeof CookiePolicyText === 'undefined')
	var CookiePolicyText = 'Create file cookie.php in your template!';
if (typeof CookiePolicyName === 'undefined')
	var CookiePolicyName = '';

function CookieScriptSetCookie(c_name, value, exdays) {
	var exdate = new Date();
	exdate.setDate(exdate.getDate() + exdays);
	var c_value = escape(value)
			+ ((exdays == null) ? "" : "; expires=" + exdate.toUTCString())
			+ "; path=" + escape('/');
	document.cookie = c_name + "=" + c_value;
}

function CookieScriptGetCookie(c_name) {
	var i, x, y, ARRcookies = document.cookie.split(";");
	for (i = 0; i < ARRcookies.length; i++) {
		x = ARRcookies[i].substr(0, ARRcookies[i].indexOf("="));
		y = ARRcookies[i].substr(ARRcookies[i].indexOf("=") + 1);
		x = x.replace(/^\s+|\s+$/g, "");
		if (x == c_name) {
			return unescape(y);
		}
	}
}

function CookieScriptInsertDiv() {
	$('body').append('<div id="cookiesPolicy">' + CookiePolicyText + '</div>');
}

$(document).ready(function() {
	if (!CookieScriptGetCookie(CookiePolicyName)) {
		CookieScriptInsertDiv();

		$("#cookiesPolicy").on("click", "a", function() {
			CookieScriptSetCookie(CookiePolicyName, '1', 9999);
			$('#cookiesDiv').fadeOut();
			return false;
		})
	}
});