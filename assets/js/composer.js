// =============================
// Config
// =============================
const urlTemplates = {
	youtube: 'https://www.youtube.com/watch?v=xxxxxxxx',
	'youtu.be': 'https://youtu.be/xxxxxxxxx?si=yyyyyyyyy',
	soundcloud: 'https://soundcloud.com/xxxx/yyyyyyy',
};

/**
 * Parameter definitions for the command builder.
 *
 * Each entry represents a yt-dlp flag that can be toggled by the user.
 *
 * @property {string}  key            - Unique identifier used in `state` and `options`
 * @property {string}  label          - Short flag label shown in the UI badge
 * @property {string}  name           - Human-readable name shown in the params list
 * @property {string}  desc           - Short description shown below the name
 * @property {string}  flag           - The actual yt-dlp flag(s) injected into the command
 * @property {boolean} youtubOnly     - If true, disabled when source is not YouTube
 * @property {boolean} requiresFfmpeg - If true, triggers the ffmpeg warning banner
 * @property {boolean} default        - Whether the option is enabled by default
 */
const paramsDefs = [

	// Resilience
	{
		key: 'ignoreErrors',
		label: '-i',
		name: 'Ignore errors',
		desc: 'Skip unavailable tracks and continue.',
		flag: '-i',
		youtubOnly: false,
		requiresFfmpeg: false,
		default: true,
	},
	{
		key: 'sleepMode',
		label: '--sleep-interval',
		name: 'Sleep interval',
		desc: 'Wait 2–6s between requests to avoid rate limiting.',
		flag: '--sleep-interval 2 --max-sleep-interval 6 --retries infinite --fragment-retries infinite',
		youtubOnly: false,
		requiresFfmpeg: false,
		default: true,
	},

	// Audio
	{
		key: 'audioMode',
		label: '-x mp3',
		name: 'Audio only (mp3)',
		desc: 'Extract audio and convert to mp3, best quality.',
		flag: '-x --audio-format mp3 --audio-quality 0',
		youtubOnly: false,
		requiresFfmpeg: true,
		default: true,
	},
	{
		key: 'ffmpeg',
		label: '--prefer-ffmpeg',
		name: 'Prefer ffmpeg',
		desc: 'Use ffmpeg as the post-processing backend.',
		flag: '--prefer-ffmpeg',
		youtubOnly: false,
		requiresFfmpeg: true,
		default: true,
	},

	// Metadata & artwork
	{
		key: 'metadata',
		label: '--add-metadata',
		name: 'Embed metadata',
		desc: 'Write title, artist and album tags into the file.',
		flag: '--add-metadata',
		youtubOnly: false,
		requiresFfmpeg: true,
		default: true,
	},
	{
		key: 'thumbnail',
		label: '--embed-thumbnail',
		name: 'Embed thumbnail',
		desc: 'Attach the cover art inside the audio file.',
		flag: '--embed-thumbnail',
		youtubOnly: false,
		requiresFfmpeg: true,
		default: true,
	},
	{
		key: 'writeThumbnail',
		label: '--write-thumbnail',
		name: 'Save thumbnail as file',
		desc: 'Write cover art as a separate .jpg next to the audio file.',
		flag: '--write-thumbnail',
		youtubOnly: false,
		requiresFfmpeg: true,
		default: false,
	},

	// Output
	{
		key: 'asciiOnly',
		label: '--restrict-filenames',
		name: 'Restrict filenames',
		desc: 'Use ASCII-only characters in output filenames.',
		flag: '--restrict-filenames',
		youtubOnly: false,
		requiresFfmpeg: false,
		default: true,
	},
	{
		key: 'noOverwrites',
		label: '--no-overwrites',
		name: 'Skip existing files',
		desc: 'Do not overwrite files already downloaded.',
		flag: '--no-overwrites',
		youtubOnly: false,
		requiresFfmpeg: false,
		default: true,
	},

	// Playlist
	{
		key: 'playlist',
		label: '--yes-playlist',
		name: 'Download playlist',
		desc: 'Download all tracks when the URL is a playlist.',
		flag: '--yes-playlist',
		youtubOnly: true,
		requiresFfmpeg: false,
		default: true,
	},
	{
		key: 'noPlaylist',
		label: '--no-playlist',
		name: 'Single track only',
		desc: 'Download only the video, even if the URL includes a playlist.',
		flag: '--no-playlist',
		youtubOnly: false,
		requiresFfmpeg: false,
		default: false,
	},

	// YouTube-specific
	{
		key: 'parseMetadata',
		label: '--parse-metadata',
		name: 'Parse metadata from title',
		desc: 'Extract artist and title from "Artist - Title" format.',
		flag: '--parse-metadata "title:%(artist)s - %(title)s"',
		youtubOnly: true,
		requiresFfmpeg: false,
		default: true,
	},
	// {
	// 	key: 'sponsorBlock',
	// 	label: '--sponsorblock-remove',
	// 	name: 'Remove sponsor segments',
	// 	desc: 'Auto-cut sponsors, intros and outros via SponsorBlock.',
	// 	flag: '--sponsorblock-remove all',
	// 	youtubOnly: true,
	// 	requiresFfmpeg: false,
	// 	default: false,
	// },

];

