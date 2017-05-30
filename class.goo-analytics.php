<?php

class goo_analytics
{

  protected $option_name = 'goo-analytics-options';
  protected $data        = array(
    'tracking_id' => ''
  );

  public function __construct()
  {
    add_action('wp_head', array($this, 'add_tracking_code'), 0);
    add_action('wp_head', array($this, 'disallow_searchengines'));

    add_action('add_meta_boxes', array($this, 'add_page_checkbox'));
    add_action('save_post', array($this, 'save_page_checkbox'));

    add_action('admin_init', array($this, 'admin_init'));
    add_action('admin_menu', array($this, 'add_admin_page'));

    register_activation_hook(GOO_ANALYTICS_PLUGIN_FILE, array($this, 'activate'));
  }

  function disallow_searchengines()
  {
    if (is_singular('post'))
    {
      $post = get_post(get_the_ID());

      if (strpos( $post->name, 'imprint' ) !== false || $post->ID===1)
        echo '<meta name="robots" content="noindex,nofollow>';
    }
  }

  function add_page_checkbox()
  {
    add_meta_box( 'goo-analytics-meta-box-id',
                  'Goo Analytics Plugin',
                  array($this, 'build_meta_box'),
                  'page',
                  'normal',
                  'high' );
  }

  function build_meta_box($page)
  {
    $values  = get_post_custom( $page->ID );

    wp_nonce_field( 'goo_analytics_meta_box_nonce', 'meta_box_nonce' );

    ?>
    <p>
        <label for="goo-analytics-checkbox">Exclude Google Analytics tracking code from this page?</label>
        <input type="checkbox" name="goo-analytics-checkbox" id="goo-analytics-checkbox" <?php echo $values['goo-analytics'][0] ?> " />
    </p>
    <?php
  }

  function save_page_checkbox($page_id)
  {
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'goo_analytics_meta_box_nonce' ) ) return;
    if( !current_user_can( 'edit_post', $page_id ) ) return;

    if( isset( $_POST['goo-analytics-checkbox'] ) )
      update_post_meta( $page_id, 'goo-analytics', 'checked' );
    else
      update_post_meta( $page_id, 'goo-analytics', '' );
  }

  function add_tracking_code()
  {
    if( is_page() && $options = get_option('goo-analytics-options') )
    {
      $values = get_post_custom(get_the_ID());

      if (isset($values['goo-analytics']) && $values['goo-analytics'][0]==="")
      {
        $tracking_id = $options['tracking_id'];
        echo "<!-- Google Analytics -->
              <script>
                (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
                })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

                ga('create', '$tracking_id', location.pathname);
                ga('send', 'pageview');
              </script>
              <!-- End Google Analytics -->";
      }
    }
  }

  function activate()
  {
    update_option($this->option_name, $this->data);
  }

  function deactivate()
  {
    delete_option($this->option_name);
  }

  function admin_init()
  {
    register_setting('goo-analytics_list_options', $this->option_name, array($this, 'validate'));
  }

  function validate($input)
  {
    $valid                = array();
    $valid['tracking_id'] = sanitize_text_field($input['tracking_id']);
    $re                   = '/(UA|YT|MO)-\d+-\d+/';

    if ( !preg_match($re, $valid['tracking_id']) )
    {
      add_settings_error(
              'tracking_id',
              'tracking_id_invaliderror',
              'Please enter a valid google tracking ID',
              'error'
      );

      $valid['tracking_id'] = $this->data['tracking_id'];
    }

    return $valid;
  }

  function add_admin_page()
  {
    add_options_page('Goo-analytics Options', 'Goo-analytics Options', 'manage_options', 'goo-analytics_list_options', array($this, 'create_options_page'));
  }

  function create_options_page()
  {
    $options = get_option($this->option_name);
    ?>
    <div class="wrap">
        <h2>Google Analytics Options</h2>
        <form method="post" action="options.php">
            <?php settings_fields('goo-analytics_list_options'); ?>
            <table class="form-table">
                <tr valign="top"><th scope="row">Google Analytics Tracking ID</th>
                    <td><input type="text" name="<?php echo $this->option_name?>[tracking_id]" value="<?php echo $options['tracking_id']; ?>" /></td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
            </p>
        </form>
    </div>
    <?php
  }

}
