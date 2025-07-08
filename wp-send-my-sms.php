<?php
/**
 * Plugin Name: WP Send My SMS
 * Description: A Simple plugin to send order status sms using "Send My SMS" API
 * Author: Al Amin
 * Version: 1.0.0
 * License: GPL-2
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


//Save SMS settings in options table
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


//Send Custom SMS
function wpsms_send_custom_sms() {

    if(!isset($_POST['wpsms_send_sms_nonce']) || !wp_verify_nonce($_POST['wpsms_send_sms_nonce'], 'wpsms_send_sms_nonce')) {
        wp_die('Error security varification');
    }

    $receiverNumber = sanitize_text_field($_POST['wpsms-rv-mobile']);
    $customSmsText = sanitize_text_field($_POST['wpsms-custom-text']);
    $api_key = get_option('wpsms_api_options')['api_key'];
    $api_user = get_option('wpsms_api_options')['api_username'];

    $params = [
        'user' => $api_user,
        'key' => $api_key,
        'to' => $receiverNumber,
        'msg' => urlencode($customSmsText)
    ];
    $url = 'https://sendmysms.net/api.php';

    $resContent = wp_remote_get(add_query_arg($params, $url));

    if (is_wp_error($resContent)) {
        $response = null;
    } else {
        $response = $resContent['body'];
        $response = json_decode($response, true);
    }

    $status = $response['status'];
    $msg = $response['response'];

    if($status == 'OK') {
        wp_safe_redirect(admin_url('admin.php?page=wpsms-send-my-sms&msg='. $msg));
    } else {
        wp_safe_redirect(admin_url('admin.php?page=wpsms-send-my-sms&msg='. $msg));
    }
}

add_action('admin_post_wpsms_send_sms', 'wpsms_send_custom_sms');