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
?>
