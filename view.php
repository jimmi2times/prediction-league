<?php
/*
 * view.php
 * shows the public tip
 *
 */

global $user_ID;
//include( plugin_dir_path( __FILE__ ) . 'styles.php');

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
	$html .=  '<p><small><a href="http://liga.parkdrei.de/category/das-spiel-hier/predictionleague/">Prediction League Plugin '.PL_VERSION.' f&uuml;r Wordpress</a></small></p>';
}
?>
