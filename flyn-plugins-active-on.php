<?php

/**
 * @package Flynsarmy Plugins Active On
 * @version 1.2
 *
 * Plugin Name: Plugins Active On
 * Description: Lists sites a plugin/theme is active on in the network plugin/theme lists
 * Author: Flynsarmy
 * Version: 1.1
 * Author URI: http://www.flynsarmy.com
 *
 * Changes:
 * 2016-10-18 - Fixed wp_get_sites deprecation
 * 2014-08-12 - Added sites theme is active on in teh network and network-site theme lists
 */

if (defined('WP_ADMIN') && WP_ADMIN) {
    require_once __DIR__ . '/vendor/autoload.php';

    add_filter('plugin_row_meta', function ($plugin_meta, $plugin_file, $plugin_data, $status) {
        $plugin_sites = Flyn\FPAO\FPAO::instance()->getSitePluginsList();

        // If the plugin was found and it's active on 1+ sites
        if (isset($plugin_sites[$plugin_file]) && $plugin_sites[$plugin_file]) {
            $sites = $plugin_sites[$plugin_file];

            $html = "<br/>Active on: <ul>";
            foreach ($sites as $site) {
                $html .= "<li>{$site}</li>";
            }
            $html .= "</ul><br/>";

            $plugin_meta[] = $html;
        }
        return $plugin_meta;
    }, 10, 4);


    /*
     * Themes
     */

    add_filter('theme_row_meta', function ($theme_meta, $stylesheet, $theme, $status) {
        $theme_sites = Flyn\FPAO\FPAO::instance()->getSiteThemesList();

        // If the plugin was found and it's active on 1+ sites
        if (isset($theme_sites[$stylesheet]) && $theme_sites[$stylesheet]) {
            $sites = $theme_sites[$stylesheet];

            $html = "<br/>Active on: <ul>";
            foreach ($sites as $site) {
                $html .= "<li>{$site}</li>";
            }
            $html .= "</ul><br/>";

            $theme_meta[] = $html;
        }
        return $theme_meta;
    }, 10, 4);
}
