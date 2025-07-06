<?php
if (! defined("ABSPATH")) exit;

$wpsms_options = get_option('wpsms_api_options');

?>

<div class="wrap">
    <h1>Send SMS</h1><br>
    
        <table class="form-table">
            <tr>
                <th scope="row">API Username:</th>
                <td><?= $wpsms_options['api_username'] ?></td>
            </tr>
            <tr>
                <th scope="row">API KEY:</th>
                <td><?= $wpsms_options['api_key'] ? '* * * * * * * * * *' : '' ?></td>
                
            </tr>
            <tr>
                <th>Enable SMS</th>
                <td><input type="checkbox" disabled <?php checked($wpsms_options['enable_sms'], "1") ?>></td>
            </tr>
        </table>
        <br>
        <br>

        <div>
            <h2>Write Message</h2>
            <form action="">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            Receiver Mobile:
                        </th>
                        <td><input type="tel" maxlength="11" name="wpsms-rv-mobile" required></td>
                    </tr>
                    <tr>
                        <th scope="row">Message</th>
                        <td><textarea name="wpsms-text-message" maxlength="255"></textarea></td>
                    </tr>
                </table>
            </form>
        </div>

</div>