<?php
/**
 * Plugin Name: Bridge-Tailor AI Business Diagnostic Connector
 * Plugin URI:  https://diagnostics.bridge-tailor.com
 * Description: Connects www.bridge-tailor.com (WordPress / Astra / Elementor) to the Bridge-Tailor AI Business Diagnostic Platform hosted at diagnostics.bridge-tailor.com. Provides the [bridge_tailor_diagnostic] and [bridge_tailor_diagnostic_banner] shortcodes and automatically upgrades external "Take our businessHealth Test" links to the proprietary diagnostic subdomain.
 * Version:     1.0.0
 * Author:      Bridge-Tailor Consulting
 * Author URI:  https://bridge-tailor.com
 */

if (! defined('ABSPATH')) {
    exit;
}

define('BT_DIAGNOSTIC_BASE_URL', getenv('BT_DIAGNOSTIC_URL') ?: 'https://diagnostics.bridge-tailor.com');

/**
 * Shortcode 1: [bridge_tailor_diagnostic module="" label="Take our Business Health Test"]
 * Renders a flat, solid-color button matching Bridge-Tailor's brand palette (#f17e01 / #2c2927).
 */
add_shortcode('bridge_tailor_diagnostic', function ($atts) {
    $a = shortcode_atts([
        'module' => '',
        'label'  => 'Take our Business Health Test',
        'theme'  => 'amber', // amber | dark
    ], $atts);

    $url = rtrim(BT_DIAGNOSTIC_BASE_URL, '/') . '/assess';
    if (! empty($a['module'])) {
        $url .= '?target=' . rawurlencode($a['module']);
    }

    $bg = $a['theme'] === 'dark' ? '#2c2927' : '#f17e01';
    $fg = $a['theme'] === 'dark' ? '#ffffff' : '#141210';

    return sprintf(
        '<a href="%s" class="bt-diagnostic-btn" style="display:inline-block;background-color:%s;color:%s;padding:10px 20px;font-family:\'Poppins\',sans-serif;font-size:12.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;text-decoration:none;border:1px solid #141210;border-radius:0;">%s &rarr;</a>',
        esc_url($url),
        esc_attr($bg),
        esc_attr($fg),
        esc_html($a['label'])
    );
});

/**
 * Shortcode 2: [bridge_tailor_diagnostic_banner]
 * Renders a compact, solid-color executive callout section for Elementor / Gutenberg pages.
 */
add_shortcode('bridge_tailor_diagnostic_banner', function ($atts) {
    $a = shortcode_atts([
        'title' => 'Evaluate Your Business Across 5 Core Pillars',
        'sub'   => 'Corporate Strategy & Governance · Financial Health · Operational Excellence · Market Access · Human Capital',
        'cta'   => 'Start AI Business Diagnostic',
    ], $atts);

    $url = rtrim(BT_DIAGNOSTIC_BASE_URL, '/') . '/assess';

    return '
    <div style="background-color:#2c2927;color:#ffffff;border-left:5px solid #f17e01;padding:18px 22px;font-family:\'Poppins\',sans-serif;display:flex;justify-content:space-between;align-items:center;gap:18px;flex-wrap:wrap;margin:16px 0;">
        <div>
            <div style="font-size:10.5px;font-weight:800;letter-spacing:0.14em;color:#f17e01;text-transform:uppercase;">BRIDGE-TAILOR AI BUSINESS DIAGNOSTIC ENGINE</div>
            <div style="font-family:\'DM Serif Display\',Georgia,serif;font-size:22px;color:#ffffff;margin-top:3px;">' . esc_html($a['title']) . '</div>
            <div style="font-size:12px;color:#d6cdc2;margin-top:3px;">' . esc_html($a['sub']) . '</div>
        </div>
        <a href="' . esc_url($url) . '" style="background-color:#f17e01;color:#141210;padding:10px 20px;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:0.05em;text-decoration:none;border:1px solid #f17e01;white-space:nowrap;">
            ' . esc_html($a['cta']) . ' &rarr;
        </a>
    </div>';
});

/**
 * Automatic Link Upgrader:
 * Finds existing "Take our businessHealth Test" links in WordPress header/page content
 * and points them directly to diagnostics.bridge-tailor.com/assess.
 */
add_action('wp_footer', function () {
    $targetUrl = esc_js(rtrim(BT_DIAGNOSTIC_BASE_URL, '/') . '/assess');
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('a').forEach(function(link) {
            var txt = (link.textContent || '').toLowerCase();
            if (txt.indexOf('health test') !== -1 || txt.indexOf('businesshealth') !== -1) {
                link.href = '{$targetUrl}';
            }
        });
    });
    </script>";
});
