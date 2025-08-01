<?php 
if (! defined("ABSPATH")) exit;

function wpsms_send_order_update_sms($id, $from, $to) {
    if (!$id) {
        return;
    }
    $api_key = get_option('wpsms_api_options')['api_key'];
    $api_user = get_option('wpsms_api_options')['api_username'];
    $wpsms_status_options = get_option('wpsms_sms_template_option')['wpsms_order_sms_templates']['wc-'.$to];

    if(!($wpsms_status_options['enable_sms'] === '1' && $api_user && $api_key )){
        return;
    }

    $order = new WC_Order($id);
    $params = [
        'user' => $api_user,
        'key' => $api_key,
        'to' => $order->get_billing_phone()
    ];

    $sms_template = wp_kses($wpsms_status_options['template'], [
        'br' => []
    ]);
    
    $replacements = [
        '{firstName}' => $order->get_billing_first_name(),
        '{lastName}' => $order->get_billing_last_name(),
        '{orderId}' => $order->get_id(),
        '{orderStatus}' => wc_get_order_status_name($order->get_status()),
        '{orderTotal}' => $order->get_total(),
        '{billingMobile}' => $order->get_billing_phone()
    ];
    $finalText = strtr($sms_template, $replacements);
    $params['msg'] = urlencode($finalText);
    $url = 'https://sendmysms.net/api.php';
    wp_remote_get(add_query_arg($params, $url));
}

add_action( 'woocommerce_order_status_changed', 'wpsms_send_order_update_sms', 10, 3 );