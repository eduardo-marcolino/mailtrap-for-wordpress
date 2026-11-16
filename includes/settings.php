<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<div class="wrap">
    <?php include __DIR__ . '/page-header.php'; ?>
    <?php settings_errors(); ?>
    <form action="options.php" method="post">
        <?php settings_fields( 'mailtrap-wp-settings' ); ?>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php esc_html_e( 'Enable Mailtrap SMTP', 'mailtrap-for-wp' ); ?></th>
                <td><label><input type="checkbox" name="mailtrap_wp_config[enabled]" value="1" <?php checked( $config['enabled'], '1' ); ?> /> <?php esc_html_e( 'Route WordPress emails through the selected Mailtrap mode', 'mailtrap-for-wp' ); ?></label></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e( 'Mode', 'mailtrap-for-wp' ); ?></th>
                <td>
                    <label><input type="radio" name="mailtrap_wp_config[mode]" value="sandbox" <?php checked( $config['mode'], 'sandbox' ); ?> /> <?php esc_html_e( 'Email Sandbox (testing; messages are not delivered)', 'mailtrap-for-wp' ); ?></label><br>
                    <label><input type="radio" name="mailtrap_wp_config[mode]" value="transactional" <?php checked( $config['mode'], 'transactional' ); ?> /> <?php esc_html_e( 'Transactional Stream (live delivery)', 'mailtrap-for-wp' ); ?></label>
                    <p class="description"><strong><?php esc_html_e( 'Caution:', 'mailtrap-for-wp' ); ?></strong> <?php esc_html_e( 'Transactional mode sends real emails. Verify your sending domain and credentials before switching.', 'mailtrap-for-wp' ); ?></p>
                </td>
            </tr>
        </table>
        <h2><?php esc_html_e( 'Email Sandbox SMTP', 'mailtrap-for-wp' ); ?></h2>
        <p><?php esc_html_e( 'Find these credentials in Email Sandbox → your sandbox → Integration → SMTP.', 'mailtrap-for-wp' ); ?> <code>sandbox.smtp.mailtrap.io</code></p>
        <table class="form-table" role="presentation">
            <tr><th scope="row"><label for="mailtrap-sandbox-username"><?php esc_html_e( 'Sandbox username', 'mailtrap-for-wp' ); ?></label></th><td><input class="regular-text" id="mailtrap-sandbox-username" autocomplete="off" name="mailtrap_wp_config[sandbox_username]" value="<?php echo esc_attr( $config['sandbox_username'] ); ?>" /></td></tr>
            <tr><th scope="row"><label for="mailtrap-sandbox-password"><?php esc_html_e( 'Sandbox password', 'mailtrap-for-wp' ); ?></label></th><td>
                <input class="regular-text" type="password" id="mailtrap-sandbox-password" autocomplete="new-password" name="mailtrap_wp_config[sandbox_password]" value="" placeholder="<?php esc_attr_e( 'Leave blank to keep saved password', 'mailtrap-for-wp' ); ?>" />
                <label><input type="checkbox" name="mailtrap_wp_config[clear_sandbox_password]" value="1" /> <?php esc_html_e( 'Clear saved password', 'mailtrap-for-wp' ); ?></label>
            </td></tr>
            <tr><th scope="row"><label for="mailtrap-sandbox-port"><?php esc_html_e( 'Port', 'mailtrap-for-wp' ); ?></label></th><td><select id="mailtrap-sandbox-port" name="mailtrap_wp_config[sandbox_port]">
                <?php foreach ( array( '2525', '587', '465', '25' ) as $port ) : ?><option value="<?php echo esc_attr( $port ); ?>" <?php selected( $config['sandbox_port'], $port ); ?>><?php echo esc_html( $port ); ?></option><?php endforeach; ?>
            </select> <span class="description"><?php esc_html_e( 'Port 465 uses TLS on connect; other ports use STARTTLS.', 'mailtrap-for-wp' ); ?></span></td></tr>
        </table>
        <h2><?php esc_html_e( 'Transactional SMTP', 'mailtrap-for-wp' ); ?></h2>
        <p><?php esc_html_e( 'Find credentials under Sending Domains → verified domain → Integrations → Transactional Stream → SMTP.', 'mailtrap-for-wp' ); ?> <code>live.smtp.mailtrap.io</code></p>
        <table class="form-table" role="presentation">
            <tr><th scope="row"><label for="mailtrap-live-username"><?php esc_html_e( 'SMTP username', 'mailtrap-for-wp' ); ?></label></th><td><input class="regular-text" id="mailtrap-live-username" autocomplete="off" name="mailtrap_wp_config[live_username]" value="<?php echo esc_attr( $config['live_username'] ); ?>" /></td></tr>
            <tr><th scope="row"><label for="mailtrap-live-password"><?php esc_html_e( 'SMTP password', 'mailtrap-for-wp' ); ?></label></th><td>
                <input class="regular-text" type="password" id="mailtrap-live-password" autocomplete="new-password" name="mailtrap_wp_config[live_password]" value="" placeholder="<?php esc_attr_e( 'Leave blank to keep saved password', 'mailtrap-for-wp' ); ?>" />
                <label><input type="checkbox" name="mailtrap_wp_config[clear_live_password]" value="1" /> <?php esc_html_e( 'Clear saved password', 'mailtrap-for-wp' ); ?></label>
            </td></tr>
            <tr><th scope="row"><label for="mailtrap-live-port"><?php esc_html_e( 'Port', 'mailtrap-for-wp' ); ?></label></th><td><select id="mailtrap-live-port" name="mailtrap_wp_config[live_port]">
                <?php foreach ( array( '587', '2525', '465', '25' ) as $port ) : ?><option value="<?php echo esc_attr( $port ); ?>" <?php selected( $config['live_port'], $port ); ?>><?php echo esc_html( $port ); ?></option><?php endforeach; ?>
            </select></td></tr>
            <tr><th scope="row"><label for="mailtrap-from-email"><?php esc_html_e( 'From email (optional)', 'mailtrap-for-wp' ); ?></label></th><td><input type="email" class="regular-text" id="mailtrap-from-email" name="mailtrap_wp_config[from_email]" value="<?php echo esc_attr( $config['from_email'] ); ?>" /><p class="description"><?php esc_html_e( 'Use an address on your verified sending domain. Leave empty to retain the sender set by WordPress or another plugin.', 'mailtrap-for-wp' ); ?></p></td></tr>
            <tr><th scope="row"><label for="mailtrap-from-name"><?php esc_html_e( 'From name (optional)', 'mailtrap-for-wp' ); ?></label></th><td><input class="regular-text" id="mailtrap-from-name" name="mailtrap_wp_config[from_name]" value="<?php echo esc_attr( $config['from_name'] ); ?>" /></td></tr>
        </table>
        <h2><?php esc_html_e( 'Sandbox message browser (optional)', 'mailtrap-for-wp' ); ?></h2>
        <p><?php esc_html_e( 'Requires a Sandbox API token with appropriate permissions. The token is separate from your SMTP credentials.', 'mailtrap-for-wp' ); ?></p>
        <table class="form-table" role="presentation">
            <tr><th scope="row"><label for="mailtrap-sandbox-api-token"><?php esc_html_e( 'API token', 'mailtrap-for-wp' ); ?></label></th><td>
                <input type="password" class="regular-text" id="mailtrap-sandbox-api-token" autocomplete="new-password" name="mailtrap_wp_config[sandbox_api_token]" value="" placeholder="<?php esc_attr_e( 'Leave blank to keep saved token', 'mailtrap-for-wp' ); ?>" />
                <label><input type="checkbox" name="mailtrap_wp_config[clear_sandbox_api_token]" value="1" /> <?php esc_html_e( 'Clear saved token', 'mailtrap-for-wp' ); ?></label>
                <p class="description"><a href="https://docs.mailtrap.io/email-sandbox/setup/sandbox-api-integration" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Sandbox API setup', 'mailtrap-for-wp' ); ?></a></p>
            </td></tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>
