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
function headless_bash() {

  switch($_REQUEST['script']) {
    case 'ls':
      $cmd = 'ls';
      break;
    case 'lah':
      $cmd = 'ls -lah';
      break;
    default:
      $cmd = 'ls';
      break;
  }

  //$bash = shell_exec( dirname(__FILE__)."/scripts/update_theme.sh 2>&1");
  echo shell_exec('cd '.dirname(__FILE__).' && '.$cmd.' 2>&1');
  //Always die in functions echoing ajax content
  die();
}
add_action('wp_ajax_headless_bash', 'headless_bash');


function headless_bash_scripts() {
?>
    <div class="wrap">
      <h1>Bash Scripts</h1>
      <p></p>
      <button class="headless_action" data-action="ls">
        list
        <span class="status"></span>
      </button>
      <br/><br/>
      <button class="headless_action" data-action="lah">
        list all
        <span class="status"></span>
      </button>
      <p></p>
      <code id="update_status"></code>

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

        $('.headless_action').on('click', function() {
          var script = $(this).data('action');
          ajaxReq('headless_bash', script, $(this));
          $(this).find('.status').text('running')
        });

        function ajaxReq(action, script, el) {
          $.ajax({
            url: ajaxurl,
            data: {
              'action': action,
              'script': script
            },
            success:function(data) {
              $('#update_status').html(data);
              console.log(data);
              el.find('.status').text('')
            },
            error: function(errorThrown){ console.log(errorThrown) }
          });
        }

      });
    </script>

<?
}

function theme_settings_page($bucket) {

  //$buckets = $s3Client->listBuckets();

  ?>
    <div class="wrap">
      <h1>Theme Panel</h1>
      aws: <br/>
      <p></p>
      <button id="headless_req">
        Update Theme
        <span class="status"></status>
      </button>
      <code id="update_status"></code>
      <br/><br/>
      <button id="headless_req_test">
        testing
        <span class="status"></status>
      </button>

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
              $('#update_status').html(data);
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
  //add_submenu_page( "headlesswp-panel", "AWS S3", "AWS S3", "manage_options", "aws-s3", "headless_aws_s3_upload", null );
  add_submenu_page( "headlesswp-panel", "Bash Scripts", "Bash Scripts", "manage_options", "bash", "headless_bash_scripts", null );
});



/* 

require_once(__DIR__ . '/vendor/autoload.php');
$dotenv = Dotenv\Dotenv::createImmutable(ABSPATH);
$dotenv->load();
use Aws\S3\S3Client;
         


function headless_aws_s3_upload() {

  $bucket = $_ENV['S3BUCKET'];
  $keyname = $_ENV['AWS_KEY'];
  
  // Instantiate the S3 client with your AWS credentials
  $s3 = new S3Client([
    'version'     => 'latest',
    'region'      => 'us-east-2',
    'credentials' => [
      'key'    => $_ENV['AWS_KEY'],
      'secret' => $_ENV['AWS_SECRET'],
    ],
  ]);
  $buckets = $s3->listBuckets();
  ?>

    <h1>AWS S3 Content</h1>
  <?

    $objects = $s3->listObjects([
      'Bucket' => $bucket
    ]);
    foreach ($objects['Contents']  as $object) {
      echo $object['Key'] . PHP_EOL;
    }

}

 */

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
