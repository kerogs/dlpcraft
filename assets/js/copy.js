const copyEl = document.querySelectorAll("[data-copy]");

copyEl.forEach((el) => {
	el.addEventListener("click", () => {
		dataCopyVal = el.getAttribute("data-copy");
		let textCopy = document.querySelector(`#${dataCopyVal}`);
		textCopy.select();
		document.execCommand("copy");

		el.classList.add("copied");
		setTimeout(() => {
			el.classList.remove("copied");
		}, 1000);
	});
});
