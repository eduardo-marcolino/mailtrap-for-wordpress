<div class="wrap">
  <?php include 'page-header.php'; ?>
  
  <form method="post" action="options.php">
    <?php settings_fields( 'mailtrap-settings' ); ?>
    <?php do_settings_sections( 'mailtrap-settings' ); ?>
    
    <table class="form-table">
      <tr>
        <th scope="row"><?php echo __( 'Enabled', 'mailtrap-for-wp' ) ?></th>
        <td><input type="checkbox" name="mailtrap_enabled" value="1" <?php echo get_option('mailtrap_enabled') === '1' ? 'checked' : '' ?> /></td>
      </tr>
      <tr>
        <th scope="row"><?php echo __( 'SMTP Host', 'mailtrap-for-wp' ) ?></th>
        <td><input type="text" name="mailtrap_host" value="<?php echo esc_attr( get_option('mailtrap_host', 'live.smtp.mailtrap.io') ); ?>" /></td>
      </tr>
      <tr>
        <th scope="row"><?php echo __( 'Username', 'mailtrap-for-wp' ) ?></th>
        <td><input type="text" name="mailtrap_username" value="<?php echo esc_attr( get_option('mailtrap_username') ); ?>" /></td>
      </tr>
      <tr>
        <th scope="row"><?php echo __( 'Password', 'mailtrap-for-wp' ) ?></th>
        <td><input type="text" name="mailtrap_password" value="<?php echo esc_attr( get_option('mailtrap_password') ); ?>" /></td>
      </tr>
      <tr>
        <th scope="row"><?php echo __( 'Port', 'mailtrap-for-wp' ) ?></th>
        <td>
          <select name="mailtrap_port">
            <option value=""><?php echo __( 'Select a port', 'mailtrap-for-wp' ) ?></option>
            <?php foreach(array(25, 465, 2525) as $port): ?>
            <option value="<?php echo $port ?>" <?php echo get_option('mailtrap_port') == $port ? 'selected' : '' ?>><?php echo $port ?></option>
            <?php endforeach; ?>
          </select>
        </td>
      </tr>
      <tr>
        <th scope="row"><?php echo __( 'Use TLS', 'mailtrap-for-wp' ) ?></th>
        <td><input type="checkbox" name="mailtrap_secure" value="tls" <?php echo get_option('mailtrap_secure') === 'tls' ? 'checked' : '' ?> /></td>
      </tr>
      <tr>
        <th scope="row"><?php echo __( 'Enable Debug', 'mailtrap-for-wp' ) ?></th>
        <td>
          <select name="mailtrap_isDebug">
            <?php foreach (range(0,4) as $level): ?>
              <option value="<?php echo $level ?>" <?php echo get_option('mailtrap_isDebug', '1') == $level ? 'selected' : '' ?>><?php echo $level ?> - <?php
                switch($level) {
                  case 0: echo __('Off', 'mailtrap-for-wp'); break;
                  case 1: echo __('Client messages', 'mailtrap-for-wp'); break;
                  case 2: echo __('Client and server messages', 'mailtrap-for-wp'); break;
                  case 3: echo __('As 2 plus connection status', 'mailtrap-for-wp'); break;
                  case 4: echo __('Low-level data output', 'mailtrap-for-wp'); break;
                }
              ?></option>
            <?php endforeach; ?>
          </select>
          <span class="description"><?php echo __( 'Set SMTP debug level (0=off, 1=client, 2=client+server, 3=+conn, 4=low-level)', 'mailtrap-for-wp' ) ?></span>
        </td>
      </tr>
      <tr>
        <td colspan="2"><hr></td>
      </tr>
      <tr>
        <th scope="row"><?php echo __( 'API Token', 'mailtrap-for-wp' ) ?></th>
        <td>
          <input type="text" name="mailtrap_api_token" value="<?php echo esc_attr( get_option('mailtrap_api_token') ); ?>" /><br>
          <small><a href="https://mailtrap.io/public-api" target="blank">https://mailtrap.io/public-api</a></small>
        </td>
      </tr>
      <tr>
        <th scope="row"><?php echo __( 'API Base URL', 'mailtrap-for-wp' ) ?></th>
        <td>
          <input type="text" name="mailtrap_base_url" value="<?php echo esc_attr( get_option('mailtrap_base_url') ); ?>" placeholder="https://mailtrap.io/api/v1" /><br>
          <small><?php echo __( 'Override the default Mailtrap API base URL if needed.', 'mailtrap-for-wp' ) ?></small>
        </td>
      </tr>
    </table>
    
    <?php submit_button(); ?>
    
  </form>
  
  <div class="mailtrap-debugging">
    <h3><?php echo __( 'Debugging', 'mailtrap-for-wp' ) ?></h3>
    
    <p><?php echo __( 'To see detailed SMTP debugging information, send a test email from the Test page.', 'mailtrap-for-wp' ) ?></p>
    
    <p>
      <a href="<?php echo admin_url('admin.php?page=mailtrap-test'); ?>" class="button button-secondary"><?php echo __( 'Go to Test Page', 'mailtrap-for-wp' ) ?></a>
    </p>
    
    <?php if (defined('WP_DEBUG') && WP_DEBUG): ?>
      <p><?php echo __( 'Debug mode is enabled. Check your server\'s error log for more information.', 'mailtrap-for-wp' ) ?></p>
    <?php else: ?>
      <p><?php echo __( 'For more detailed debugging, enable WP_DEBUG in your wp-config.php file.', 'mailtrap-for-wp' ) ?></p>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['mailtrap_errors']) && !empty($_SESSION['mailtrap_errors'])): ?>
      <h4><?php echo __( 'Recent Errors', 'mailtrap-for-wp' ) ?></h4>
      <ul class="mailtrap-error-list">
        <?php foreach ($_SESSION['mailtrap_errors'] as $error): ?>
          <li><?php echo esc_html($error); ?></li>
        <?php endforeach; ?>
      </ul>
      <p>
        <a href="<?php echo admin_url('admin.php?page=mailtrap-settings&clear_errors=1'); ?>" class="button"><?php echo __( 'Clear Errors', 'mailtrap-for-wp' ) ?></a>
      </p>
    <?php endif; ?>
  </div>
</div>
