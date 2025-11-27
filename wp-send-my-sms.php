<?php
/**
 * Plugin Name: WP Send My SMS
 * Plugin URI: https://github.com/al-amin404/wp-send-my-sms
 * Description: A Simple plugin to send order status sms notification using the "Send My SMS" API
 * Author: Al Amin
 * Author URI: https://github.com/al-amin404/
 * Version: 1.0.0
 * Requires PHP: V7.4
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-send-my-sms
 * Domain Path: /languages
 */

 if ( ! defined("ABSPATH") ) exit;

 //Register Send SMS in Admin Menu
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
        __('Wocommerce order SMS','wp-send-my-sms'),
        __('WC Order SMS','wp-send-my-sms'),
        'manage_options',
        'wpsms-send-wc-order-sms',
        'wpsms_send_wc_order_sms',
        2
    );

    add_submenu_page(
        'wpsms-send-my-sms',
        __('Send My SMS Settings','wp-send-my-sms'),
        __('Settings','wp-send-my-sms'),
        'manage_options',
        'wpsms-send-sms-settings',
        'wpsms_send_sms_admin_settings',
        3
    );
}

add_action('admin_menu','wpsms_admin_panel_menu');

function wpsms_admin_panel_content() {
    include_once (plugin_dir_path( __FILE__ ) .'includes/wpsms-send.php');
}

include_once (plugin_dir_path( __FILE__ ) .'includes/wpsms-order-notification.php');
include_once (plugin_dir_path( __FILE__ ) .'includes/wpsms-send-wc-order-sms.php');


function wpsms_send_sms_admin_settings() {
    include_once (plugin_dir_path(__FILE__). 'includes/wpsms-settings.php');
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
    exit;
}

add_action('admin_post_wpsms_api_options','wpsms_api_options_save');


//Send Custom SMS
function wpsms_send_custom_sms() {

    if(get_option('wpsms_api_options')['enable_sms'] !== '1') {
        wp_safe_redirect(admin_url('admin.php?page=wpsms-send-my-sms&msg=Error! Enable SMS API first'));
        exit;
    }

    if(!isset($_POST['wpsms_send_sms_nonce']) || !wp_verify_nonce($_POST['wpsms_send_sms_nonce'], 'wpsms_send_sms_nonce')) {
        wp_die('Error security varification');
        exit;
    }

    $customSmsText = wp_kses($_POST['wpsms-custom-text'], [
        'br' => []
    ]);
    $api_key = get_option('wpsms_api_options')['api_key'];
    $api_user = get_option('wpsms_api_options')['api_username'];
    $redirectUrl = 'wpsms-send-my-sms';
    $params = [
        'user' => $api_user,
        'key' => $api_key
    ];

    // Send WC custom SMS if OrderID exists
    if(isset($_POST['wc-orderId'])) {
        $id = absint($_POST['wc-orderId']);
        $order = wc_get_order($id);

        if(!$order) {
            wp_safe_redirect(admin_url('admin.php?page=wpsms-send-wc-order-sms&msg=Error! Order not found.'));
            exit;
        }

        $replacements = [
            '{firstName}' => $order->get_billing_first_name(),
            '{lastName}' => $order->get_billing_last_name(),
            '{orderId}' => $order->get_id(),
            '{orderStatus}' => wc_get_order_status_name($order->get_status()),
            '{orderTotal}' => $order->get_total(),
            '{billingMobile}' => $order->get_billing_phone()
        ];

        $finalText = strtr($customSmsText, $replacements);

        $params['to'] = $order->get_billing_phone();
        $params['msg'] = urlencode($finalText);

        $url = 'https://sendmysms.net/api.php';

        $resContent = wp_remote_get(add_query_arg($params, $url));

        $redirectUrl = 'wpsms-send-wc-order-sms';
    } 
    // Send Bulk SMS if checked
    elseif(isset($_POST['bulk-sms'])) {
        $numbers = explode(',', sanitize_text_field($_POST['wpsms-rv-mobile']));

        foreach($numbers as $receiverNumber) {

            $params['to'] = $receiverNumber;
            $params['msg'] = urlencode($customSmsText);

            $url = 'https://sendmysms.net/api.php';

            $resContent = wp_remote_get(add_query_arg($params, $url));
        }
    }

    // for Normal non-bulk SMS
    else {
        $params['to'] = sanitize_text_field($_POST['wpsms-rv-mobile']);
        $params['msg'] = urlencode($customSmsText);

        $url = 'https://sendmysms.net/api.php';
        $resContent = wp_remote_get(add_query_arg($params, $url));
    }//END if-else condition
    
    if (is_wp_error($resContent)) {
        $response = null;
    } else {
        $response = $resContent['body'];
        $response = json_decode($response, true);
    };

    $status = sanitize_text_field($response['status']);
    $msg = sanitize_text_field($response['response']);

    wp_safe_redirect(esc_url_raw(admin_url('admin.php?page='.$redirectUrl.'&msg='. $status. '! ' .$msg)));
    exit;
}

add_action('admin_post_wpsms_send_sms', 'wpsms_send_custom_sms');


//Load assets for WC order SMS pages
function wpsms_load_assets($hook_suffix) {
    if($hook_suffix != 'send-sms_page_wpsms-send-wc-order-sms' && $hook_suffix != 'send-sms_page_wpsms-send-my-sms' && $hook_suffix != 'toplevel_page_wpsms-send-my-sms' && $hook_suffix != 'send-sms_page_wpsms-send-sms-settings') {
        return;
    }

    wp_enqueue_style('wpsms-admin', plugin_dir_url(__FILE__) . 'assets/css/admin.css', [], '1.0.0', 'all');
}
add_action('admin_enqueue_scripts', 'wpsms_load_assets');