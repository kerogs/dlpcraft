import { setCookie, getCookie } from "./cookies.js";

document.addEventListener("DOMContentLoaded", () => {

	const ytdlpPath = document.querySelector("#ytdlpPath");
	const ytdlpCustomPath = document.querySelector("#ytdlpCustomPath");

	if (!ytdlpPath || !ytdlpCustomPath) return;

	// Restore
	const savedPath = getCookie("ytdlpPath");
	const savedCustom = getCookie("ytdlpCustomPath");

	if (savedPath) {
		ytdlpPath.value = savedPath;
		ytdlpPath.dispatchEvent(new Event("change"));
	}

	// Save on change
	ytdlpPath.addEventListener("change", () => {
		setCookie("ytdlpPath", ytdlpPath.value);
	});

	ytdlpCustomPath.addEventListener("change", () => {
		setCookie("ytdlpCustomPath", ytdlpCustomPath.value);
	});

});

ytdlpCustomPath.value = getCookie("ytdlpCustomPath");
