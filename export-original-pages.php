<?php

$root = file_get_contents(glob(__DIR__.'/*.html')[0]);
function bundleSection(string $html, string $type): ?string {
    $tag = '<script type="__bundler/'.$type.'">';
    $start = strpos($html, $tag);
    if ($start === false) return null;
    $start += strlen($tag);
    $end = strpos($html, '</script>', $start);
    return trim(substr($html, $start, $end-$start));
}
function decodeItem(array $item): string {
    $bytes = base64_decode($item['data']);
    return $item['compressed'] ? gzdecode($bytes) : $bytes;
}
$rootPages = json_decode(bundleSection($root, 'manifest'), true);
$pages = ['home', 'services', 'about', 'contact', 'gallery', 'project'];
$mimeExtensions = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/svg+xml'=>'svg','font/woff2'=>'woff2','text/javascript'=>'js','text/css'=>'css'];
$assetDirectory = __DIR__.'/backend/public/original-assets';
$pageDirectory = __DIR__.'/backend/public/original-design';
@mkdir($assetDirectory, 0777, true);
foreach (array_values($rootPages) as $index => $page) {
    $bundle = decodeItem($page);
    $assets = json_decode(bundleSection($bundle, 'manifest'), true);
    $template = json_decode(bundleSection($bundle, 'template'), true);
    if (!$assets || !$template) throw new RuntimeException('Bundle cannot be decoded: '.$pages[$index]);
    foreach ($assets as $id => $asset) {
        $extension = $mimeExtensions[$asset['mime']] ?? null;
        if (!$extension) throw new RuntimeException('Unknown asset type '.$asset['mime']);
        $filename = $id.'.'.$extension;
        $target = $assetDirectory.'/'.$filename;
        if (!file_exists($target)) file_put_contents($target, decodeItem($asset));
        $optimized = $filename;
        if (function_exists('imagewebp') && in_array($extension, ['jpg', 'png'], true) && filesize($target) > 120 * 1024) {
            $webp = $assetDirectory.'/'.$id.'.webp';
            if (!file_exists($webp)) {
                $image = @imagecreatefromstring(file_get_contents($target));
                if ($image) {
                    imagewebp($image, $webp, 86);
                    imagedestroy($image);
                }
            }
            if (file_exists($webp) && filesize($webp) < filesize($target) * 0.9) {
                $optimized = $id.'.webp';
            }
        }
        $template = str_replace($id, '/original-assets/'.$optimized, $template);
    }
    $libraries = '<script src="/original-assets/d836ff88-d0ad-4aef-9c37-1be970bf9285.js"></script>'
        .'<script src="/original-assets/9e497946-1e95-4747-b509-3387f4f90c8b.js"></script>';
    $template = str_replace('<head>', '<head>'.$libraries, $template);
    // These references were not included in the supplied HTML export.
    // Use one local fallback to avoid hundreds of failing HTTP requests.
    $template = preg_replace('~/_blob/[a-f0-9]{32}~', '/original-assets/unavailable.svg', $template);
    $template = str_replace('</head>', '<link rel="stylesheet" href="/css/original-bridge.css"><script defer src="/js/original-bridge.js"></script><script defer src="/js/site-content.js"></script></head>', $template);
    file_put_contents($pageDirectory.'/'.$pages[$index].'-standalone.html', $template);
    echo $pages[$index].' '.strlen($template)." bytes\n";
}
file_put_contents($assetDirectory.'/unavailable.svg', '<svg xmlns="http://www.w3.org/2000/svg" width="800" height="450"><rect width="100%" height="100%" fill="#01173c"/></svg>');
