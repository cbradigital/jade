<?php
/*
 * Plugin Name: JADE Tool
 * Version: 1.0
 * Description: JADE - Job Ad Decoder /  This plugin is the logic and templates behind JADE.
 * Author: Philipp Horwath
 */

define("PAGEID_JADE_START", 828);
define("PAGEID_JADE_ANALYSIS", 439);
define("PAGEID_JADE_PROFILE", 1092);
define("PAGEID_JADE_PROFILE_VERSIONS", 1112);

//define("JADE_BACKEND_CURRENT_USER_CAN", "administrator");
define("JADE_BACKEND_CURRENT_USER_CAN", "manage_categories"); // SuperAdmin, Admin und Editor

require_once(dirname(__FILE__) . '/jade-consts.php');
require_once(dirname(__FILE__) . '/jade-helpers.php');

require_once(dirname(__FILE__) . '/admin/dictionary.php');
require_once(dirname(__FILE__) . '/admin/dict-export.php');
require_once(dirname(__FILE__) . '/admin/jobs.php');
require_once(dirname(__FILE__) . '/admin/jobs-export.php');
require_once(dirname(__FILE__) . '/admin/admin_menus.php');

require_once(dirname(__FILE__) . '/inc/shortcodes.php');

require_once(dirname(__FILE__) . '/inc/jade-start.php');
require_once(dirname(__FILE__) . '/inc/jade-start-rest.php');

require_once(dirname(__FILE__) . '/inc/jade-analysis.php');
require_once(dirname(__FILE__) . '/inc/jade-analysis-rest.php');

require_once(dirname(__FILE__) . '/inc/jade-profile.php');
require_once(dirname(__FILE__) . '/inc/jade-profile-rest.php');

require_once(dirname(__FILE__) . '/inc/jade-profile-versions.php');
require_once(dirname(__FILE__) . '/inc/jade-profile-versions-rest.php');

function wpb_hook_javascript() {
 $r = rand(1, 1000000);
  $validateJS = plugin_dir_url( __FILE__ ) . "/js/jquery.validate.min.js";
  $momentJS = plugin_dir_url( __FILE__ ) . "/js/moment.min.js";

  $analysisCSS = plugin_dir_url( __FILE__ ) . "/css/jade-analysis.css?rand=" . $r ;
  $highlightJS = plugin_dir_url( __FILE__ ) . "/js/jquery.highlight-within-textarea.min.js";

  $printPDF = plugin_dir_url( __FILE__ ) . "/js/html2pdf.bundle.min.js";
  //$printPDF = plugin_dir_url( __FILE__ ) . "/js/pspdfkit.min.js";
  //$printPDF = "https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.3.1/jspdf.umd.min.js";


  if (is_page (PAGEID_JADE_START)) {
    $pluginUrl = plugin_dir_url( __FILE__ ) . "/js/jade-start.js?rand=" . $r ;
    ?>
        <script src="<?php echo $validateJS; ?>"></script>
        <script src="<?php echo $pluginUrl; ?>"></script>
    <?php
  }
  if (is_page (PAGEID_JADE_ANALYSIS)) {
    $pluginUrl = plugin_dir_url( __FILE__ ) . "/js/jade-analysis.js?rand=" . $r ;
    ?>
        <script src="<?php echo $validateJS; ?>"></script>
        <script src="<?php echo $pluginUrl; ?>"></script>
        <script src="<?php echo $highlightJS; ?>"></script>
        <script src="<?php echo $printPDF; ?>"></script>
        <link rel="stylesheet" href="<?php echo $analysisCSS; ?>">
    <?php
  }
  if (is_page (PAGEID_JADE_PROFILE)) {
    $pluginUrl = plugin_dir_url( __FILE__ ) . "/js/jade-profile.js?rand=" . $r ;
    ?>
        <script src="<?php echo $momentJS; ?>"></script>
        <script src="<?php echo $pluginUrl; ?>"></script>
    <?php
  }
  if (is_page (PAGEID_JADE_PROFILE_VERSIONS)) {
    $pluginUrl = plugin_dir_url( __FILE__ ) . "/js/jade-profile-versions.js?rand=" . $r ;
    ?>
        <script src="<?php echo $momentJS; ?>"></script>
        <script src="<?php echo $pluginUrl; ?>"></script>
    <?php
  }
}
add_action('wp_head', 'wpb_hook_javascript');
