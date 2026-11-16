<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$current_tab = isset( $_GET['page'] ) && is_string( $_GET['page'] )
    ? sanitize_key( wp_unslash( $_GET['page'] ) )
    : 'mailtrap-settings';
$tabs = array(
    'mailtrap-settings' => array( 'Settings', admin_url( 'options-general.php?page=mailtrap-settings' ) ),
    'mailtrap-test'     => array( 'Send a test', admin_url( 'admin.php?page=mailtrap-test' ) ),
    'mailtrap-inbox'    => array( 'Sandbox messages', admin_url( 'admin.php?page=mailtrap-inbox' ) ),
);
?>
<h1><?php esc_html_e( 'Mailtrap for WordPress', 'mailtrap-for-wp' ); ?></h1>
<p><?php esc_html_e( 'Third-party plugin for Mailtrap Email Sandbox and transactional SMTP sending.', 'mailtrap-for-wp' ); ?></p>
<nav class="nav-tab-wrapper" aria-label="Mailtrap">
    <?php foreach ( $tabs as $slug => $tab ) : ?>
        <a href="<?php echo esc_url( $tab[1] ); ?>" class="nav-tab <?php echo $current_tab === $slug ? 'nav-tab-active' : ''; ?>">
            <?php echo esc_html( $tab[0] ); ?>
        </a>
    <?php endforeach; ?>
</nav>
