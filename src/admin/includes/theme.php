<?php
// Validate configurable brand colors and choose a WCAG-readable foreground.
$admin_brand = get_setting('logo_secondary_color', '#b42332');
if (!preg_match('/^#[0-9a-f]{6}$/i', $admin_brand)) $admin_brand = '#b42332';
$admin_rgb = array_map('hexdec', str_split(substr($admin_brand, 1), 2));
$admin_linear = array_map(static function ($channel) {
    $channel /= 255;
    return $channel <= 0.04045 ? $channel / 12.92 : pow(($channel + 0.055) / 1.055, 2.4);
}, $admin_rgb);
$admin_luminance = $admin_linear[0] * .2126 + $admin_linear[1] * .7152 + $admin_linear[2] * .0722;
$admin_foreground = $admin_luminance > .179 ? '#000000' : '#ffffff';
$admin_hover = '#' . implode('', array_map(static function ($channel) use ($admin_foreground) {
    $channel = $admin_foreground === '#ffffff' ? $channel * .85 : $channel + (255 - $channel) * .15;
    return str_pad(dechex((int)round($channel)), 2, '0', STR_PAD_LEFT);
}, $admin_rgb));
// Text links on white require a dark brand variant even when the logo is pale.
$admin_link = $admin_luminance <= .183 ? $admin_brand : '#9f2230';
?>
<style>
    body.admin-app {
        --admin-brand: <?= $admin_brand ?>;
        --admin-brand-hover: <?= $admin_hover ?>;
        --admin-brand-text: <?= $admin_foreground ?>;
        --admin-link: <?= $admin_link ?>;
    }
</style>
