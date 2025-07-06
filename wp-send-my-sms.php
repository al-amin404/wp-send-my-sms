<?php
/**
 * Plugin Name: WP Send My SMS
 * Description: A Simple plugin to send order status sms using "Send My SMS" API
 * Version: 1.0.0
 * Author: Al Amin
 * Text Domain: wp-send-my-sms
 */

 if ( ! defined("ABSPATH") ) exit;

function wpsms_admin_panel_menu() {
    add_menu_page(
        __('Send My SMS', 'wp-send-my-sms'),
        __('Send SMS','wp-send-my-sms'),
        'manage_options',
        'wpsms-send-my-sms',
        'wpsms_admin_panel_content',
        'dashicons-email'
    );

    add_submenu_page(
        'wpsms-send-my-sms',
        __('Send My SMS Settings','wp-send-my-sms'),
        __('Settings','wp-send-my-sms'),
        'manage_options',
        'wpsms-send-sms-settings',
        'wpsms_send_sms_admin_settings',
        2
    );
}

add_action('admin_menu','wpsms_admin_panel_menu');

function wpsms_admin_panel_content() {
    include_once (plugin_dir_path( __FILE__ ) .'wpsms-send.php');
}



function wpsms_send_sms_admin_settings() {
    include_once (plugin_dir_path(__FILE__). 'wpsms-settings.php');
}

function wpsms_api_options_save() {

    if(!isset($_POST['wpsms_wp_nonce']) || !wp_verify_nonce($_POST['wpsms_wp_nonce'], 'wpsms_wp_nonce')) {
        wp_die('Error security varification');
    }
    
    $api_user = sanitize_text_field($_POST['wpsms-api-user']);
    $api_key = sanitize_text_field($_POST['wpsms-api-key']);
    $enable_sms = isset($_POST['wpsms-enable-sms']) ? '1' : '0';

    $wpsms_options = [
        'api_username' => $api_user,
        'api_key' => $api_key,
        'enable_sms' => $enable_sms
    ];

    update_option('wpsms_api_options', $wpsms_options);

    wp_safe_redirect( admin_url('admin.php?page=wpsms-send-my-sms') );
}

add_action('admin_post_wpsms_api_options','wpsms_api_options_save');