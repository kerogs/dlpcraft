<?php

/**
 * ============================================================
 *  YouTube → Emby Downloader  (yt-dlp + PHP)
 *  Télécharge des vidéos YouTube et génère tous les fichiers
 *  nécessaires pour une intégration parfaite dans Emby :
 *    - Vidéo (.mp4 / mkv)
 *    - Miniatures (poster, thumb, fanart)
 *    - Fichier .nfo (format Emby / Kodi)
 *    - Fichiers .strm (streaming direct sans téléchargement)
 *    - Structure Saison = Année, Épisode = MMJJ
 * ============================================================
 *
 * USAGE :
 *   php youtube_emby.php --url "https://www.youtube.com/watch?v=XXXX" [options]
 *   php youtube_emby.php --playlist "https://www.youtube.com/playlist?list=XXXX" [options]
 *
 * OPTIONS :
 *   --url          URL d'une vidéo unique
 *   --playlist     URL d'une playlist complète
 *   --output-dir   Dossier de sortie (défaut : ./YouTube)
 *   --show-name    Nom de la « série » (défaut : nom de la chaîne)
 *   --strm         Génère des .strm au lieu de télécharger la vidéo
 *   --quality      Qualité vidéo : best (défaut), 1080, 720, 480
 *   --format       Format vidéo : mp4 (défaut) ou mkv
 *   --no-nfo       Ne génère pas les fichiers .nfo
 *   --no-thumb     Ne télécharge pas les miniatures
 *   --sleep        Pause entre les téléchargements en secondes (défaut : 3)
 *   --cookies      Chemin vers un fichier cookies.txt (pour vidéos membres)
 *   --ytdlp-path   Chemin vers yt-dlp (défaut : yt-dlp dans le PATH)
 *
 * EXEMPLES :
 *   php youtube_emby.php --url "https://youtu.be/XXXX" --show-name "MaChaîne"
 *   php youtube_emby.php --playlist "https://youtube.com/playlist?list=PLxxx" --strm
 *   php youtube_emby.php --url "https://youtu.be/XXXX" --quality 720 --format mkv
 * ============================================================
 */

// ─── CONFIG PAR DÉFAUT ────────────────────────────────────────────────────────
$config = [
    'url'        => '',
    'playlist'   => '',
    'output_dir' => './YouTube',
    'show_name'  => '',
    'strm'       => false,
    'quality'    => 'best',
    'format'     => 'mp4',
    'no_nfo'     => false,
    'no_thumb'   => false,
    'sleep'      => 3,
    'cookies'    => '',
    'ytdlp_path' => 'yt-dlp',
];

// ─── PARSING DES ARGUMENTS CLI ───────────────────────────────────────────────
$args = $argv ?? [];
array_shift($args); // retire le nom du script

for ($i = 0; $i < count($args); $i++) {
    switch ($args[$i]) {
        case '--url':
            $config['url']        = $args[++$i] ?? '';
            break;
        case '--playlist':
            $config['playlist']   = $args[++$i] ?? '';
            break;
        case '--output-dir':
            $config['output_dir'] = $args[++$i] ?? './YouTube';
            break;
        case '--show-name':
            $config['show_name']  = $args[++$i] ?? '';
            break;
        case '--strm':
            $config['strm']       = true;
            break;
        case '--quality':
            $config['quality']    = $args[++$i] ?? 'best';
            break;
        case '--format':
            $config['format']     = $args[++$i] ?? 'mp4';
            break;
        case '--no-nfo':
            $config['no_nfo']     = true;
            break;
        case '--no-thumb':
            $config['no_thumb']   = true;
            break;
        case '--sleep':
            $config['sleep']      = (int)($args[++$i] ?? 3);
            break;
        case '--cookies':
            $config['cookies']    = $args[++$i] ?? '';
            break;
        case '--ytdlp-path':
            $config['ytdlp_path'] = $args[++$i] ?? 'yt-dlp';
            break;
        case '--help':
            print_help();
            exit(0);
    }
}

// ─── VALIDATION ───────────────────────────────────────────────────────────────
if (empty($config['url']) && empty($config['playlist'])) {
    log_msg('ERROR', 'Vous devez fournir --url ou --playlist. Utilisez --help pour l\'aide.');
    exit(1);
}

check_dependencies($config['ytdlp_path']);

// ─── POINT D'ENTRÉE ──────────────────────────────────────────────────────────
$target_url = !empty($config['playlist']) ? $config['playlist'] : $config['url'];
$is_playlist = !empty($config['playlist']);

log_msg('INFO', 'Démarrage du traitement...');
log_msg('INFO', 'URL cible : ' . $target_url);
log_msg('INFO', 'Mode : ' . ($config['strm'] ? 'STRM (streaming)' : 'TÉLÉCHARGEMENT'));

// 1. Récupération des métadonnées
$videos = fetch_metadata($target_url, $config);

