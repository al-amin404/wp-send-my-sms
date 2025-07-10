<?php 

function wpsms_order_sms_template_option_save() {
    if(!isset($_POST['wpsms_send_sms_nonce']) || !wp_verify_nonce($_POST['wpsms_send_sms_nonce'], 'wpsms_send_sms_nonce')) {
        wp_die('Error security varification');
    }

    $order_sms_template_options = [
        'enable_wc_order_sms' => isset($_POST['enable-order-sms']) ? '1' : '0',
        'wpsms_order_sms_template' => sanitize_text_field($_POST['wpsms-order-sms-template'])
    ];

    update_option('wpsms_sms_template_option', $order_sms_template_options);

    wp_safe_redirect( admin_url('admin.php?page=wpsms-send-wc-order-sms') );
    exit;
}

add_action("admin_post_wpsms_order_sms_template_option","wpsms_order_sms_template_option_save");

function wpsms_send_wc_order_sms($wpsms_template_option) { 
    $wpsms_template_option = get_option('wpsms_sms_template_option');
    ?>
    <div class="wrap">
        <h1>WC Order Status SMS Notification</h1>
        <form action="<?= admin_url('admin-post.php')?>" method="post">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        Enable order status SMS:
                    </th>
                    <td>
                        <input type="checkbox" name="enable-order-sms" id="toggle-sms" <?php checked($wpsms_template_option['enable_wc_order_sms'] ?? '' , "1") ?>>
                    </td>
                </tr>
                <tr>
                    <th scope="row">SMS template</th>
                    <td>
                        <textarea name="wpsms-order-sms-template" rows="3" cols="66" maxlength="255"><?= $wpsms_template_option['wpsms_order_sms_template'] ?? '' ?></textarea><br>
                        <input type="hidden" name="action" value="wpsms_order_sms_template_option">
                        <?php wp_nonce_field('wpsms_send_sms_nonce', 'wpsms_send_sms_nonce') ?>
                        <button type="submit" class="button button-primary" style="margin-top: 10px;">Save</button>
                    </td>
                </tr>
            </table>
        </form>
        
        <div style="margin-top:30px;">
            <h2>Send a custom order SMS</h2>
            <form action="<?= admin_url('admin-post.php')?>" method="post">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            Order ID:
                        </th>
                        <td>
                            <input type="number" name="wc-orderId" required><br>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            Receiver Mobile:
                        </th>
                        <td>
                            <input type="tel" name="wpsms-rv-mobile" required><br>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Message</th>
                        <td>
                            <textarea name="wpsms-custom-text" rows="5" cols="33" maxlength="255"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <input type="hidden" name="action" value="wpsms_wc_custom_order_sms">
                        <?php wp_nonce_field('wpsms_send_sms_nonce', 'wpsms_send_sms_nonce') ?>
                        <td><button type="submit" class="button button-primary">Send SMS</button></td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
<?php }