<?php
if (! defined("ABSPATH")) exit;

$wpsms_options = get_option('wpsms_api_options');

?>

<div class="wrap">
    <h1><?= __('Send SMS', 'wp-send-my-sms') ?></h1><br>
    
        <table class="form-table">
            <tr>
                <th scope="row"><?= __('API Username:', 'wp-send-my-sms') ?></th>
                <td><?= $wpsms_options['api_username'] ?></td>
            </tr>
            <tr>
                <th scope="row"><?= __('API Key:', 'wp-send-my-sms') ?></th>
                <td><?= $wpsms_options['api_key'] ? '* * * * * * * * * *' : '' ?></td>
                
            </tr>
            <tr>
                <th><?= __('Enable SMS', 'wp-send-my-sms') ?></th>
                <td><input type="checkbox" disabled <?php checked($wpsms_options['enable_sms'], "1") ?>></td>
            </tr>
        </table>
        <br>
        <br>

        <div>
            <h2><?= __('Write a Message', 'wp-send-my-sms') ?></h2>
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
                            <?= __('Receiver Mobile:', 'wp-send-my-sms') ?>
                        </th>
                        <td>
                            <input type="tel" name="wpsms-rv-mobile" required><br>
                            <div style="margin-top: 10px ;">
                                <input type="checkbox" name="bulk-sms" id=""><span><?= __('Send Bulk SMS', 'wp-send-my-sms') ?></span><br>
                                <p style="font-size: 12px; margin-top: 10px;"><?= __('Use <code>,</code> to seperate each number. (ex. <code>01912345678, 01524536578, ...</code> )', 'wp-send-my-sms') ?></p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?= __('Message', 'wp-send-my-sms') ?></th>
                        <td><textarea name="wpsms-custom-text" maxlength="255"></textarea></td>
                    </tr>
                    <tr>
                        <input type="hidden" name="action" value="wpsms_send_sms">
                        <?php wp_nonce_field('wpsms_send_sms_nonce', 'wpsms_send_sms_nonce') ?>
                        <td><button type="submit" class="button button-primary"><?= __('Send Message', 'wp-send-my-sms') ?></button></td>
                    </tr>
                </table>
            </form>
        </div>

</div>