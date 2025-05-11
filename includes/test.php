<div class="wrap">
  <?php include 'page-header.php'; ?>
  <?php include 'notices.php'; ?>
  
  <p><?php echo __( 'This test sends an email using standard wp_mail function.', 'mailtrap-for-wp' ) ?></p>
  <form action="<?php echo admin_url('admin.php?page=mailtrap-test'); ?>" method="post">
    
    <?php wp_nonce_field('mailtrap_test_action'); ?>
    
    <table class="form-table">
        <tr>
          <th scope="row"><?php echo __( 'From', 'mailtrap-for-wp' ) ?></th>
          <td><input type="email" name="from" value="<?php echo get_option('admin_email')?>"/></td>
        </tr>
        <tr>
          <th scope="row"><?php echo __( 'To', 'mailtrap-for-wp' ) ?></th>
          <td><input type="email" name="to" value="<?php echo get_option('admin_email')?>"/></td>
        </tr>
        <tr>
          <th scope="row"><?php echo __( 'Message', 'mailtrap-for-wp' ) ?></th>
          <td><textarea name="message" rows="5"><?php echo __( 'This is a Mailtrap for Wordpress Plugin test email.', 'mailtrap-for-wp' ) ?></textarea></td>
        </tr>
        <tr>
          <th></th>
          <td><button type="submit" class="button button-primary"><?php echo __( 'Send', 'mailtrap-for-wp' ) ?></button></td>
        </tr>
    </table>
  </form>
  
  <?php if (isset($_SESSION['mailtrap_debug_log']) && !empty($_SESSION['mailtrap_debug_log'])): ?>
  <div class="mailtrap-debug-log">
    <h3><?php echo __( 'SMTP Debug Log', 'mailtrap-for-wp' ) ?></h3>
    <pre style="background: #f0f0f0; padding: 10px; overflow: auto; max-height: 300px;"><?php echo esc_html($_SESSION['mailtrap_debug_log']); ?></pre>
    <p>
      <a href="<?php echo admin_url('admin.php?page=mailtrap-test&clear_debug=1'); ?>" class="button"><?php echo __( 'Clear Debug Log', 'mailtrap-for-wp' ) ?></a>
    </p>
  </div>
  <?php endif; ?>
</div>