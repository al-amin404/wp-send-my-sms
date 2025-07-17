<?php 

function wpsms_order_sms_template_option_save() {
    if(!isset($_POST['wpsms_send_sms_nonce']) || !wp_verify_nonce($_POST['wpsms_send_sms_nonce'], 'wpsms_send_sms_nonce')) {
        wp_die('Error security varification');
    };

    $status_templates = function() {
        $templates = [];
        foreach(wc_get_order_statuses() as $status_slug => $status_label) {
            $templates['enable_sms_'.$status_slug] = isset($_POST['enable-sms-'. $status_slug]) ? '1' : '0';
            $templates[$status_slug] = wp_kses($_POST['wpsms-order-sms-template-' . $status_slug], ['br' => []]);
        };
        return $templates;
    };

    $order_sms_template_options = [
        'wpsms_order_sms_templates' => $status_templates()
    ];
    
    update_option('wpsms_sms_template_option', $order_sms_template_options);

    wp_safe_redirect( admin_url('admin.php?page=wpsms-send-wc-order-sms') );
    exit;
}

add_action("admin_post_wpsms_order_sms_template_option","wpsms_order_sms_template_option_save");

function wpsms_send_wc_order_sms($wpsms_template_option) {

    $active_plugins = get_option('active_plugins');
    if(!in_array('woocommerce/woocommerce.php', $active_plugins)) {
        echo "<div class='wrap'><h3>Install and Activate the Woocommerce plugin to use order notification SMS feature.</h3></div>";
        echo"<a href='". admin_url('plugin-install.php') ."' class='button button-primary' style='margin-top: 10px;'>Install Plugin</a>";
        exit;
    }

    $wpsms_template_option = get_option('wpsms_sms_template_option');
    $order_statuses = wc_get_order_statuses();

    ?>
    <div class="wrap">
        <h1>WC Order Status SMS Notification</h1>
        <form action="<?= admin_url('admin-post.php')?>" method="post">
            <table class="form-table">
                <tr>
                    <th scope="row">SMS template</th>
                    <td>
                        <?php 
                            foreach($order_statuses as $status_slug => $status_label) { ?>
                                <h4 style="margin-bottom: 0;"><?= $status_label ?></h4>
                                <p><input type="checkbox" name="enable-sms-<?= $status_slug ?>" <?php checked($wpsms_template_option['wpsms_order_sms_templates']['enable_sms_'.$status_slug] ?? '' , "1") ?>>Enable</p><br>
                                <textarea name="wpsms-order-sms-template-<?= $status_slug ?>" rows="5" cols="90" maxlength="255"><?= $wpsms_template_option['wpsms_order_sms_templates'][$status_slug] ?? '' ?></textarea><br>
                           <?php }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th></th>
                    <td>
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