if (empty($videos)) {
    log_msg('ERROR', 'Aucune vidéo trouvée. Vérifiez l\'URL.');
    exit(1);
}

log_msg('INFO', count($videos) . ' vidéo(s) trouvée(s).');

// 2. Récupère avatar + bannière de la chaîne une seule fois
$channel_avatar_url = '';
$channel_banner_url = '';
if (!empty($videos)) {
    log_msg('INFO', 'Récupération des images de la chaîne YouTube...');
    $channel_images = get_channel_images(
        $videos[0]['channel_id'],
        $videos[0]['channel_url'] ?? ''
    );
    $channel_avatar_url = $channel_images['avatar'];
    $channel_banner_url = $channel_images['banner'];
    if (!empty($channel_avatar_url)) {
        log_msg('SUCCESS', 'Avatar chaîne trouvé.');
    } else {
        log_msg('WARN', 'Avatar chaîne introuvable.');
    }
    if (!empty($channel_banner_url)) {
        log_msg('SUCCESS', 'Bannière chaîne trouvée.');
    } else {
        log_msg('WARN', 'Bannière chaîne introuvable.');
    }
}

// 3. Traitement de chaque vidéo
foreach ($videos as $index => $video) {
    $video['channel_avatar'] = $channel_avatar_url;
    $video['channel_banner'] = $channel_banner_url;
    log_msg('INFO', sprintf('[%d/%d] Traitement : %s', $index + 1, count($videos), $video['title']));
    process_video($video, $config);

    if ($index < count($videos) - 1) {
        log_msg('INFO', "Pause de {$config['sleep']} secondes...");
        sleep($config['sleep']);
    }
}

// 4. Génération du tvshow.nfo (racine de la série)
if (!$config['no_nfo']) {
    generate_tvshow_nfo($videos, $config, $channel_avatar_url, $channel_banner_url);
}

log_msg('SUCCESS', 'Traitement terminé !');

// ─── FONCTIONS ────────────────────────────────────────────────────────────────

/**
 * Récupère les métadonnées JSON de toutes les vidéos via yt-dlp
 */
function fetch_metadata(string $url, array $config): array
{
    log_msg('INFO', 'Récupération des métadonnées...');

    $cmd = build_ytdlp_cmd($config, [
        '--dump-json',
        '--no-warnings',
        '--skip-download',
    ], $url);

    $output = [];
    exec($cmd . ' ' . null_redirect(), $output, $ret);

    if ($ret !== 0 && empty($output)) {
        log_msg('ERROR', 'Impossible de récupérer les métadonnées. Code retour : ' . $ret);
        return [];
    }

    $videos = [];
    foreach ($output as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        $data = json_decode($line, true);
        if ($data && isset($data['id'])) {
            $videos[] = normalize_video_data($data);
        }
    }

    return $videos;
}

/**
 * Normalise les données d'une vidéo en structure uniforme
 */
function normalize_video_data(array $data): array
{
    $upload_date = $data['upload_date'] ?? date('Ymd'); // YYYYMMDD
    $year   = substr($upload_date, 0, 4);
    $month  = substr($upload_date, 4, 2);
    $day    = substr($upload_date, 6, 2);

    // Numéro d'épisode au format MMJJ (ex: 0325 pour le 25 mars)
    $episode_num = (int)($month . $day);

    // Nettoyage du titre pour usage comme nom de fichier
    $safe_title = preg_replace('/[^\w\s\-\.\[\]\(\)]/u', '', $data['title'] ?? 'Sans titre');
    $safe_title = preg_replace('/\s+/', ' ', trim($safe_title));
    $safe_title = substr($safe_title, 0, 100); // limite la longueur

    return [
        'id'            => $data['id'] ?? '',
        'url'           => 'https://www.youtube.com/watch?v=' . ($data['id'] ?? ''),
        'title'         => $data['title'] ?? 'Sans titre',
        'safe_title'    => $safe_title,
        'channel'       => $data['channel'] ?? $data['uploader'] ?? 'Chaîne inconnue',
        'channel_id'    => $data['channel_id'] ?? $data['uploader_id'] ?? '',
        'channel_url'   => $data['channel_url'] ?? $data['uploader_url'] ?? '',
        'description'   => $data['description'] ?? '',
        'upload_date'   => $upload_date,
        'year'          => $year,
        'month'         => $month,
        'day'           => $day,
        'episode_num'   => $episode_num,
        'season_num'    => (int)$year,
        'duration'      => $data['duration'] ?? 0,
        'view_count'    => $data['view_count'] ?? 0,
        'like_count'    => $data['like_count'] ?? 0,
        'tags'          => $data['tags'] ?? [],
        'categories'    => $data['categories'] ?? [],
        'thumbnail'     => $data['thumbnail'] ?? '',
        'thumbnails'    => $data['thumbnails'] ?? [],
        'webpage_url'   => $data['webpage_url'] ?? ('https://www.youtube.com/watch?v=' . ($data['id'] ?? '')),
        'subtitles'     => $data['subtitles'] ?? [],
        'automatic_captions' => $data['automatic_captions'] ?? [],
        'language'      => $data['language'] ?? '',
        'age_limit'     => $data['age_limit'] ?? 0,
        'availability'  => $data['availability'] ?? 'public',
        'channel_avatar' => '', // rempli plus tard
        'channel_banner' => '', // rempli plus tard
    ];
}

