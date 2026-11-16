<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$sandbox_id = isset( $_GET['sandbox_id'] ) ? absint( $_GET['sandbox_id'] ) : 0;
$message_id = isset( $_GET['message_id'] ) ? absint( $_GET['message_id'] ) : 0;
$page_number = isset( $_GET['pageno'] ) ? min( 1000, max( 1, absint( $_GET['pageno'] ) ) ) : 1;
$base = admin_url( 'admin.php?page=mailtrap-inbox' );
?>
<div class="wrap">
    <?php include __DIR__ . '/page-header.php'; ?>
    <p><?php esc_html_e( 'Read-only view of captured Sandbox messages. External resources in message previews are blocked.', 'mailtrap-for-wp' ); ?></p>
    <?php if ( ! MailtrapPlugin::credential( 'sandbox_api_token', 'MAILTRAP_WP_SANDBOX_API_TOKEN' ) ) : ?>
        <p><?php esc_html_e( 'Set your Sandbox API token in the plugin settings to browse messages.', 'mailtrap-for-wp' ); ?></p>
    <?php elseif ( ! $sandbox_id ) : ?>
        <?php $sandboxes = MailtrapSandboxAPI::sandboxes(); ?>
        <?php if ( is_wp_error( $sandboxes ) ) : ?>
            <div class="notice notice-error"><p><?php echo esc_html( $sandboxes->get_error_message() ); ?></p></div>
        <?php elseif ( empty( $sandboxes ) ) : ?>
            <p><?php esc_html_e( 'No sandboxes found.', 'mailtrap-for-wp' ); ?></p>
        <?php else : ?>
            <h2><?php esc_html_e( 'Sandboxes', 'mailtrap-for-wp' ); ?></h2>
            <table class="widefat striped"><thead><tr><th><?php esc_html_e( 'Sandbox', 'mailtrap-for-wp' ); ?></th><th><?php esc_html_e( 'Messages', 'mailtrap-for-wp' ); ?></th></tr></thead><tbody>
            <?php foreach ( $sandboxes as $sandbox ) : ?>
                <?php if ( ! is_array( $sandbox ) || empty( $sandbox['id'] ) ) { continue; } ?>
                <tr>
                    <td><a href="<?php echo esc_url( add_query_arg( 'sandbox_id', absint( $sandbox['id'] ), $base ) ); ?>"><?php echo esc_html( isset( $sandbox['name'] ) ? $sandbox['name'] : (string) $sandbox['id'] ); ?></a></td>
                    <td><?php echo esc_html( isset( $sandbox['emails_count'] ) ? (string) $sandbox['emails_count'] : '—' ); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody></table>
        <?php endif; ?>
    <?php else : ?>
        <p><a href="<?php echo esc_url( $base ); ?>">&larr; <?php esc_html_e( 'All sandboxes', 'mailtrap-for-wp' ); ?></a></p>
        <?php $messages = MailtrapSandboxAPI::messages( $sandbox_id, $page_number ); ?>
        <?php if ( is_wp_error( $messages ) ) : ?>
            <div class="notice notice-error"><p><?php echo esc_html( $messages->get_error_message() ); ?></p></div>
        <?php else : ?>
            <h2><?php echo esc_html( sprintf( __( 'Sandbox #%d messages', 'mailtrap-for-wp' ), $sandbox_id ) ); ?></h2>
            <?php if ( empty( $messages ) ) : ?>
                <p><?php esc_html_e( 'No messages found on this page.', 'mailtrap-for-wp' ); ?></p>
            <?php else : ?>
                <table class="widefat striped"><thead><tr><th><?php esc_html_e( 'Subject', 'mailtrap-for-wp' ); ?></th><th><?php esc_html_e( 'To', 'mailtrap-for-wp' ); ?></th><th><?php esc_html_e( 'Sent at', 'mailtrap-for-wp' ); ?></th></tr></thead><tbody>
                    <?php foreach ( $messages as $message ) : ?>
                        <?php if ( ! is_array( $message ) || empty( $message['id'] ) ) { continue; } ?>
                        <?php $url = add_query_arg( array( 'sandbox_id' => $sandbox_id, 'message_id' => absint( $message['id'] ), 'pageno' => $page_number ), $base ); ?>
                        <tr>
                            <td><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( isset( $message['subject'] ) ? $message['subject'] : '(no subject)' ); ?></a></td>
                            <td><?php echo esc_html( isset( $message['to_email'] ) ? $message['to_email'] : '' ); ?></td>
                            <td><?php echo esc_html( isset( $message['sent_at'] ) ? $message['sent_at'] : '' ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody></table>
                <p>
                    <?php if ( $page_number > 1 ) : ?><a class="button" href="<?php echo esc_url( add_query_arg( array( 'sandbox_id' => $sandbox_id, 'pageno' => $page_number - 1 ), $base ) ); ?>"><?php esc_html_e( 'Previous page', 'mailtrap-for-wp' ); ?></a><?php endif; ?>
                    <?php if ( count( $messages ) >= 30 ) : ?><a class="button" href="<?php echo esc_url( add_query_arg( array( 'sandbox_id' => $sandbox_id, 'pageno' => $page_number + 1 ), $base ) ); ?>"><?php esc_html_e( 'Next page', 'mailtrap-for-wp' ); ?></a><?php endif; ?>
                </p>
            <?php endif; ?>
            <?php if ( $message_id ) : ?>
                <?php $details = MailtrapSandboxAPI::message( $sandbox_id, $message_id ); ?>
                <?php $body = MailtrapSandboxAPI::message_body( $sandbox_id, $message_id ); ?>
                <?php if ( is_wp_error( $details ) || is_wp_error( $body ) ) : ?>
                    <div class="notice notice-error"><p><?php echo esc_html( is_wp_error( $details ) ? $details->get_error_message() : $body->get_error_message() ); ?></p></div>
                <?php else : ?>
                    <h3><?php echo esc_html( isset( $details['subject'] ) ? $details['subject'] : '(no subject)' ); ?></h3>
                    <p><strong><?php esc_html_e( 'From:', 'mailtrap-for-wp' ); ?></strong> <?php echo esc_html( isset( $details['from_email'] ) ? $details['from_email'] : '' ); ?><br>
                    <strong><?php esc_html_e( 'To:', 'mailtrap-for-wp' ); ?></strong> <?php echo esc_html( isset( $details['to_email'] ) ? $details['to_email'] : '' ); ?></p>
                    <?php // The sandboxed, restricted iframe prevents email HTML from executing in the WordPress admin context. ?>
                    <?php $csp = '<meta http-equiv="Content-Security-Policy" content="default-src \'none\'; style-src \'unsafe-inline\'; img-src data:;">'; ?>
                    <iframe title="<?php esc_attr_e( 'Sandbox email preview', 'mailtrap-for-wp' ); ?>" sandbox="" referrerpolicy="no-referrer" style="width:100%;min-height:380px;border:1px solid #ccd0d4;background:#fff" srcdoc="<?php echo esc_attr( $csp . $body ); ?>"></iframe>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
