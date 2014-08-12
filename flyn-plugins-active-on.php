<?php
/**
 * @package Flynsarmy Plugins Active On
 * @version 1.1
 *
 * Plugin Name: Plugins Active On
 * Description: Lists sites a plugin/theme is active on in the network plugin/theme lists
 * Author: Flynsarmy
 * Version: 1.1
 * Author URI: http://www.flynsarmy.com
 *
 * Changes:
 * 2014-08-12 - Added sites theme is active on in teh network and network-site theme lists
 */

/**
 * Retrieve a list of plugins and the sites they're enabled on
 *
 * @return array [
 *         plugin_path => [/, /foo/, /bar/, ...],
 *         plugin2_path => [Network Enabled],
 *         ...
 * ]
 */
$fpao_plugin_sites = array();
function fpao_get_site_plugins_list()
{
	global $fpao_plugin_sites;

	// Only regenerate if necessary
	if ( !$fpao_plugin_sites )
	{
		// Grab a list of sites
		$sites = wp_get_sites();

		foreach ( $sites as $site )
		{
			switch_to_blog($site['blog_id']);

			$active_plugins = get_option('active_plugins');
			foreach ( $active_plugins as $plugin_path )
			{
				// If we're network enabled, don't bother adding a site list
				if ( is_plugin_active_for_network( $plugin_path ) )
					$fpao_plugin_sites[$plugin_path] = array();
				else
					$fpao_plugin_sites[$plugin_path][] = "<a href='".admin_url('plugins.php')."'>".$site['path']."</a>";
			}

			restore_current_blog();
		}
	}

	return $fpao_plugin_sites;
}

add_filter( 'plugin_row_meta', function($plugin_meta, $plugin_file, $plugin_data, $status) {
	$plugin_sites = fpao_get_site_plugins_list();

	// If the plugin was found and it's active on 1+ sites
	if ( isset($plugin_sites[$plugin_file]) && $plugin_sites[$plugin_file] )
	{
		$sites = $plugin_sites[$plugin_file];

		$html = "<br/>Active on: <ul>";
		foreach ( $sites as $site )
			$html .= "<li>" . $site . "</li>";
		$html .= "</ul><br/>";

		$plugin_meta[] = $html;
	}
	return $plugin_meta;
}, 10, 4);


/*
 * Themes
 */

$fpao_theme_sites = array();
function fpao_get_site_themes_list()
{
	global $fpao_theme_sites;

	// Only regenerate if necessary
	if ( !$fpao_theme_sites )
	{
		// $themes = wp_get_themes();
		$network_themes = array_keys(wp_get_themes(['allowed' => 'network']));
		$sites = wp_get_sites();

		foreach ( $sites as $site )
		{
			$themes = WP_Theme::get_allowed_on_site($site['blog_id']);

			foreach ( $themes as $stylesheet_dir => $enabled )
				if ( !in_array($stylesheet_dir, $network_themes) )
					$fpao_theme_sites[$stylesheet_dir][] = "<a href='".admin_url('themes.php')."'>".$site['path']."</a>";
		}
	}

	return $fpao_theme_sites;
}

add_filter( 'theme_row_meta', function($theme_meta, $stylesheet, $theme, $status) {
	$theme_sites = fpao_get_site_themes_list();

	// If the plugin was found and it's active on 1+ sites
	if ( isset($theme_sites[$stylesheet]) && $theme_sites[$stylesheet] )
	{
		$sites = $theme_sites[$stylesheet];

		$html = "<br/>Active on: <ul>";
		foreach ( $sites as $site )
			$html .= "<li>" . $site . "</li>";
		$html .= "</ul><br/>";

		$theme_meta[] = $html;
	}
	return $theme_meta;
}, 10, 4);