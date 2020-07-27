<?php

namespace Flyn\FPAO;

use Flyn\FPAO\Traits\Singleton;
use WP_Theme;

class FPAO
{
    use Singleton;

    public array $plugin_sites = [];
    public array $theme_sites = [];
    
    /**
     * Retrieve a list of plugins and the sites they're enabled on
     *
     * @return array [
     *         plugin_path => [/, /foo/, /bar/, ...],
     *         plugin2_path => [Network Enabled],
     *         ...
     * ]
     */
    public function getSitePluginsList(): array
    {
        // Only regenerate if necessary
        if (!count($this->plugin_sites)) {
            // Grab a list of sites
            $sites = get_sites();

            foreach ($sites as $site) {
                switch_to_blog($site->blog_id);

                $active_plugins = get_option('active_plugins');
                foreach ($active_plugins as $plugin_path) {
                    if (!isset($this->plugin_sites[$plugin_path])) {
                        $this->plugin_sites[$plugin_path] = [];
                    }

                    // If we're network enabled, don't bother adding a site list
                    if (is_plugin_active_for_network($plugin_path)) {
                        $this->plugin_sites[$plugin_path] = [];
                    } else {
                        $this->plugin_sites[$plugin_path][] =
                            "<a href='" . admin_url('plugins.php') . "'>" .
                            $site->path .
                            "</a>";
                    }
                }

                restore_current_blog();
            }
        }

        return $this->plugin_sites;
    }

    public function getSiteThemesList()
    {
        // Only regenerate if necessary
        if (!count($this->theme_sites)) {
            // $themes = wp_get_themes();
            $network_themes = array_keys(wp_get_themes(['allowed' => 'network']));
            $sites = get_sites();

            foreach ($sites as $site) {
                $themes = WP_Theme::get_allowed_on_site($site->blog_id);

                foreach ($themes as $stylesheet_dir => $enabled) {
                    if (!in_array($stylesheet_dir, $network_themes)) {
                        if (!isset($this->theme_sites[$stylesheet_dir])) {
                            $this->theme_sites[$stylesheet_dir] = [];
                        }

                        $this->theme_sites[$stylesheet_dir][] =
                            "<a href='" . admin_url('themes.php') . "'>" .
                            $site->path .
                            "</a>";
                    }
                }
            }
        }

        return $this->theme_sites;
    }
}
