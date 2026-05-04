/**
 * Create or update a cookie
 * @param {string} name
 * @param {string} value
 * @param {int} hours default 8760 (1 year in hours)
 */
export const setCookie = (name, value, hours = 8760) => {
	const date = new Date();
	date.setTime(date.getTime() + (hours * 60 * 60 * 1000));
	const expires = "expires=" + date.toUTCString();
	document.cookie = name + "=" + value + ";" + expires + ";path=/";
}

/**
 * delete a cookie
 * @param {string} name
 */
export const deleteCookie = (name) => {
	setCookie(name, "", -1);
}

/**
 * get a cookie
 * @param {string} name
 * @returns {string|null}
 */
export const getCookie = (name) => {
	const nameEQ = name + "=";
	const ca = document.cookie.split(';');
	for (let i = 0; i < ca.length; i++) {
		let c = ca[i];
		while (c.charAt(0) == ' ') c = c.substring(1, c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
	}
	return null;
}

/**
 * show all cookies
 * @param {boolean} cookiePerLine
 */
export const showCookies = (cookiePerLine = true) => {
	if (cookiePerLine){
		document.cookie.split(';').forEach(cookie => console.log(cookie));
	} else{
		console.log(document.cookie);
	}
}

// showCookies();
