<?php 
if (! defined("ABSPATH")) exit;
function wpsms_order_sms_template_option_save() {
    if(!isset($_POST['wpsms_send_sms_nonce']) || !wp_verify_nonce($_POST['wpsms_send_sms_nonce'], 'wpsms_send_sms_nonce')) {
        wp_die('Error security varification');
    };
    
    $templates = [];

        foreach(wc_get_order_statuses() as $status_slug => $status_label) {
            $templates[$status_slug] = [
                'enable_sms' => isset($_POST['enable-sms-'. $status_slug]) ? '1' : '0',
                'template' => wp_kses($_POST['wpsms-order-sms-template-' . $status_slug], ['br' => []]) ?? ''
            ];
        };
    $order_sms_template_options = [
        'wpsms_order_sms_templates' => $templates
    ];
    
    update_option('wpsms_sms_template_option', $order_sms_template_options);

    wp_safe_redirect( admin_url('admin.php?page=wpsms-send-wc-order-sms') );
    exit;
}

add_action("admin_post_wpsms_order_sms_template_option","wpsms_order_sms_template_option_save");

function wpsms_send_wc_order_sms($wpsms_template_option) {

    $active_plugins = get_option('active_plugins');
    if(!in_array('woocommerce/woocommerce.php', $active_plugins)) {
        echo "<div class='wrap'><h3>". esc_html__('Install and Activate the Woocommerce plugin to use order notification SMS feature.', 'wp-send-my-sms') ."</h3></div>";
        echo"<a href='". admin_url('plugin-install.php') ."' class='". esc_attr('button button-primary') ."' style='". esc_attr('margin-top: 10px') ."'>". __('Install Plugin', 'wp-send-my-sms') ."</a>";
        exit;
    }
    
    $wpsms_template_option = get_option('wpsms_sms_template_option')['wpsms_order_sms_templates'];
    $order_statuses = wc_get_order_statuses();

    ?>
    <div class="wrap">
        <form action="<?= esc_url(admin_url('admin-post.php'))?>" method="post">
            <table class="form-table">
                <tr>
                    <th scope="row"><?= esc_html__('SMS templates', 'wp-send-my-sms') ?></th>
                    <td>
                        <?php 
                            foreach($order_statuses as $status_slug => $status_label) { ?>
                                <h4 style="margin-bottom: 0;"><?= esc_html($status_label) ?></h4>
                                <p><input type="checkbox" name="<?= esc_attr('enable-sms-'.$status_slug) ?>" <?php checked($wpsms_template_option[$status_slug]['enable_sms'] ?? '' , "1") ?>><?= __('Enable', 'wp-send-my-sms') ?></p><br>
                                <textarea name="<?= esc_attr('wpsms-order-sms-template-'.$status_slug) ?>" rows="5" cols="90" maxlength="255"><?= esc_html($wpsms_template_option[$status_slug]['template']) ?? '' ?></textarea><br>
                           <?php }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th></th>
                    <td>
                        <input type="hidden" name="action" value="wpsms_order_sms_template_option">
                        <?php wp_nonce_field('wpsms_send_sms_nonce', 'wpsms_send_sms_nonce') ?>
                        <button type="submit" class="button button-primary" style="margin-top: 10px;"><?= esc_html__('Save', 'wp-send-my-sms') ?></button>
                    </td>
                </tr>
            </table>
        </form>
        
        <div style="margin-top:30px;">
            <h2><?= esc_html__('Send a custom order SMS', 'wp-send-my-sms') ?></h2>
            <div <?= !empty($_GET['msg']) ? 
                    "style='".esc_attr('background-color:#fff;
                        border:1px solid #c3c4c7;
                        border-left-width:4px;
                        border-left-color:#0a875a;
                        box-shadow:0 1px 1px rgba(0,0,0,.04);
                        padding:0 15px 0 5px;
                        width: max-content;')."'" : ''  
                ?> >
                    <p><?= $_GET['msg'] ?? ''  ?></p>
            </div>
            <form action="<?= esc_url(admin_url('admin-post.php'))?>" method="post">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <?= esc_html__('Order ID:', 'wp-send-my-sms') ?>
                        </th>
                        <td>
                            <input type="text" name="wc-orderId" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?= esc_html__('Message:', 'wp-send-my-sms') ?></th>
                        <td>
                            <textarea name="wpsms-custom-text" rows="5" cols="33" maxlength="255" required></textarea>
                        </td>
                    </tr>
                    <tr>
                        <input type="hidden" name="action" value="wpsms_send_sms">
                        <?php wp_nonce_field('wpsms_send_sms_nonce', 'wpsms_send_sms_nonce') ?>
                        <td><button type="submit" class="button button-primary"><?= esc_html__('Send SMS', 'wp-send-my-sms') ?></button></td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
<?php }