/**
 * Traite une vidéo : téléchargement/strm + NFO + miniatures
 */
function process_video(array $video, array $config): void
{
    // Détermine les chemins
    $show_name  = !empty($config['show_name']) ? $config['show_name'] : sanitize_dirname($video['channel']);
    $season_dir = sprintf('%s/%s/Season %s', $config['output_dir'], $show_name, $video['year']);
    $base_name  = sprintf('S%sE%04d - %s', $video['year'], $video['episode_num'], $video['safe_title']);

    // Crée les dossiers
    if (!is_dir($season_dir)) {
        mkdir($season_dir, 0755, true);
        log_msg('INFO', 'Dossier créé : ' . $season_dir);
    }

    $file_base = $season_dir . '/' . $base_name;

    if ($config['strm']) {
        // ── MODE STRM ──────────────────────────────────────────────────────
        generate_strm($video, $file_base, $config);
    } else {
        // ── MODE TÉLÉCHARGEMENT ────────────────────────────────────────────
        download_video($video, $file_base, $config);
    }

    // ── MINIATURES ────────────────────────────────────────────────────────
    if (!$config['no_thumb']) {
        download_thumbnails($video, $file_base, $season_dir, $config);
    }

    // ── NFO ───────────────────────────────────────────────────────────────
    if (!$config['no_nfo']) {
        generate_episode_nfo($video, $file_base, $config);
    }
}

/**
 * Télécharge la vidéo avec yt-dlp
 */
function download_video(array $video, string $file_base, array $config): void
{
    $ext     = $config['format'];
    $out_tpl = $file_base . '.%(ext)s'; // yt-dlp remplace %(ext)s

    // Format selector selon la qualité demandée
    $format_sel = build_format_selector($config['quality'], $config['format']);

    // ── Étape 1 : téléchargement de la vidéo uniquement ──────────────────
    $video_args = [
        '--format',
        esc($format_sel),
        '--merge-output-format',
        esc($config['format']),
        '--write-thumbnail',
        '--convert-thumbnails',
        'jpg',
        '--embed-metadata',
        '--embed-chapters',
        '--add-metadata',
        '--no-playlist',
        '--output',
        esc($out_tpl),
    ];

    $cmd = build_ytdlp_cmd($config, $video_args, $video['url']);

    log_msg('INFO', 'Téléchargement de la vidéo...');
    passthru($cmd, $ret);

    $downloaded = glob($file_base . '.' . $config['format']);
    if (!empty($downloaded)) {
        log_msg('SUCCESS', 'Vidéo téléchargée : ' . basename($downloaded[0]));
    } else {
        log_msg('WARN', 'Vidéo non téléchargée (code ' . $ret . ') pour ' . $video['id']);
        return; // inutile de continuer
    }

    // ── Étape 2 : sous-titres séparément (FR, EN, KO) — échec ignoré ─────
    log_msg('INFO', 'Tentative de téléchargement des sous-titres (FR/EN/KO)...');
    $sub_args = [
        '--skip-download',           // ne re-télécharge pas la vidéo
        '--write-auto-subs',
        '--write-subs',              // sous-titres manuels aussi si disponibles
        '--sub-langs',
        'fr,en,ko',
        '--convert-subs',
        'srt',
        '--ignore-errors',
        '--no-abort-on-error',
        '--no-playlist',
        '--output',
        esc($out_tpl),
    ];

    $sub_cmd = build_ytdlp_cmd($config, $sub_args, $video['url']);
    exec($sub_cmd . ' ' . null_redirect(), $sub_out, $sub_ret);

    if ($sub_ret === 0) {
        log_msg('SUCCESS', 'Sous-titres téléchargés.');
    } else {
        log_msg('INFO', 'Aucun sous-titre disponible (ignoré).');
    }
}

/**
 * Génère un fichier .strm (lien direct pour Emby)
 */
function generate_strm(array $video, string $file_base, array $config): void
{
    $strm_path = $file_base . '.strm';

    if (file_exists($strm_path)) {
        log_msg('INFO', 'STRM déjà existant, ignoré : ' . basename($strm_path));
        return;
    }

    // Récupère l'URL directe du stream
    $format_sel = build_format_selector($config['quality'], $config['format']);
    $cmd = build_ytdlp_cmd($config, [
        '--format',
        esc($format_sel),
        '--get-url',
        '--no-playlist',
    ], $video['url']);

    $stream_urls = [];
    exec($cmd . ' ' . null_redirect(), $stream_urls, $ret);

    if ($ret !== 0 || empty($stream_urls)) {
        log_msg('WARN', 'Impossible d\'obtenir l\'URL de stream pour ' . $video['id']);
        // Fallback : URL YouTube directe (Emby peut la résoudre avec le plugin)
        file_put_contents($strm_path, $video['url'] . PHP_EOL);
    } else {
        // Prend la première URL (vidéo+audio fusionnés si possible)
        file_put_contents($strm_path, $stream_urls[0] . PHP_EOL);
    }

    log_msg('SUCCESS', 'STRM généré : ' . basename($strm_path));
}

