<?php
/*
Plugin Name: Prediction League
Plugin URI: http://www.liga.parkdrei.de
Description: Prediction League / Sporty Office / Fußballtip
Author: Robert Kapp
Version: 2.1.3

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
			if(is_page($page_id) AND $page_id!=""){
				add_filter( 'the_content', 'pl_print_content', 20 );
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

/**
 * add the content oft the game to the page
 * refactored 2.1.3
 * no further need of the view.php
 */
function pl_print_content( $content ) {
		global $user_ID;
		/*
		 * get some data
		 * - number of active competitions
		 * - chosen competition
		 * - chosen round (or next round)
		 * - chosen view
		 */
		$page_id 				= $pl_options['page_id'];
		$number_of_competitions	= pl_get_number_of_competitions();
		if (isset($_REQUEST['action'])) $action = $_REQUEST['action'];
		else $action = false;
		if (isset($_REQUEST['view'])) $view = $_REQUEST['view'];
		else $view = false;

		if (isset($_REQUEST['competition_id'])) {
			$competition_id	= $_REQUEST['competition_id'];
			settype($competition_id, "INT");
			} else {
				$competition_id = false;
			}
		if (isset($_REQUEST['round'])) {
			$round = $_REQUEST['round'];
			settype($round, "INT");
			} else {
				$round = false;
			}
		/*
		 * what to do in case of missing data
		 * - no registrated user --> view = Register
		 * - no chosen view --> view = TipGames
		 * - no chosen competition --> the first active competition in the db
		 * - no chosen round --> round = nextday
		 * - no active competition --> view = Error/no competition
		 */
		if (!$view and !$user_ID){$view="results";}
		if (!$view){$view = "tipgames";}
		if ($competition_id){$competition = pl_get_competition($competition_id);}
		if (!$competition_id){$competition = pl_get_first_competition();}
		//if (!$round){$round = $competition['next_round'];}
		if (!$round AND $view =="tipgames"){$round = $competition['next_round'];}
		if (!$number_of_competitions OR $number_of_competitions == 0){$view = 'noactivecompetition';}
		if (!$user_ID AND $view == "tipgames"){$view = "register";}
		/*
		 * get the form data and save the tips to the database
		 */
		if ($action == "sendtips"){
			// Number of games
			$numberofgames = trim($_POST['numberofgames']);
			for($i=1; $i<=$numberofgames; $i++){
				// Reads the form
				unset($team1tip);
				unset($team2tip);
				$game = "game".$i;
				$team1 = "team1-".$i;
				$team2 = "team2-".$i;
				$game_id = $_POST[$game];settype($game_id, "INT");
				if (isset ($_POST[$team1]) AND $_POST[$team1] != ""){$team1tip = $_POST[$team1];settype($team1tip, "INT");}
				if (isset ($_POST[$team2]) AND $_POST[$team2] != ""){$team2tip = $_POST[$team2];settype($team2tip, "INT");}
				$game = pl_get_game($game_id);
				$timestamp = time();
				// both fields must be filled with "good" values, timecheck must be ok.
				if (isset($team1tip) AND isset($team2tip)){
					if($team1tip > -1 AND $team2tip > -1 AND ($timestamp + ($competition['tiptime'] * 60) + get_option('gmt_offset') * 3600) < $game['gametime']){
						pl_update_tip($game_id, $team1tip, $team2tip, $user_ID);
						}
					}
				}
			}
		// and now the page
		if ($view != 'noactivecompetition'){
			$html .= pl_print_top_menu_tip($number_of_competitions, $view, $round, $competition['id']);
			}
		if ($view == "tipgames" AND $user_ID){
			if ($action == "sendtips"){
				$html .=  '<h3>'.TipsSent.'</h3>';
			}
			$html .= pl_print_games($round, $competition, $pl_options);
			}
		if ($view == "results"){
			$html .= pl_print_results($round, $competition, $pl_options);
			}
		if ($view == "overview"){
			$html .= pl_print_overview($round, $competition, $pl_options);
			}
		if ($view == "noactivecompetition"){
			$html .=  '<h3>'.NoActiveCompetition.'</h3>';
			}
		if ($view == "register"){
			$html .=  '<h3>'.LoginRequired.'</h3>';
			}
		if ($pl_options['showlink'] == "on") {
			$html .=  '<p><small><a href="http://liga.parkdrei.de/category/das-spiel-hier/predictionleague/">Prediction League Plugin '.PL_VERSION.' for Wordpress</a></small></p>';
		}
		// Content on the wp-page and the plugin-output
		$content = $content.$html;
		return $content;
}


?>
