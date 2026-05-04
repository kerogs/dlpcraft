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

function copyThisCustomCmd(dataCopyVal, el) {

	// yt-dlp path
	let ytdlppathId = document.querySelector("#ytdlpPath");
	let ytdlpPathVal = "";
	let ytdlpPath = ytdlppathId.value;
	if (ytdlpPath === "path") {
		ytdlpPathVal = "yt-dlp";
	} else if (ytdlpPath === "exe") {
		ytdlpPathVal = ".\\yt-dlp.exe";
	} else{
		let ytdlpCustomPathId = document.querySelector("#ytdlpCustomPath");
		ytdlpPathVal = ytdlpCustomPathId.value;
	}

	// command
	const dataMap = {
		youtube2mp3: '-f "bestaudio/best" --extract-audio --audio-format mp3 --audio-quality 0 --embed-metadata --embed-thumbnail --convert-thumbnails jpg --add-metadata --parse-metadata "uploader:%(artist)s" --parse-metadata "title:%(track)s" --output "./dlpcraft/%(artist)s - %(title)s [%(id)s].%(ext)s" --postprocessor-args "-id3v2_version 3" --ignore-errors --sleep-interval 2 --max-sleep-interval 6 --retries infinite --fragment-retries infinite',
		soundcloud2mp3: '-f "bestaudio" --extract-audio --audio-format mp3 --audio-quality 0 --embed-metadata --embed-thumbnail --add-metadata --parse-metadata "%(uploader)s:%(artist)s" --parse-metadata "%(title)s:%(track)s" --output "./dlpcraft/%(artist)s - %(track)s [%(id)s].%(ext)s" --postprocessor-args "-id3v2_version 3" --ignore-errors --sleep-interval 2 --max-sleep-interval 6 --retries infinite --fragment-retries infinite',
	};

	// url
	let CheckForUrl = document.querySelector("#url");
	let checkForUrlMore = document.querySelectorAll(".moreUrlLists__item");
	let urlArray = [CheckForUrl.value];
	checkForUrlMore.forEach((el) => {
		if (el.value !== "") {
			urlArray.push(el.value);
		}
	});

	// join and add " at the start and end of each url
	urlArray = urlArray.map((url) => `"${url}"`);
	urlArray = urlArray.join(" ");

	// search for dataCopyVal in dataMap
	let textCopy =  ytdlpPathVal + " " + dataMap[dataCopyVal] + " " + urlArray;
	navigator.clipboard.writeText(textCopy);

	el.classList.add("copied");
	setTimeout(() => {
		el.classList.remove("copied");
	}, 1000);
}
window.copyThisCustomCmd = copyThisCustomCmd;