/**
 * Télécharge et prépare toutes les images Emby pour un épisode et la série
 *
 * Structure Emby attendue :
 *   Épisode  : NomFichier-thumb.jpg
 *   Saison   : poster.jpg, banner.jpg, backdrop.jpg
 *   Série    : poster.jpg, logo.png, banner.jpg, fanart.jpg, disc.jpg,
 *              clearart.jpg, backdrop.jpg, landscape.jpg
 *              + actor-NomChaine.jpg (photo de profil chaîne)
 */
function download_thumbnails(array $video, string $file_base, string $season_dir, array $config): void
{
    $thumb_url = get_best_thumbnail($video);
    $show_dir  = dirname($season_dir);

    // ── 1. Miniature de l'épisode ─────────────────────────────────────────
    $thumb_path = $file_base . '-thumb.jpg';
    if (!file_exists($thumb_path) && !empty($thumb_url)) {
        $img = @file_get_contents($thumb_url);
        if ($img !== false) {
            file_put_contents($thumb_path, $img);
            log_msg('SUCCESS', 'Épisode thumb : ' . basename($thumb_path));
        } else {
            // Fallback yt-dlp
            $cmd = build_ytdlp_cmd($config, [
                '--skip-download',
                '--write-thumbnail',
                '--convert-thumbnails',
                'jpg',
                '--output',
                esc($file_base . '.%(ext)s'),
            ], $video['url']);
            exec($cmd . ' ' . null_redirect());
            // yt-dlp génère NomFichier.jpg, on le renomme en -thumb.jpg
            $ytdlp_thumb = $file_base . '.jpg';
            if (file_exists($ytdlp_thumb) && !file_exists($thumb_path)) {
                rename($ytdlp_thumb, $thumb_path);
            }
        }
    }

    // Source pour les images série/saison : miniature de la vidéo
    $source_img = file_exists($thumb_path) ? $thumb_path : null;

    // ── Télécharge bannière et avatar de la chaîne (une seule fois par série) ──
    $banner_path = $show_dir . '/banner.jpg';
    $fanart_path = $show_dir . '/fanart.jpg';

    if (!empty($video['channel_banner']) && (!file_exists($banner_path) || !file_exists($fanart_path))) {
        $banner_data = @file_get_contents($video['channel_banner'], false, stream_context_create([
            'http' => ['timeout' => 15, 'user_agent' => 'Mozilla/5.0']
        ]));
        if ($banner_data !== false) {
            // Bannière série
            if (!file_exists($banner_path)) {
                file_put_contents($banner_path, $banner_data);
                log_msg('SUCCESS', 'Bannière chaîne → série/banner.jpg');
            }
            // Arrière-plan série (même image haute résolution)
            if (!file_exists($fanart_path)) {
                file_put_contents($fanart_path, $banner_data);
                log_msg('SUCCESS', 'Arrière-plan chaîne → série/fanart.jpg');
            }
            // Backdrop série
            $backdrop_path = $show_dir . '/backdrop.jpg';
            if (!file_exists($backdrop_path)) {
                file_put_contents($backdrop_path, $banner_data);
                log_msg('INFO', 'Backdrop chaîne → série/backdrop.jpg');
            }
            // Landscape série
            $landscape_path = $show_dir . '/landscape.jpg';
            if (!file_exists($landscape_path)) {
                file_put_contents($landscape_path, $banner_data);
                log_msg('INFO', 'Landscape chaîne → série/landscape.jpg');
            }
            // Saison : bannière + backdrop aussi
            $season_banner = $season_dir . '/banner.jpg';
            if (!file_exists($season_banner)) {
                file_put_contents($season_banner, $banner_data);
                log_msg('INFO', 'Bannière chaîne → saison/banner.jpg');
            }
            $season_backdrop = $season_dir . '/backdrop.jpg';
            if (!file_exists($season_backdrop)) {
                file_put_contents($season_backdrop, $banner_data);
                log_msg('INFO', 'Backdrop chaîne → saison/backdrop.jpg');
            }
        } else {
            log_msg('WARN', 'Impossible de télécharger la bannière de la chaîne.');
        }
    }

    // ── 2. Images de la SAISON (depuis miniature vidéo) ──────────────────
    $season_images = [
        'poster.jpg' => $source_img,
    ];
    foreach ($season_images as $filename => $src) {
        $dest = $season_dir . '/' . $filename;
        if (!file_exists($dest) && $src && file_exists($src)) {
            copy($src, $dest);
            log_msg('INFO', 'Saison ' . $filename . ' créé.');
        }
    }

    // ── 3. Images de la SÉRIE depuis miniature vidéo (si pas déjà faites avec la bannière) ──
    $series_images = [
        'poster.jpg'            => $source_img,
        'logo.jpg'              => $source_img,
        'disc.jpg'              => $source_img,
        'clearart.jpg'          => $source_img,
        'season-all-poster.jpg' => $source_img,
        // banner/fanart/backdrop/landscape = bannière chaîne (gérée ci-dessus)
        // Si pas de bannière dispo, fallback sur la miniature vidéo
        'banner.jpg'            => (empty($video['channel_banner']) ? $source_img : null),
        'fanart.jpg'            => (empty($video['channel_banner']) ? $source_img : null),
        'backdrop.jpg'          => (empty($video['channel_banner']) ? $source_img : null),
        'landscape.jpg'         => (empty($video['channel_banner']) ? $source_img : null),
    ];

    foreach ($series_images as $filename => $src) {
        $dest = $show_dir . '/' . $filename;
        if (!file_exists($dest) && $src && file_exists($src)) {
            copy($src, $dest);
            log_msg('INFO', 'Série ' . $filename . ' créé.');
        }
    }

    // ── 4. Photo de profil de la chaîne (acteur dans Emby) ───────────────
    //   Emby cherche : .actors/NomActeur.jpg dans le dossier de la série
    //   On télécharge l'avatar YouTube de la chaîne via l'API publique
    download_channel_avatar($video, $show_dir);
}

