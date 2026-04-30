<div align="center">

<picture>
  <source media="(prefers-color-scheme: dark)" srcset="assets/images/logo_dark_theme.png" width="100">
  <source media="(prefers-color-scheme: light)" srcset="assets/images/logo_light_theme.png" width="100">
  <img alt="dlpcraft" src="assets/images/logo_light_theme.png">
</picture>

</div>

<h1 align="center">
	DLP Craft
</h1>


<p align="center">
  YT-DLP command generator 
</p>

<div align="center">

![version](https://img.shields.io/badge/Version-1.2.1--beta-%23F5EFE6?style=flat-square&logo=github&logoColor=%23F5EFE6&labelColor=%232E2620&color=%23F5EFE6)

</div>


---

## What is dlpcraft?

**dlpcraft** is a web UI that helps you build `yt-dlp` commands without memorizing flags.  
Paste a URL, toggle the options you need, and get a ready-to-run terminal command.

## Features

- **Source auto-detection**: paste a URL and the source selector updates automatically
- **Predefined flag sets**: per source (YouTube & SoundCloud)
- **Advanced parameters panel**: toggle individual yt-dlp flags with descriptions


## Requirements

### Required

| Tool | Purpose |
|------|---------|
| [yt-dlp](https://github.com/yt-dlp/yt-dlp) | The actual downloader, dlpcraft generates commands for it |

### Optional but recommended

| Tool | Purpose |
|------|---------|
| [ffmpeg](https://ffmpeg.org/download.html) | Required for audio conversion, metadata tagging, and thumbnail embedding |

> [!NOTE] 
> `yt-dlp` alone can download audio streams, but it cannot convert formats (e.g. to mp3), write ID3 tags, or inject cover art into audio files. ffmpeg is the post-processor that handles all of that. dlpcraft will warn you in the UI when any of your active options require it.

## Getting started

**1. Get yt-dlp**

```bash
# via pip
pip install yt-dlp

# or download the standalone binary from
# https://github.com/yt-dlp/yt-dlp/releases
```

**2. (Optional) Get ffmpeg**

Download from [ffmpeg.org](https://ffmpeg.org/download.html) and make sure it is available in your PATH,  
or place `ffmpeg.exe` in the same directory as `yt-dlp.exe`.

**3. Open dlpcraft**

Go to the [dlpcraft website](https:://kerogs.github.io/dlpcraft/).

You can clone the repository and run the index.html file locally if you'd like.

**4. Generate a command**

1. Select your yt-dlp binary mode (system PATH, local `.exe`, or custom path)
2. Paste an URL
3. Toggle any advanced parameters you need (optional)
4. Click **Copy command** and paste it in your terminal

---

## YT-DLP binary modes

| Mode | Command used | When to use |
|------|-------------|-------------|
| `yt-dlp` | `yt-dlp` | yt-dlp is installed system-wide and available in PATH |
| `./yt-dlp.exe` | `.\yt-dlp.exe` | You downloaded the Windows standalone binary in your current directory |
| Custom path | Your path | yt-dlp is somewhere specific, e.g. `C:\tools\yt-dlp.exe` |

## Color palette

dlpcraft uses a custom coffee-toned palette.

| Variable | Hex | Role |
|----------|-----|------|
| `$background` | `#F5EFE6` | Page background |
| `$foreground` | `#2E2620` | Primary text |
| `$heading` | `#2E2620` | Headings |
| `$muted` | `#7A6A59` | Secondary text, descriptions |
| `$border` | `#D4C5B0` | Borders, dividers |
| `$links` | `#A0713A` | Links, accents |
| `$solid` | `#3D200A` | Buttons, badges, strong accents |

Semantic colors (success, info, warning, error) follow the same warm-toned logic —  
see `assets/styles/scss/components/_themes.scss` for the full definitions.

---

## SCSS

```bash
# compile SCSS (once)
sass assets/styles/scss/style.scss assets/styles/css/style.css

# or watch mode
sass --watch assets/styles/scss/style.scss assets/styles/css/style.css
```

---

## Contributing

1. Fork the repository
2. Create a feature branch — `git checkout -b feat/my-feature`
3. Commit your changes — `git commit -m "feat: add my feature"`
4. Push and open a Pull Request

When adding a new yt-dlp parameter, add it to `paramsDefs` in `composer.js` following the existing structure.  
Make sure to set `requiresFfmpeg` and `youtubOnly` correctly. If you need to add any other settings, feel free to do so.

---

## License

![Static Badge](https://img.shields.io/badge/License-MIT-%23F5EFE6?style=flat-square&logo=mit&logoColor=%23F5EFE6&labelColor=%232E2620&color=%23F5EFE6)
