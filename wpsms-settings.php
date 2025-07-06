<?php
 if ( ! defined("ABSPATH") ) exit;

$wpsms_options = get_option('wpsms_api_options');

?>

<div class="wrap">
    <h1>SMS API Settings</h1><br>
    <form action="<?= admin_url('admin-post.php') ?>" method="post">
        <table class="form-table">
            <tr>
                <th scope="row">API Username</th>
                <td><input type="text" name="wpsms-api-user" id="wpsms-api-username" value="<?= $wpsms_options['api_username']?>" required/></td>
            </tr>
            <tr>
                <th scope="row">API KEY</th>
                <td><input type="text" name="wpsms-api-key" id="wpsms-api-key" value="<?= $wpsms_options['api_key']?>" required/></td>
            </tr>
            <tr>
                <th>Enable SMS</th>
                <td><input type="checkbox" name="wpsms-enable-sms" id="toggle-sms" <?php checked($wpsms_options['enable_sms'], "1") ?>></td>
            </tr>
            <tr>
                <input type="hidden" name="action" value="wpsms_api_options">
                <?php wp_nonce_field('wpsms_wp_nonce', 'wpsms_wp_nonce')?>
                <td>
                    <button class="button button-primary" type="submit">Save Settings</button>
                </td>
            </tr>
        </table>
    </form>
</div>