/**
 * Télécharge la photo de profil de la chaîne YouTube
 * et la place dans .actors/ pour que Emby l'associe au créateur
 */
/**
 * Récupère avatar ET bannière de la chaîne YouTube en un seul chargement de page.
 * Retourne [ 'avatar' => 'https://...', 'banner' => 'https://...' ]
 */
function get_channel_images(string $channel_id, string $channel_url): array
{
    $result = ['avatar' => '', 'banner' => ''];

    if (empty($channel_id) && empty($channel_url)) return $result;

    $page_url = !empty($channel_url)
        ? $channel_url
        : 'https://www.youtube.com/channel/' . $channel_id;

    $ctx = stream_context_create(['http' => [
        'timeout'    => 15,
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36',
        'header'     => "Accept-Language: fr-FR,fr;q=0.9,en;q=0.8
",
    ]]);

    $page = @file_get_contents($page_url, false, $ctx);
    if ($page === false) return $result;

    // ── AVATAR ────────────────────────────────────────────────────────────
    // og:image = photo de profil de la chaîne
    if (preg_match('/<meta property="og:image" content="([^"]+)"/', $page, $m)) {
        $result['avatar'] = html_entity_decode($m[1]);
    }
    // Fallback JSON embarqué
    if (empty($result['avatar'])) {
        foreach (['"avatar":{"thumbnails":[{"url":"([^"]+)"', '"channelAvatarUrl":"([^"]+)"', '"width":88,"height":88}.*?"url":"([^"]+)"'] as $pat) {
            if (preg_match('/#' . $pat . '#', $page, $m)) {
                $result['avatar'] = html_entity_decode(str_replace('\\u0026', '&', $m[1]));
                break;
            }
        }
    }

    // ── BANNIÈRE ──────────────────────────────────────────────────────────
    // YouTube stocke la bannière dans le JSON ytInitialData sous "banner"
    // Pattern : "banner":{"imageBanner":{"image":{"thumbnails":[{"url":"https://...","width":...}
    $banner_patterns = [
        '"banner":{"imageBanner":{"image":{"thumbnails":\[{"url":"([^"]+)"',
        '"c4TabbedHeaderRenderer".*?"banner":{"imageBanner":{"image":{"thumbnails":\[{"url":"([^"]+)"',
        '"tvBanner":{"imageBanner":{"image":{"thumbnails":\[{"url":"([^"]+)"',
        '"bannerImageUrl":"([^"]+)"',
        '"banner":\{"thumbnails":\[.*?"url":"([^"]+)","width":2560',
        '"width":2560,"height":1440},"url":"([^"]+)"',
        '"url":"([^"]+)","width":2560',
    ];
    foreach ($banner_patterns as $pat) {
        if (preg_match('/#' . $pat . '#s', $page, $m)) {
            $url = html_entity_decode(str_replace('\\u0026', '&', $m[1]));
            if (strpos($url, 'http') === 0) {
                $result['banner'] = $url;
                break;
            }
        }
    }

    // Fallback : og:image:width élevée = bannière possible
    if (empty($result['banner'])) {
        // Cherche toutes les URLs yt3.googleusercontent.com avec =w2560
        if (preg_match_all('/https:\\/\\/yt3\.googleusercontent\.com\/[^"\\]+/', $page, $matches)) {
            foreach ($matches[0] as $url) {
                $clean = html_entity_decode(str_replace('\\u0026', '&', $url));
                // La bannière a typiquement =w2560 ou banner dans l'URL
                if (strpos($clean, 'w2560') !== false || stripos($clean, 'banner') !== false) {
                    $result['banner'] = $clean;
                    break;
                }
            }
        }
    }

    return $result;
}

