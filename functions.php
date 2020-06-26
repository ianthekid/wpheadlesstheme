<?php
//
add_theme_support('post-thumbnails');
add_theme_support('align-wide');
add_theme_support('responsive-embeds');

if ( function_exists( 'register_nav_menus' ) ) {
  register_nav_menus(
    array(
      'primary-menu' => __( 'Primary Menu' ),
      'footer-menu' => __( 'Footer Menu' )
    )
  );
}


/**
 * Ajax js
 */
function headless_req() {

  if( isset($_REQUEST) ) {
    $script = $_REQUEST['script'];

    //$bash = shell_exec( dirname(__FILE__)."/scripts/update_theme.sh 2>&1");
    echo shell_exec('cd '.dirname(__FILE__).' && git pull 2>&1');
  }
  //Always die in functions echoing ajax content
  die();
}
add_action('wp_ajax_headless_req', 'headless_req');

function theme_settings_page() {
  ?>
    <div class="wrap">
      <h1>Theme Panel</h1>
      <button id="headless_req">
        Update Theme
        <span class="status"></status>
      </button>
      <br/><br/><br/>
      <button onClick="testScript();">testing</button
      <form method="post" action="options.php">
        <?php
          settings_fields("section");
          do_settings_sections("theme-options");
          submit_button();
        ?>
      </form>
    </div>
    <script>
      jQuery(document).ready(function($) {

        $('#headless_req').on('click', () => {
          ajaxReq('headless_req', 'theme_update');
          $(this).find('.status').text('updating')
        });

        function ajaxReq(action, script) {
          $.ajax({
            url: ajaxurl,
            data: {
              'action': action,
              'script': script
            },
            success:function(data) { 
              console.log(data);
              $(`#${action}`).find('.status').text('')
            },
            error: function(errorThrown){ console.log(errorThrown) }
          });
        }

      });

    function testScript(){
        var abspath = `<?=ABSPATH ?>`;
        var endpoint = `<?=get_template_directory_uri()?>/script.php?sh=test`;
        fetch(`${endpoint}`)
        .then(res => res.json())
        .then(data => {
        console.log(data)
                console.log(abspath)
        })
      }
    </script>
  <?php
}


add_action("admin_menu", function () {
  add_menu_page("HeadlessWP", "HeadlessWP", "manage_options", "headlesswp-panel", "theme_settings_page", null, 99);
});


/*
if(isset($_REQUEST['headless-updated'])) {
  add_action( 'admin_notices', function() {
    if( $_REQUEST['headless-updated'] == 'true' ) {
        $class = "notice-success";
        $text = "Webinars updated successfully!";
    } else {
        $class = "notice-error";
        $text = "No webinars found.";
    }

    echo '<div class="notice '.$class.' is-dismissible">';
    echo '<p>'.$text.'</p></div>';
  });
}
*/

/**
 * Add dropdown menu for scripts
 **/
add_action( 'wp_before_admin_bar_render', function() {
  global $wp_admin_bar;

  // generate parent node
  $args_parent = array(
    'id'                => 'headlessMenu',
    'title'     => 'HeadlessWP'
  );
  $wp_admin_bar->add_node( $args_parent );

  // generate subnodes
  $menu_nodes = [[
      'id'=>'awsUpdate',
      'title'=>'S3 Update',
      'href' => get_template_directory_uri().'/script.php?sh=aws_s3_upload',
      'parent'=>'headlessMenu'
    ], [
      'id'=>'themeUpdate',
      'title'=>'Update Theme',
      'href' => get_template_directory_uri().'/script.php?sh=theme_update',
      'parent'=>'headlessMenu'
  ]];

  foreach($menu_nodes as $node) {
    $wp_admin_bar->add_node($node);
  }

}, 999 );
