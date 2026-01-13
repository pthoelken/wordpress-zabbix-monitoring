<?php
/**
 * Plugin Name: Zabbix WordPress Update Status
 * Description: Read-only REST endpoint for Zabbix to query WordPress update status.
 */

add_action('rest_api_init', function () {

    register_rest_route('zabbix/v1', '/updates', [
        'methods'  => 'GET',

        'permission_callback' => '__return_true',

        'callback' => function () {

            try {
                require_once ABSPATH . 'wp-admin/includes/update.php';
                require_once ABSPATH . 'wp-admin/includes/upgrade.php';

                if (function_exists('wp_version_check')) {
                    wp_version_check();
                }
                if (function_exists('wp_update_plugins')) {
                    wp_update_plugins();
                }
                if (function_exists('wp_update_themes')) {
                    wp_update_themes();
                }

                $core   = get_site_transient('update_core');
                $plugin = get_site_transient('update_plugins');
                $theme  = get_site_transient('update_themes');

                // Core Updates
                $core_updates = 0;
                if (!empty($core->updates) && is_array($core->updates)) {
                    foreach ($core->updates as $update) {
                        if (
                            isset($update->response) &&
                            $update->response === 'upgrade'
                        ) {
                            $core_updates++;
                        }
                    }
                }

                // Plugin Updates
                $plugin_updates = !empty($plugin->response)
                    ? count($plugin->response)
                    : 0;

                // Theme Updates
                $theme_updates = !empty($theme->response)
                    ? count($theme->response)
                    : 0;

                // DB Update
                $db_update_required = (
                    function_exists('db_requires_upgrade') &&
                    db_requires_upgrade()
                ) ? 1 : 0;

                // Translation Updates (Core + Plugins + Themes)
                $translation_updates = 0;
                if (!empty($core->translations)) {
                    $translation_updates += count($core->translations);
                }
                if (!empty($plugin->translations)) {
                    $translation_updates += count($plugin->translations);
                }
                if (!empty($theme->translations)) {
                    $translation_updates += count($theme->translations);
                }

                return [
                    'core_updates'        => $core_updates,
                    'plugin_updates'      => $plugin_updates,
                    'theme_updates'       => $theme_updates,
                    'db_update_required'  => $db_update_required,
                    'translation_updates' => $translation_updates
                ];

            } catch (Throwable $e) {
                return new WP_Error(
                    'zabbix_internal_error',
                    $e->getMessage(),
                    ['status' => 500]
                );
            }
        }
    ]);

});