/**
 * Compatibilité : wrapper pour l'ancien appel get_channel_avatar_url()
 */
function get_channel_avatar_url(string $channel_id, string $channel_url): string
{
    $imgs = get_channel_images($channel_id, $channel_url);
    return $imgs['avatar'];
}

/**
 * Télécharge avatar + bannière et les place dans le dossier de la série
 * pour Emby (acteur) et comme images de la série.
 */
function download_channel_avatar(array $video, string $show_dir): void
{
    // Plus utilisé directement — géré dans download_thumbnails via channel_banner
}

/**
 * Génère le fichier NFO d'un épisode (format Emby/Kodi)
 */
function generate_episode_nfo(array $video, string $file_base, array $config): void
{
    $nfo_path = $file_base . '.nfo';

    $duration_formatted = format_duration($video['duration']);
    $aired = sprintf('%s-%s-%s', $video['year'], $video['month'], $video['day']);

    $tags_xml = '';
    foreach ($video['tags'] as $tag) {
        $tags_xml .= '    <tag>' . xmle($tag) . '</tag>' . PHP_EOL;
    }

    $genres_xml = '';
    foreach ($video['categories'] as $cat) {
        $genres_xml .= '    <genre>' . xmle($cat) . '</genre>' . PHP_EOL;
    }

    $show_name = !empty($config['show_name']) ? $config['show_name'] : $video['channel'];

    $nfo = <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<episodedetails>

    <!-- ── Identification ───────────────────────── -->
    <title>{$video['safe_title']}</title>
    <originaltitle>{$video['title']}</originaltitle>
    <showtitle>{$show_name}</showtitle>

    <!-- ── Numérotation Emby (Saison=Année, Ep=MMJJ) -->
    <season>{$video['season_num']}</season>
    <episode>{$video['episode_num']}</episode>
    <displayseason>{$video['year']}</displayseason>
    <displayepisode>{$video['episode_num']}</displayepisode>

    <!-- ── Dates ─────────────────────────────────── -->
    <aired>{$aired}</aired>
    <premiered>{$aired}</premiered>
    <year>{$video['year']}</year>
    <dateadded>{$aired}</dateadded>

    <!-- ── Médias ─────────────────────────────────── -->
    <runtime>{$duration_formatted}</runtime>

    <!-- ── Description ───────────────────────────── -->
    <plot>{$video['description']}</plot>
    <outline>{$video['description']}</outline>

    <!-- ── Source YouTube ────────────────────────── -->
    <uniqueid type="youtube" default="true">{$video['id']}</uniqueid>
    <id>{$video['id']}</id>

    <!-- ── Statistiques ──────────────────────────── -->
    <userrating>{$video['view_count']}</userrating>
    <votes>{$video['like_count']}</votes>

    <!-- ── Créateur / Studio ─────────────────────── -->
    <studio>{$video['channel']}</studio>
    <director>{$video['channel']}</director>

    <actor>
        <name>{$video['channel']}</name>
        <role>Créateur</role>
        <type>Director</type>
        <thumb>{$video['channel_avatar']}</thumb>
    </actor>

    <!-- ── Tags & Genres ─────────────────────────── -->
{$tags_xml}{$genres_xml}
    <!-- ── Classification ────────────────────────── -->
    <agerating>{$video['age_limit']}</agerating>
    <contentrating>{$video['age_limit']}</contentrating>

    <!-- ── URL source ────────────────────────────── -->
    <trailer>{$video['webpage_url']}</trailer>

    <!-- ── Miniature ─────────────────────────────── -->
    <thumb aspect="thumb">{$video['thumbnail']}</thumb>

</episodedetails>
XML;

    file_put_contents($nfo_path, $nfo);
    log_msg('SUCCESS', 'NFO épisode généré : ' . basename($nfo_path));
}

/**
 * Génère le tvshow.nfo à la racine de la série
 */
