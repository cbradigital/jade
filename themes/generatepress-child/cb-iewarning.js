jQuery(document).ready(function cbCheckIE() {
	console.log('Skript geladen');
	var userAgent = window.navigator.userAgent;
	var isIE = userAgent.indexOf('Trident/');
	if(isIE >= 0) {
	    console.log('ist ie');
		jQuery('.smart-page-loader').hide();
		jQuery('.cb-header-iewarning').show();
	}
});
