/**
 * Gets an element from the DOM.
 * @param id the element's ID
 * @return the element or null if not found
 */
function $(id){
	return document.getElementById(id);
}

/**
 * Adds commas to a number (example: converts "12345" to "12,345").
 * @param num the number
 * @return the number with commas
 * @see http://www.mredkj.com/javascript/numberFormat.html
 */
function addCommas(num){
	var nStr = num.toString();
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	return x1 + x2;
}

/**
 * Creates a new XML HTTP object for sending AJAX requests.
 * @return the XML HTTP object
 */
function newXmlhttp(){
	if (window.XMLHttpRequest){
		// code for IE7+, Firefox, Chrome, Opera, Safari
		return new XMLHttpRequest();
	} else {
		// code for IE6, IE5
		return new ActiveXObject("Microsoft.XMLHTTP");
	}
}

/**
 * Generates a query string from a list of parameters.
 * @param params an object where each field is a parameter
 * @return the query string
 */
function queryString(params){
	var q = "";
	
	for (p in params){
		var key = p;
		var value = params[p];
		q += encodeURIComponent(key) + "=" + encodeURIComponent(value) + "&";
	}
	
	if (q.length > 0){
		//trim trailing "&"
		q = q.substring(0, q.length-1);
	}
	
	return q;
}
