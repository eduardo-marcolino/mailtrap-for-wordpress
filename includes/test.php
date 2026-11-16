<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<div class="wrap">
    <?php include __DIR__ . '/page-header.php'; ?>
    <?php if ( true === $email_sent ) : ?>
        <div class="notice notice-success"><p><?php echo 'sandbox' === $config['mode']
            ? esc_html__( 'Test email submitted to Email Sandbox. Open your sandbox to inspect it.', 'mailtrap-for-wp' )
            : esc_html__( 'WordPress submitted the email to live SMTP. This does not confirm inbox delivery.', 'mailtrap-for-wp' ); ?></p></div>
    <?php endif; ?>
    <?php if ( $error_message ) : ?>
        <div class="notice notice-error"><p><?php echo esc_html( $error_message ); ?></p></div>
    <?php endif; ?>
    <p><?php esc_html_e( 'Send a message using the regular WordPress wp_mail() function.', 'mailtrap-for-wp' ); ?></p>
    <?php if ( 'transactional' === $config['mode'] ) : ?>
        <p><strong><?php esc_html_e( 'Live mode: this sends to an actual recipient.', 'mailtrap-for-wp' ); ?></strong></p>
    <?php else : ?>
        <p><?php esc_html_e( 'Sandbox mode: the message is captured and is not delivered to the recipient.', 'mailtrap-for-wp' ); ?></p>
    <?php endif; ?>
    <form action="<?php echo esc_url( admin_url( 'admin.php?page=mailtrap-test' ) ); ?>" method="post">
        <?php wp_nonce_field( 'mailtrap_test_action' ); ?>
        <table class="form-table" role="presentation">
            <tr><th scope="row"><label for="mailtrap-test-to"><?php esc_html_e( 'To', 'mailtrap-for-wp' ); ?></label></th><td><input class="regular-text" type="email" required id="mailtrap-test-to" name="to" value="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" /></td></tr>
            <tr><th scope="row"><label for="mailtrap-test-message"><?php esc_html_e( 'Message', 'mailtrap-for-wp' ); ?></label></th><td><textarea class="large-text" id="mailtrap-test-message" name="message" rows="5">This is a Mailtrap for WordPress test.</textarea></td></tr>
        </table>
        <?php submit_button( __( 'Send test email', 'mailtrap-for-wp' ) ); ?>
    </form>
</div>