function generate_tvshow_nfo(array $videos, array $config, string $channel_avatar_url = ''): void
{
    if (empty($videos)) return;

    $first        = $videos[0];
    $show_name    = !empty($config['show_name']) ? $config['show_name'] : $first['channel'];
    $show_dir     = $config['output_dir'] . '/' . sanitize_dirname($show_name);
    $nfo_path     = $show_dir . '/tvshow.nfo';
    $channel_url  = $first['channel_url'] ?? '';

    // Collecte les années pour les saisons
    $years  = array_unique(array_column($videos, 'year'));
    sort($years);
    $tags_all = array_unique(array_merge(...array_column($videos, 'tags')));
    $cats_all = array_unique(array_merge(...array_column($videos, 'categories')));

    $seasons_xml = '';
    foreach ($years as $y) {
        $seasons_xml .= "    <season number=\"{$y}\">{$y}</season>" . PHP_EOL;
    }

    $tags_xml = '';
    foreach (array_slice($tags_all, 0, 20) as $tag) {
        $tags_xml .= '    <tag>' . xmle($tag) . '</tag>' . PHP_EOL;
    }

    $genres_xml = '';
    foreach ($cats_all as $cat) {
        $genres_xml .= '    <genre>' . xmle($cat) . '</genre>' . PHP_EOL;
    }

    $total      = count($videos);
    $first_date = $first['year'] . '-' . $first['month'] . '-' . $first['day'];
    if (!is_dir($show_dir)) mkdir($show_dir, 0755, true);

    $nfo = <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<tvshow>

    <!-- ── Identification ───────────────────────── -->
    <title>{$show_name}</title>
    <originaltitle>{$show_name}</originaltitle>
    <sorttitle>{$show_name}</sorttitle>

    <!-- ── Description ───────────────────────────── -->
    <plot>Chaîne YouTube : {$first['channel']}
URL : {$channel_url}
Total vidéos archivées : {$total}</plot>

    <!-- ── Dates ─────────────────────────────────── -->
    <premiered>{$first_date}</premiered>
    <year>{$first['year']}</year>

    <!-- ── Studio / Créateur ─────────────────────── -->
    <studio>{$first['channel']}</studio>

    <actor>
        <name>{$first['channel']}</name>
        <role>Créateur</role>
        <type>Creator</type>
        <url>{$channel_url}</url>
        <thumb>{$channel_avatar_url}</thumb>
    </actor>

    <!-- ── Saisons ────────────────────────────────── -->
{$seasons_xml}
    <!-- ── Tags & Genres ─────────────────────────── -->
{$tags_xml}{$genres_xml}
    <!-- ── Identifiant YouTube ───────────────────── -->
    <uniqueid type="youtube_channel" default="true">{$first['channel_id']}</uniqueid>
    <id>{$first['channel_id']}</id>

    <!-- ── Statut ────────────────────────────────── -->
    <status>Continuing</status>

</tvshow>
XML;

    file_put_contents($nfo_path, $nfo);
    log_msg('SUCCESS', 'tvshow.nfo généré : ' . $nfo_path);
}

// ─── UTILITAIRES ──────────────────────────────────────────────────────────────

/**
 * Construit la commande yt-dlp de base avec options communes
 */
function is_windows(): bool
{
    return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
}

/**
 * Echappe un argument pour la ligne de commande (compatible Windows et Unix)
 */
function esc(string $arg): string
{
    if (is_windows()) {
        // Sur Windows : guillemets doubles, on échappe les guillemets internes
        $arg = str_replace('"', '""', $arg);
        return '"' . $arg . '"';
    }
    return escapeshellarg($arg);
}

/**
 * Redirige stderr vers null selon l'OS
 */
function null_redirect(): string
{
    return is_windows() ? '2>NUL' : '2>/dev/null';
}

function build_ytdlp_cmd(array $config, array $extra_args = [], string $url = ''): string
{
    // Sur Windows, on encapsule le chemin avec guillemets doubles si besoin
    $ytdlp = esc($config['ytdlp_path']);
    $parts = [$ytdlp];

    // Options communes
    $parts[] = '--no-warnings';
    $parts[] = '--retries infinite';
    $parts[] = '--fragment-retries infinite';
    $parts[] = '--sleep-interval ' . $config['sleep'];
    $parts[] = '--max-sleep-interval ' . ($config['sleep'] + 5);

    // Cookies optionnels
    if (!empty($config['cookies']) && file_exists($config['cookies'])) {
        $parts[] = '--cookies ' . esc($config['cookies']);
    }

    // Arguments supplémentaires (déjà échappés par l'appelant ou flags simples)
    foreach ($extra_args as $arg) {
        $parts[] = $arg;
    }

    // URL en dernier
    if (!empty($url)) {
        $parts[] = esc($url);
    }

    return implode(' ', $parts);
}

/**
 * Construit le sélecteur de format yt-dlp selon la qualité désirée
 */
function build_format_selector(string $quality, string $format): string
{
    $ext = $format === 'mkv' ? 'mkv' : 'mp4';

    switch ($quality) {
        case '1080':
            return "bestvideo[height<=1080][ext={$ext}]+bestaudio[ext=m4a]/bestvideo[height<=1080]+bestaudio/best[height<=1080]/best";
        case '720':
            return "bestvideo[height<=720][ext={$ext}]+bestaudio[ext=m4a]/bestvideo[height<=720]+bestaudio/best[height<=720]/best";
        case '480':
            return "bestvideo[height<=480]+bestaudio/best[height<=480]/best";
        default: // best
            return "bestvideo[ext={$ext}]+bestaudio[ext=m4a]/bestvideo+bestaudio/best";
    }
}

/**
 * Sélectionne la miniature de meilleure résolution disponible
 */