// =============================
// State
// =============================
const state = Object.fromEntries(paramsDefs.map(p => [p.key, p.default]));

// =============================
// DOM
// =============================
const ytdlpPath = document.querySelector('#ytdlpPath');
const ytdlpCustomPath = document.querySelector('#ytdlpCustomPath');
const generator = document.querySelector('#generator');
const source = generator.querySelector('#source');
const url = generator.querySelector('#url');
const paramsList = document.querySelector('#paramsList');
const paramsPanel = document.querySelector('#params');
const paramsToggle = document.querySelector('#paramsToggleMode');

// =============================
// Helpers
// =============================
function getBase() {
	const mode = ytdlpPath.value;
	return mode === 'custom' ? (ytdlpCustomPath.value.trim() || 'yt-dlp') : mode === 'exe' ? '.\\yt-dlp.exe' : 'yt-dlp';
}

function buildCommand() {
	const isYoutube = source.value === 'youtube';
	const flags = paramsDefs
		.filter(({ key, youtubOnly }) => state[key] && !(youtubOnly && !isYoutube))
		.map(p => p.flag);

	const output = isYoutube
		? '-o "./dlpcraft/%(artist)s - %(title)s.%(ext)s"'
		: '-o "./dlpcraft/%(title)s - %(uploader)s.%(ext)s"';

	return [getBase(), ...flags, output, `"${url.value}"`].join(' ');
}

// =============================
// Ffmpeg warning
// =============================
const ffmpegWarning = document.querySelector('#ffmpegWarning');

function updateFfmpegWarning() {
	const needsFfmpeg = paramsDefs.some(({ key, requiresFfmpeg }) => requiresFfmpeg && state[key]);
	ffmpegWarning.style.display = needsFfmpeg ? 'flex' : 'none';
}

// =============================
// Render
// =============================

function render() {

	// build output
	let moreUrlList = document.querySelectorAll(".moreUrlLists__item");

	let moreUrlListArray = Array.from(moreUrlList)
		.map(input => `"${input.value}"`)
		.filter(v => v !== '""');

	const extraUrls = moreUrlListArray.join(" ");

	generator.querySelector('#urlCopy').value =
		buildCommand() + (extraUrls ? " " + extraUrls : "");

	// display warning if ffmpeg is required
	updateFfmpegWarning();

	// params list
	const isYoutube = source.value === 'youtube';
	paramsList.innerHTML = '';

	paramsDefs.forEach(({ key, name, desc, youtubOnly, requiresFfmpeg }) => {
		const unavailable = youtubOnly && !isYoutube;
		const checked = state[key] && !unavailable;

		const ffmpegBadge = "";

		const item = document.createElement('div');
		item.classList.add('param-item');
		if (requiresFfmpeg) {
			item.classList.add('requires-ffmpeg');
			item.title = "Requires FFMPEG";
		}

		if (unavailable) item.classList.add('disabled');

		item.innerHTML = `
            <div class="param-info">
                <span class="param-name">${name}</span>
                <span class="param-desc">${desc}</span>
            </div>
            <label class="param-toggle"${youtubOnly ? ' title="YouTube only"' : ''}>
                <input type="checkbox" ${checked ? 'checked' : ''} ${unavailable ? 'disabled' : ''}>
                <div class="toggle-track"><div class="toggle-thumb"></div></div>
            </label>
        `;

		item.querySelector('input').addEventListener('change', e => {
			state[key] = e.target.checked;
			render();
		});

		paramsList.appendChild(item);
	});
}

// =============================
// Events
// =============================
source.addEventListener('change', () => {
	url.placeholder = urlTemplates[source.value];
	render();
});

url.addEventListener('input', () => {
	try {
		const { hostname } = new URL(url.value);
		if (hostname === 'www.youtube.com' || hostname === 'youtu.be') source.value = 'youtube';
		else if (hostname === 'soundcloud.com') source.value = 'soundcloud';
	} catch (_) { }
	render();
});

ytdlpPath.addEventListener('change', () => {
	ytdlpCustomPath.style.display = ytdlpPath.value === 'custom' ? 'block' : 'none';
	render();
});

ytdlpCustomPath.addEventListener('input', render);

paramsToggle.addEventListener('click', () => {
	paramsToggle.classList.toggle('open');
	paramsPanel.classList.toggle('open');
});

// =============================
// More Url gestion
// =============================
function addNewUrl() {
	const moreUrlList = document.querySelector('.moreUrlLists');

	const input = document.createElement('input');
	input.type = 'text';
	input.className = 'moreUrlLists__item';
	input.placeholder = 'Put a new url';

	input.addEventListener('input', render);

	moreUrlList.appendChild(input);
}

// =============================
// Init
// =============================
ytdlpCustomPath.style.display = 'none';
url.placeholder = urlTemplates[source.value];
render();

