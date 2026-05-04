<div align="center">

<picture>
  <source media="(prefers-color-scheme: dark)" srcset="assets/images/logo_dark_theme.png" width="100">
  <source media="(prefers-color-scheme: light)" srcset="assets/images/logo_light_theme.png" width="100">
  <img alt="dlpcraft" src="assets/images/logo_light_theme.png">
</picture>

</div>

<h1 align="center">
	<i>dlpcraft</i>
</h1>


<p align="center">
  A visual command builder for yt-dlp
</p>

<div align="center">

[![View site - GH Pages](https://img.shields.io/badge/View_site-GH_Pages-%23F5EFE6?style=flat-square&labelColor=%232E2620&color=%23F5EFE6)](https://kerogs.github.io/dlpcraft/)
![version](https://img.shields.io/badge/Version-1.5.1--beta-%23F5EFE6?style=flat-square&logo=github&logoColor=%23F5EFE6&labelColor=%232E2620&color=%23F5EFE6)

![banner](assets/images/banner_small.png)


</div>

## What is dlpcraft?

**dlpcraft** is a web UI that helps you build `yt-dlp` commands without memorizing flags.  
Paste a URL, toggle the options you need, and get a ready-to-run terminal command.

## Features

- **Source auto-detection**: paste a URL and the source selector updates automatically
- **Predefined flag sets**: per source (YouTube & SoundCloud)
- **Advanced parameters panel**: toggle individual yt-dlp flags with descriptions
- **Preconfigured yt-dlp command**: Use preconfigured command lines to get results faster and with better quality.
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

**1. Get [yt-dlp](https://github.com/yt-dlp/yt-dlp/releases)**

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

### Recommended structure
If you don't want to set up the paths, you can call the files directly. We recommend the following structure:
```
dlpcraft/
├── ffmpeg.exe (optional)
└── yt-dlp.exe
```

Be sure to specify in dlpcraft that you are calling ``yt-dlp.exe`` directly. Your commands will then be executed directly by calling the exe file.

## YT-DLP binary modes

| Mode | Command used | When to use |
|------|-------------|-------------|
| `yt-dlp` | `yt-dlp` | yt-dlp is installed system-wide and available in PATH |
| `./yt-dlp.exe` | `.\yt-dlp.exe` | You downloaded the Windows standalone binary in your current directory |
| Custom path | Your path | yt-dlp is somewhere specific, e.g. `C:\tools\yt-dlp.exe` |

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
2. Create a feature branch - `git checkout -b feat/my-feature`
3. Commit your changes - `git commit -m "feat: add my feature"`
4. Push and open a Pull Request

When adding a new yt-dlp parameter, add it to `paramsDefs` in `composer.js` following the existing structure.  
Make sure to set `requiresFfmpeg` and `youtubOnly` correctly. If you need to add any other settings, feel free to do so.

---

## License

![Static Badge](https://img.shields.io/badge/License-MIT-%23F5EFE6?style=flat-square&logo=mit&logoColor=%23F5EFE6&labelColor=%232E2620&color=%23F5EFE6)
