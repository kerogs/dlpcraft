import { getCookie, setCookie } from "./cookie.js";

// ===============================================
// Theme functions
// ===============================================

const THEMES = ["light", "dark"];
const COOKIE_KEY = "theme";

function applyTheme(theme) {
	document.documentElement.setAttribute("data-theme", theme);
}

function getSystemTheme() {
	return window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
}

function getCurrentTheme() {
	return document.documentElement.getAttribute("data-theme") || getSystemTheme();
}

export function changeTheme() {
	const current = getCurrentTheme();
	const next = THEMES[(THEMES.indexOf(current) + 1) % THEMES.length];
	applyTheme(next);
	setCookie(COOKIE_KEY, next, 365);
}


// ===============================================
// Init - apply saved theme on page load
// ===============================================

(function init() {
    const saved = getCookie(COOKIE_KEY);
    applyTheme(saved || getSystemTheme());
})();

window.changeTheme = changeTheme;
