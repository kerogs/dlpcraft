import { setCookie, getCookie } from "./cookie.js";

// ===============================================
// Data about number of commands generated
// ===============================================
const totalCommands = document.querySelector("#totalCommands");

// we show commande total generated on page
// if no data about the number of cookie, we generate one
if (!getCookie("totalCommands")){
	console.log("No cookie 'totalCommands' we generate one");
	try{
		fetch("https://abacus.jasoncameron.dev/get/kerogs/dlpcraft_cmd", {
			method: "GET",
		})
		.then(res => res.json())
		.then(data => {
			// save total cookies for 16 hours
			setCookie("totalCommands", data.value, 16);
			// display result on page
			totalCommands.innerHTML = data.value;
		})
	} catch (error) {console.error(error);}
} else{
	// value exist, just get from cookie and display it.
	console.log("Cookie 'totalCommands' exist. Value:"+getCookie("totalCommands"));
	totalCommands.innerHTML = getCookie("totalCommands");
}

/**
 * Increment the number of commands generated.
 */
window.incrementTotalCommands = function() {
	const total = parseInt(getCookie("totalCommands")) + 1;
	setCookie("totalCommands", total, 2);
	totalCommands.innerHTML = total;

	console.log("Increment total commands");

	// send total to the api
	try{
		fetch("https://abacus.jasoncameron.dev/hit/kerogs/dlpcraft_cmd", {
			method: "GET",
		})
		.then(res => res.json())
		.then(data => {
			// save total cookies for 2 days
			setCookie("totalCommands", data.value, 2);
			// display result on page
			totalCommands.innerHTML = data.value;
		})
	} catch (error) {console.error(error);}
}
