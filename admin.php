<?php
/*
Plugin Name: Prediction League
Plugin URI: http://www.liga.parkdrei.de
Description: Prediction League / Sporty Office / Fußballtip
Author: Robert Kapp
Version: 2.1.2

License: GPL
Author URI: http://www.liga.parkdrei.de/predictionleague

Min WP Version: ??
Max WP Version: 3.9.1
*/

define('PL_VERSION', "2.1.2");
include( plugin_dir_path( __FILE__ ) . 'lib.php');

/* create db while the activation process */
add_action('activate_predictionleague/admin.php', 'prediction_league_install');

if (isset($_REQUEST['action'])){
	$action = $_REQUEST['action'];
} else {
	$action = false;
}
/* export */
	if ($action == "export"){
		$competition_id = $_REQUEST['competition_id'];settype($competition_id, INT);
		pl_export_competition($competition_id);
		exit;
		}



/* get the options */
if ($pl_optionstring = get_option('predictionleague_options')){
	$pl_options = pl_read_option_string($pl_optionstring);}
else {
	$pl_options = pl_set_default_options_array();
	$option_string = pl_create_option_string($pl_options);
	update_option("predictionleague_options", mysql_real_escape_string($option_string));
	}
/* get the language file */
if (file_exists(plugin_dir_path( __FILE__ ) ."/language/".$pl_options['language'].".php")){
	include(plugin_dir_path( __FILE__ ) ."/language/".$pl_options['language'].".php");
	}
	/* display the view.php */
	function pl_print_predictionleague($content) {
		global $pl_options;
		$page_id = $pl_options['page_id'];
			if(is_page($page_id) AND $page_id!="")
			{
			echo $content;
			echo 'hu';
			include_once (plugin_dir_path( __FILE__ ) ."view.php");
			}
			else
			{return $content;}
		}
	/* if everything is ok */
	if (pl_check_tables()){
		/* check the next round */
		pl_check_next_day();
		/* print the content */
		add_action('the_content', 'pl_print_predictionleague');
	}

	/* menu (Tipspiel, Manage) */
	add_action('admin_menu', 'add_predictionleague');

	function add_predictionleague() {
		add_menu_page(__('Options', 'Prediction League'), __('Prediction League', 'predictionleague'),
		'edit_others_posts', 	'predictionleague', 'pl_options_page');

		add_submenu_page( 'predictionleague' , __('competitions', 'predictionleague'), __('Competitions', 'predictionleague'), 'edit_others_posts',
		'competitions', 'pl_competition_page');

		add_submenu_page( 'predictionleague' , __('infos', 'predictionleague'), __('Help/Infos', 'predictionleague'), 'edit_others_posts',
		'infos', 'pl_info_page');
		}

	/* version */
	add_action('wp_head', 'add_predictionleague_version');

		function pl_scripts() {
			global $pl_options;
		    wp_enqueue_script( 'jquery' );
		    wp_enqueue_script('jquery-ui-core');
		    wp_enqueue_script('jquery-ui-sortable');
		    $pluginurl = urlencode(plugins_url('', __FILE__));
		    wp_register_style( 'pl_style', plugins_url('styles.php?colors='.$pl_options['color1'].'-'.$pl_options['color2'].'-'.$pl_options['color3'].'-'.$pl_options['color4'].'&pluginurl='.$pluginurl, __FILE__), false, '1.0', 'all');
		    wp_enqueue_style( 'pl_style' );
		}

		add_action( 'wp_enqueue_scripts', 'pl_scripts' );

/**
 * metatags
 * since 1.0
 */
function add_predictionleague_version() {
	echo "<meta name='Prediction League' content='".PL_VERSION."' />\n";
	}
?>
