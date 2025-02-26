<?php

/**
 * Plugin Name: Chatina Ai – Live Chat Online Platform
 * Plugin URI: https://chatina.ai/lab/wordpress/
 * Description: Add online chat to your website
 * Version: 1.0
 * Author: Ertano
 * Author URI: https://ertano.com
 * License: GPLv2 or later
 * Text Domain: chatina
 * Domain Path: /languages
 */


namespace Chatina;

if (!defined('ABSPATH')) exit;

if (!defined('CHATINA_PLUGIN_FILE')) define('CHATINA_PLUGIN_FILE', __FILE__);
if (!defined('CHATINA_PLUGIN_URL')) define('CHATINA_PLUGIN_URL', plugin_dir_url(__FILE__));

class Chatina
{
    public function __construct()
    {
        load_plugin_textdomain('chatina', false, dirname(plugin_basename(__FILE__)) . '/languages/');

        add_action('wp_footer', [$this, 'add_chatina_root']);

        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('wp_enqueue_scripts', [$this, 'register_assets']);
        add_action('admin_enqueue_scripts', [$this, 'admin_assets']);

        // redirect to settings page after activation
        register_activation_hook(__FILE__, function () {
            add_option('chatina_redirect', true);
        });

        add_action('admin_init', function () {
            if (get_option('chatina_redirect')) {
                delete_option('chatina_redirect');
                wp_redirect(admin_url('options-general.php?page=chatina'));
                exit;
            }
        });

        add_filter('plugin_action_links', [$this, 'filter_plugin_action_links']);

        add_action('admin_init', [$this, 'handle_chatina_requests']);
    }

    public function filter_plugin_action_links($links)
    {
        $links[] = '<a href="' . admin_url('options-general.php?page=chatina') . '">' . __('Settings', 'chatina') . '</a>';
        return $links;
    }

    public function add_admin_menu()
    {
        add_submenu_page('options-general.php', __('Chatina', 'chatina'), __('Chatina', 'chatina'), 'manage_options', 'chatina', [$this, 'chatina_page']);
    }

    public function chatina_page()
    {
        include_once plugin_dir_path(__FILE__) . 'views/setting.php';
    }


    public function handle_chatina_requests()
    {
        if (isset($_GET['apiKey']) && isset($_GET['_wpnonce'])) {
            $_nonce = sanitize_text_field(wp_unslash($_GET['_wpnonce']));
            if (!wp_verify_nonce($_nonce, 'chatina_save_api_key')) {
                wp_die('Security check');
            }

            $api_key = sanitize_text_field(wp_unslash($_GET['apiKey']));
            update_option('chatina_api_key', $api_key);

            wp_redirect(admin_url('admin.php?page=chatina&message=success'));
            exit;
        }

        if (isset($_GET['disconnect']) && isset($_GET['_wpnonce'])) {
            $_nonce = sanitize_text_field(wp_unslash($_GET['_wpnonce']));
            if (!wp_verify_nonce($_nonce, 'chatina_disconnect')) {
                wp_die('Security check');
            }

            delete_option('chatina_api_key');

            wp_redirect(admin_url('admin.php?page=chatina&message=success'));
            exit;
        }
    }

    function admin_assets()
    {
        $screen = get_current_screen();
        if ($screen->id === 'settings_page_chatina') {
            wp_enqueue_style('chatina-admin', plugin_dir_url(__FILE__) . 'assets/css/chatina.css', [], '1.0.0');
        }
    }

    public static function register_assets()
    {
        wp_enqueue_script('chatina', plugin_dir_url(__FILE__) . 'assets/js/chatina.js', [], '1.0.0', true);
        wp_localize_script('chatina', 'chatina', [
            'bId' => sanitize_text_field(get_option('chatina_api_key')),
        ]);
    }

    public static function add_chatina_root()
    {
        echo '<div id="chatina-root"></div>';
    }
}

new Chatina();
