# yt-dlp-cmd
Liste de script, et autre pour YT-DLP

## Youtube
```sh
.\yt-dlp.exe -i --sleep-interval 2 --max-sleep-interval 6 --retries infinite --fragment-retries infinite -x --audio-format mp3 --audio-quality 0 --prefer-ffmpeg --add-metadata --embed-thumbnail --restrict-filenames --yes-playlist --parse-metadata "title:%(artist)s - %(title)s" -o ".\othermusic\yt\%(artist)s - %(title)s.%(ext)s" "URL_ICI"
```

## Soundcloud
```sh
.\yt-dlp.exe -i --sleep-interval 2 --max-sleep-interval 6 --retries infinite --fragment-retries infinite -x --audio-format mp3 --audio-quality 0 --prefer-ffmpeg --add-metadata --embed-thumbnail --restrict-filenames -o ".\othermusic\soundcloud\%(title)s - %(uploader)s.%(ext)s" "URL_ICI"
```
