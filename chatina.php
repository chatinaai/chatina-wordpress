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

        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('wp_enqueue_scripts', [$this, 'hooks']);
        add_action('admin_enqueue_scripts', [$this, 'admin_assets']);

        // after activation redirect to settings page
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
        include plugin_dir_path(__FILE__) . 'views/setting.php';
    }

    function admin_assets()
    {
        $screen = get_current_screen();
        if ($screen->id === 'settings_page_chatina') {
            wp_enqueue_style('chatina-admin', plugin_dir_url(__FILE__) . 'assets/css/chatina.css', [], '1.0.0');
        }
    }

    public function hooks()
    {
        add_action('wp_footer', function () {
            $api_key = sanitize_text_field(get_option('chatina_api_key'));
            if (!$api_key) return;
            echo '<script>
window.addEventListener("load",(function(){const t="' . esc_html($api_key) . '";window.chatina={bId:t};var e=document.createElement("div");e.id="chatina-root",document.body.appendChild(e);var n=document.createElement("link");n.rel="stylesheet",n.href="https://cdn.chatina.ai/static/widget.css",n.crossOrigin="anonymous",document.head.appendChild(n);var a=document.createElement("script");a.src="https://cdn.chatina.ai/static/widget.js",a.crossOrigin="anonymous",document.head.appendChild(a)}));
</script>';
        });
    }
}

new Chatina();
