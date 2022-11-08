<?php
function child_theme_styles() {
  wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
  wp_enqueue_style( 'child-theme-css', get_stylesheet_directory_uri() .'/style.css' , array('parent-style'));
}
add_action( 'wp_enqueue_scripts', 'child_theme_styles' );

// Google Fonts ausstellen
add_filter( 'elementor/frontend/print_google_fonts', '__return_false' );

// Standard-Footer entfernen
add_action( 'after_setup_theme', 'cb_remove_footer_area' );
function cb_remove_footer_area() {
  remove_action( 'generate_footer','generate_construct_footer' );
}

// Neuen Footer direkt von der Uni-Startseite übernehmen
// add_action( 'wp_footer','cb_insert_footer' );
// function cb_insert_footer () {
//   $url = 'https://www.uibk.ac.at/index.html.de';
//   $source = file_get_contents($url);
//   $startFooterHTML = strpos($source, '<footer'); // Nicht schön, dass über reine Strings zu machen... aber so funktioniert es zumindest.
//   $footerHTML = substr($source, $startFooterHTML);
//   echo $footerHTML;
//   //echo '<link rel="stylesheet" type="text/css" media="all" href="https://www.uibk.ac.at/stylesheets/15/css/minimized-202002190957.css"/>';
// }

function cbWarningIE() {
	wp_enqueue_script('cb-header-iewarning', '/wp-content/themes/generatepress-child/cb-iewarning.js', array('jquery'), false, true);
}
add_action('wp_head', 'cbWarningIE');
?>