function get_best_thumbnail(array $video): string
{
    // Tente les thumbnails triées par résolution décroissante
    if (!empty($video['thumbnails'])) {
        usort($video['thumbnails'], function ($a, $b) {
            $wa = ($a['width'] ?? 0) * ($a['height'] ?? 0);
            $wb = ($b['width'] ?? 0) * ($b['height'] ?? 0);
            return $wb - $wa;
        });
        foreach ($video['thumbnails'] as $t) {
            if (!empty($t['url'])) return $t['url'];
        }
    }
    return $video['thumbnail'] ?? '';
}

/**
 * Formate une durée en secondes en minutes (pour NFO)
 */
function format_duration(int $seconds): int
{
    return (int)ceil($seconds / 60);
}

/**
 * Échappe une chaîne pour XML
 */
function xmle(string $s): string
{
    return htmlspecialchars($s, ENT_XML1 | ENT_COMPAT, 'UTF-8');
}

/**
 * Nettoie un nom pour usage comme nom de dossier
 */
function sanitize_dirname(string $name): string
{
    // Retire les caractères interdits Windows + # qui pose problème dans les chemins
    $name = preg_replace('/[<>:"\/\\|?*#\x00-\x1F]/u', '', $name);
    $name = preg_replace('/\s+/', ' ', trim($name));
    // Windows interdit les noms se terminant par un point ou un espace
    $name = rtrim($name, '. ');
    return substr($name, 0, 100) ?: 'Unknown';
}

/**
 * Vérifie que yt-dlp est disponible
 */
function check_dependencies(string $ytdlp_path): void
{
    exec(esc($ytdlp_path) . ' --version ' . null_redirect(), $out, $ret);
    if ($ret !== 0) {
        log_msg('ERROR', "yt-dlp introuvable à l'emplacement : {$ytdlp_path}");
        log_msg('ERROR', 'Installez-le depuis https://github.com/yt-dlp/yt-dlp');
        exit(1);
    }
    log_msg('INFO', 'yt-dlp version : ' . ($out[0] ?? '?'));
}

/**
 * Affiche un message de log avec couleur ANSI
 */
function log_msg(string $level, string $msg): void
{
    $colors = [
        'INFO'    => "\033[36m",   // cyan
        'SUCCESS' => "\033[32m",   // vert
        'WARN'    => "\033[33m",   // jaune
        'ERROR'   => "\033[31m",   // rouge
    ];
    $reset = "\033[0m";
    $color = $colors[$level] ?? '';
    $ts    = date('H:i:s');
    echo "{$color}[{$ts}][{$level}]{$reset} {$msg}" . PHP_EOL;
}

/**
 * Affiche l'aide
 */
function print_help(): void
{
    echo <<<HELP

  ┌─────────────────────────────────────────────────────────┐
  │          YouTube → Emby Downloader (PHP + yt-dlp)       │
  └─────────────────────────────────────────────────────────┘

  USAGE :
    php youtube_emby.php --url <URL> [options]
    php youtube_emby.php --playlist <URL> [options]

  OPTIONS :
    --url <URL>          URL d'une vidéo unique
    --playlist <URL>     URL d'une playlist complète
    --output-dir <dir>   Dossier de sortie (défaut : ./YouTube)
    --show-name <nom>    Nom de la série Emby (défaut : nom de la chaîne)
    --strm               Génère des .strm au lieu de télécharger
    --quality <q>        Qualité : best (défaut), 1080, 720, 480
    --format <f>         Format : mp4 (défaut) ou mkv
    --no-nfo             Ne génère pas les fichiers .nfo
    --no-thumb           Ne télécharge pas les miniatures
    --sleep <s>          Pause entre téléchargements (défaut : 3)
    --cookies <fichier>  Fichier cookies.txt (pour vidéos membres)
    --ytdlp-path <path>  Chemin vers yt-dlp (défaut : yt-dlp)
    --help               Affiche cette aide

  EXEMPLES :
    php youtube_emby.php --url "https://youtu.be/XXXX"
    php youtube_emby.php --playlist "https://youtube.com/playlist?list=PLxxx" --strm
    php youtube_emby.php --url "https://youtu.be/XXXX" --quality 1080 --show-name "MaChaîne"
    php youtube_emby.php --playlist "..." --format mkv --no-thumb --sleep 5

  STRUCTURE GÉNÉRÉE :
    ./YouTube/
    └── NomDeLaChaîne/
        ├── tvshow.nfo
        ├── poster.jpg
        ├── fanart.jpg
        ├── Season 2023/
        │   ├── poster.jpg
        │   ├── S2023E0315 - Titre de la vidéo.mp4
        │   ├── S2023E0315 - Titre de la vidéo.nfo
        │   ├── S2023E0315 - Titre de la vidéo-thumb.jpg
        │   └── S2023E0315 - Titre de la vidéo.fr.srt
        └── Season 2024/
            └── ...

HELP;
}
