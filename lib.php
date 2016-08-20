<?php
/**
 * The options for management
 * since 1.0
 */
function pl_options_page() {
	global $pl_options, $action;
	// Actions
	if ($action == "deletetables" AND !isset($_REQUEST['confirm'])){
		$link = '?page=predictionleague&amp;action=deletetables';
		pl_admin_message(DeleteTables, 100, 100, 1, $link);
		}
	if ($action == "deletetables" and isset($_REQUEST['confirm']) AND $_REQUEST['confirm'] == "yes"){
		if (pl_delete_tables()){pl_admin_message(TablesDeleted, 100, 100);}
		}
	if ($action == "createtables"){
		if (pl_create_tables()){pl_admin_message(TablesCreated, 100, 100);}
		}
	if ($action == "editoptions"){
		// collect the vars
		$pl_options['page_id'] 		= $_POST['page_id'];settype($pl_options['page_id'], "INT");
		$pl_options['language'] 	= $_POST['language'];strip_tags($pl_options['language']);
		$pl_options['color1']		= $_POST['color1'];strip_tags($pl_options['color1']);
		$pl_options['color2']		= $_POST['color2'];strip_tags($pl_options['color2']);
		$pl_options['color3']		= $_POST['color3'];strip_tags($pl_options['color3']);
		$pl_options['color4']		= $_POST['color4'];strip_tags($pl_options['color4']);


		if (isset($_POST['showlink'])) {
			$pl_options['showlink']		= $_POST['showlink'];strip_tags($pl_options['showlink']);
			} else {
			$pl_options['showlink'] = "off";
		}
		$pl_option_string = pl_create_option_string($pl_options);
		update_option("predictionleague_options", mysql_real_escape_string($pl_option_string));
		$pl_optionstring = get_option('predictionleague_options');
		$pl_options = pl_read_option_string($pl_optionstring);
		}
		?>
	<!-- color picker -->
	<script language="javascript">
	var which_input;
	function fillColorValue(color){
		document.getElementById(which_input).value = color;
		document.getElementById(which_input+"_div").style.backgroundColor = "#"+color;
	}
	function show_picker(ID, Current_Color, Previous_Color){
		which_input = ID;
		var lnk = "<?php echo plugins_url('color_picker/color_picker_files/color_picker_interface.html', __FILE__); ?>\
		?cur_color="+Current_Color+"&pre_color="+Previous_Color;
		window.open(lnk, "", "width=465, height=350");
	}
	</script>
	<!-- header -->
	<div class="wrap">
		<?php
		echo '<h3>'.MainOptions.'</h3>';

		if(pl_check_tables()){
			echo '<p>'.TablesOk.'</p>';
			}
		else {
			echo '<p>'.TablesNotOk.'</p>';
			echo '<p><a href="?page=predictionleague&amp;action=createtables">'.CreateTablesLink.'</a></p>';
			}
		pl_print_main_option_form($pl_options);
		echo '<p><a href="?page=predictionleague&amp;action=deletetables">'.DeleteTablesLink.'</a></p>';
		?>
	</div>
	<?php
}

/**
 * manage the competitions
 * since 1.0
 * updated 2.0
 */
function pl_competition_page() {
	global $pl_options;


	// if there is something wrong with the tables
	if (!pl_check_tables()){
		echo '<div class="wrap">';
		echo '<h3>'.NoCompleteSetup.'</h3>';
		echo '</div>';
		return FALSE;
		}
	// setup the vars
	if (isset($_REQUEST['competition_id'])) {
		$competition_id = $_REQUEST['competition_id'];
		settype($competition_id, "INT");
		}
	else {$competition_id = false;}
	if (isset($_REQUEST['do'])){
		$do = $_REQUEST['do'];strip_tags($do);
		}
	else {$do = false;}
	if (isset($_REQUEST['action'])){
		$action = $_REQUEST['action'];strip_tags($action);
		}
	else {$action = false;}
	$form = array();

	// import an xml file
	if ($action == "importcompetition"){
		global $filename;
		$filename = $_FILES['importxml']['tmp_name'];
		if ($filename){
			include(plugin_dir_path( __FILE__ ) ."import_competition.php");}
			$action = "edit";
		}

	if ($action == "importcompetition_buli"){
		global $filename;
		$filename = plugin_dir_path( __FILE__ ) ."/Bundesliga-2014.xml";
		if ($filename){
			include(plugin_dir_path( __FILE__ ) ."import_competition.php");}
			$action = "edit";
		}
	if ($action == "importcompetition_buli2"){
		global $filename;
		$filename = plugin_dir_path( __FILE__ ) ."/Bundesliga-2-2014.xml";
		if ($filename){
			include(plugin_dir_path( __FILE__ ) ."import_competition.php");}
			$action = "edit";
		}
	if ($action == "importcompetition_buliopenDB"){
			import_opendb();
			$action = "edit";
		}

	// save the changes of the competition-options
	if($do == "change"){
		$requestVars = array("name", "rounds", "round_names", "points_one", "points_two", "points_three", "points_four", "tiptime", "active");
		foreach ($requestVars as $type){
			if (isset($_POST[$type])){
				$form[$type] = $_POST[$type];
				strip_tags($form[$type]);
				if ($type != "name" AND $type != "round_names"){
					settype($form[$type], "INT");
				}
			}
			elseif ($type == "active"){
				$form['active'] = 0;
			}
		}
		$form['id'] = $competition_id;
		pl_save_competition($form);
		$do = false;
		}
	// save the team changes
	if($do == "changeteam"){
		$requestVars = array("team_name", "team_shortname");
		foreach ($requestVars as $type){
			$form[$type] = $_POST[$type];
			strip_tags($form[$type]);
		}
		if (isset($_REQUEST['team_id'])){
			$form['id'] = $_REQUEST['team_id'];
			settype($form['id'], "INT");
			}
		else {
			$form['id'] = false;
		}
		$form['competition_id']	= $competition_id;
		pl_save_team($form);
		$do = "manageteams";
		}
	// delete a team
	if($do == "deleteteam"){
		if (isset($_REQUEST['team_id'])) {
			$form['id']				= $_REQUEST['team_id'];
			settype($form['id'], "INT");
			}
		pl_delete_team($form);
		$do = "manageteams";
		}
	// save the game changes
	if($do == "changegame"){
		$numberofgames 	= $_POST['numberofgames'];
		if ($numberofgames){
			for ($x=1; $x<=$numberofgames; $x++){
				if (isset($_POST['game_id-'.$x])){
					$form['id']				= $_POST['game_id-'.$x];
					settype($form['id'], "INT");
				}
				$form['team1']			= $_POST['team1-'.$x];
				$form['team2']			= $_POST['team2-'.$x];
				$day					= $_POST['day-'.$x];
				$month					= $_POST['month-'.$x];
				$year					= $_POST['year-'.$x];
				$hour					= $_POST['hour-'.$x];
				$minute					= $_POST['minute-'.$x];
				settype($day, "INT");settype($month, "INT");settype($year, "INT");settype($hour, "INT");settype($minute, "INT");
				$form['gametime'] 		= mktime($hour, $minute, 0, $month, $day, $year);
				$form['competition_id']	= $competition_id;
				$form['round']			= $_REQUEST['round'];

				settype($form['competition_id'], "INT");
				settype($form['team1'], "INT");
				settype($form['team2'], "INT");
				settype($form['gametime'], "INT");
				settype($form['round'], "INT");
				pl_save_game($form);
		}}
		$do = "managegames";
		}
	// delete a game
	if($do == "deletegame"){
		$form['id']				= $_REQUEST['game_id'];
		settype($form['id'], "INT");
		pl_delete_game($form);
		$do = "managegames";
		}
	// delete a competition
	if($do == "deletecompetition" AND !isset($_REQUEST['confirm'])){
		$link = '?page=competitions&amp;action=edit&amp;do=deletecompetition&amp;competition_id='.$competition_id;
		pl_admin_message(DeleteCompetition, 100, 100, 1, $link);
		//unset($competition_id);
		}

	if($do == "deletecompetition" AND isset($_REQUEST['confirm']) AND $_REQUEST['confirm'] == "yes"){
		pl_delete_competition($competition_id);
		pl_admin_message(CompetitionDeleted, 100, 100);
		//unset($competition_id);
		}
	// save results and calculate the points
	if($do == "sendresults"){
		$numberofgames 	= $_POST['numberofgames'];
		if ($numberofgames){
			for ($x=1; $x<=$numberofgames; $x++){
				$form['id']				= $_POST['game_id-'.$x];
				$form['team1_score']		= $_POST['team1_score-'.$x];
				$form['team2_score']		= $_POST['team2_score-'.$x];
				$form['competition_id']	= $competition_id;
				$form['round']			= $_REQUEST['round'];
				settype($form['id'], "INT");
				settype($form['competition_id'], "INT");
				/* to avoid the 0 and nothing problem */
				if (!isset($form['team1_score'])){$form['team1_score'] = "NULL";}
				if (!isset($form['team2_score'])){$form['team2_score'] = "NULL";}
				if ($form['team1_score'] != "NULL"){settype($form['team1_score'], "INT");}
				if ($form['team2_score'] != "NULL"){settype($form['team2_score'], "INT");}
				settype($form['round'], "INT");
				// TODO
				// because wp->update doesn't handle the NULL parameters the game is only updated
				// no way to clean a result
				if ($_POST['team1_score-'.$x] != "" AND $_POST['team2_score-'.$x] != ""){
					pl_save_results($form);
				}
			}}
		pl_calculate_points($competition_id, $form['round']);
		// save draft of a post with the results
		if (isset($_POST['savedraft']) AND $_POST['savedraft'] == 1)
			{
			$competition = pl_get_competition($competition_id);
			//$_POST['post_category'] = $_POST['category'];settype($_POST['post_category'], "INT");
			$_POST['post_title'] 	= Analysis.' '.$competition['name'].' - '.pl_get_round_name($form['round'], $competition['round_names']);
			$_POST['post_content'] = pl_get_results_for_draft($competition_id, $form['round']);
			$_POST['post_content'] .= pl_get_results_for_draft($competition_id, 0);
			wp_insert_post($_POST);
			}
		}

	// manage user tips
	if ($do == "editusertips"){
		$round = $_REQUEST['round']; settype($round, "INT");
		$userID = $_REQUEST['userID']; settype($userID, "INT");
		$games = pl_get_games($competition_id, $round);
		if ($games){
			foreach($games as $game){
				$recent_user_tips='';
				$recent_user_tips = pl_get_recent_user_tips($game['id'], $userID);
				$new_team1tip = $_POST['team1-'.$userID.'-'.$game['id']];settype($new_team1tip, "INT");
				$new_team2tip = $_POST['team2-'.$userID.'-'.$game['id']];settype($new_team2tip, "INT");
				// save only the changes
				if ($recent_user_tips['team1tip'] != $new_team1tip OR $recent_user_tips['team2tip'] != $new_team2tip ){
					pl_update_tip($game['id'], $new_team1tip, $new_team2tip, $userID);
					}
				if (!$recent_user_tips AND $new_team1tip AND $new_team2tip){
					pl_update_tip($game['id'], $new_team1tip, $new_team2tip, $userID);
					}
				}
			}
	}
	// If there is no competitionID print the Main menu
	if (!$competition_id){pl_print_main_menu_competitions();}
	if ($competition_id AND $do != "deletecompetition"){pl_print_sub_menu_competitions($competition_id);}
	if ($do == "new"){pl_print_competition_option_form('');}
	if ($competition_id AND $do != "deletecompetition"){
		// prints the options
		if ($action == "edit" AND !$do){
			pl_print_competition_option_form($competition_id);
			}
		// team list
		if ($do == "manageteams"){
			pl_print_manage_teams($competition_id);
			}
		// manage the games
		if ($do == "managegames"){
			if (isset($_REQUEST['round'])) {
				$round = $_REQUEST['round'];
				settype($round, "INT");
				}
			else {
				$round = false;
				}
			pl_print_manage_games($competition_id, $round);
			}
		// manage the results
		if ($action == "manageresults"){
			if (isset($_REQUEST['round'])){
				$round = $_REQUEST['round'];
				settype($round, "INT");
			}
			else {$round = false;}
			pl_print_manage_results($competition_id, $round);
			}
		// manage the results
		if ($action == "manageusertips"){
			if (isset($_REQUEST['round'])){
				$round = $_REQUEST['round'];
				settype($round, "INT");
				}
			else {$round = false;}
			pl_print_manage_usertips($competition_id, $round);
			}
		}
}


/**
 * prints the results
 * since 1.0
 */
function pl_print_results($round, $competition, $pl_options){
global $wpdb, $user_ID, $page_id;
/* get the results orderd by points */
$results = pl_get_results_table($competition['id'], $round);
if (!empty($results)){
	if (!$competition['round_names']){
		$roundname = $round.'. '.Round;}
	else {$roundname = pl_get_round_name($round, $competition['round_names']);}
	if ($round == 0){$roundname = Master;}
	$place = 0; 			/* Counter */
	$position = 0;			/* Position */
	$lastuserpoints = 0;  	/* To manage even scores of the users */
	$lastround = pl_calculate_lastround($competition['id']) - 1;


	$html .=  '<div class="pl_headline">';
		$html .=  Results.' '.$roundname;
	$html .=  '</div>';

	$html .=  '<table class="pl_tipp" cellspacing=1 cellpadding = 1>';

	$html .=  '<tr class="tiprow">';
		$html .=  '<th class="tipcolumnone">'.Place.'</th>';
		$html .=  '<th class="tipcolumntwo">'.UserName.'</th>';
		$html .=  '<th class="tipcolumnthree">'.Points.'</th>';
	$html .=  '</tr>';

	foreach ($results as $user){
		/* To manage even scores of the users */
		$showplace = TRUE;
		if ($lastuserpoints == $user['points']) {$showplace = "FALSE";}
		$lastuserpoints = $user['points'];
		$place = $place + 1;
		if ($showplace == "TRUE"){$position = $place;}
		$lastposition_string = '';
		$picture = '';
		if ($lastround > 0 and $round == 0){
				$lastposition = pl_get_position($user['id'], $lastround, $competition['id']);
				$lastposition_string = '('.$lastposition.'.)';
				$picture = pl_get_updown_picture($position, $lastposition);
				}
		$html .=  '<tr class="tiprow">';
			$html .=  '<td class="tipcolumnone">'.$picture.' '.$position.'. '.$lastposition_string.'</td>';
			$html .=  '<td class="tipcolumntwo">'.$user['display_name'].'</td>';
			$html .=  '<td class="tipcolumnthree">'.$user['points'].'</td>';
		$html .=  '</tr>';
		}
	$html .=  '</table>';
	}
	return $html;
}


/**
 * prints the gamesTable as a form
 * depends on the competition and the round
 */
function pl_print_games($round, $competition, $pl_option0s) {
global $wpdb, $user_ID, $pl_options;
$page_id = $pl_options['page_id'];
$timestamp = time();
// get the games by round and competition
$games = pl_get_games($competition['id'], $round);
$results = pl_get_results_user($competition['id'], $round, $user_ID);

if (!$competition['round_names']){
	$roundname = $round.'. '.Round;}
else {$roundname = pl_get_round_name($round, $competition['round_names']);}
if(!empty($games))
	{
	// defines the form
	$html .=  '<form action = "?page_id='.$page_id.'&amp;view=tipgames&amp;action=sendtips&amp;round='.$round.'&amp;competition_id='.$competition['id'].'#menutip" method="POST">';

	$html .=  '<div class="pl_headline">';
		$html .=  Tips.' '.$roundname;
	$html .=  '</div>';

	$html .=  '<table class="tip" cellspacing= 1 cellpadding = 1>';

	$html .=  '<tr class="tiprow">';
		$html .=  '<th width="10%" class="tipcolumnone">'.Gametime.'</th>';
		$html .=  '<th width="40%" class="tipcolumntwo">'.Game.'</th>';
		$html .=  '<th width="22%" class="tipcolumnthree">'.YourTip.'</th>';
		$html .=  '<th width="15%" class="tipcolumnfour">'.Result.'</th>';
		$html .=  '<th width="13%" class="tipcolumnfive">'.YourPoints.'</th>';
	$html .=  '</tr>';
	$numberofgames = 0; // count the games
		foreach ($games as $game) {
		$numberofgames = $numberofgames + 1; // count the games
		$team1 = pl_tip_get_team($game['team1']);
		$team2 = pl_tip_get_team($game['team2']);
		$date = date("d.m.y",$game['gametime']);
		$time = date("G:i",$game['gametime']);
		$recent_user_tips = pl_get_recent_user_tips($game['id'], $user_ID);
		$html .=  '<tr class="tiprow">';
			// Column One --> Date and Time
			$html .=  '<td class="tipcolumnone">';
				$html .=  $date.'<br/>'.$time;
			$html .=  '</td>';
			// Column Two --> Game
			$html .=  '<td class="tipcolumntwo">';
				$picture1 = pl_get_team_picture($team1['team_shortname']);
				$picture2 = pl_get_team_picture($team2['team_shortname']);
				$html .=  $picture1.' '.$team1['team_name'].'<br/>'.$picture2.' '.$team2['team_name'];
			$html .=  '</td>';
			// Column Three --> Tips
			$html .=  '<td class="tipcolumnthree">';
				if (($timestamp + ($competition['tiptime'] * 60) + (get_option('gmt_offset') * 3600)) < $game['gametime']){
					$html .=  '<input class = "tipinput" type="tel" name="team1-'.$numberofgames.'" size=2 maxlength=3 value="'.$recent_user_tips['team1tip'].'">';
					$html .=  ' : ';
					$html .=  '<input class = "tipinput" type="tel" name="team2-'.$numberofgames.'" size=2 maxlength=3 value="'.$recent_user_tips['team2tip'].'">';
					// Sends the GameID
					$html .=  '<input type="hidden" name="game'.$numberofgames.'" value="'.$game['id'].'">';
					}
				else {
					if ($recent_user_tips){
						$html .=  $recent_user_tips['team1tip'].' : '.$recent_user_tips['team2tip'];}
					else {$html .=  NoTips;}
					// Sends the GameID
					$html .=  '<input type="hidden" name="game'.$numberofgames.'" value="'.$game['id'].'">';
					}
			$html .=  '</td>';
			// Column Four --> Results
			$html .=  '<td class="tipcolumnfour">';
				if ($game['team1_score'] != NULL AND $game['team2_score'] != NULL){
					$html .=  $game['team1_score'].' : '.$game['team2_score'];}
				else {$html .=  '---';}
			$html .=  '</td>';
			// Column Five --> Points
			$html .=  '<td class="tipcolumnfive">';
				$points = pl_get_score($recent_user_tips['id']);
				if ($points['points'] != NULL){
					$html .=  $points['points'];}
				else {$html .=  '---';}
			$html .=  '</td>';


		$html .=  '</tr>';
		}

	// sends the number of games
		$html .=  '<tr>';
			$html .=  '<th class="tipcolumnone" colspan="3">';
				$html .=  '<input type="hidden" name="numberofgames" value="'.$numberofgames.'">';
				$html .=  '<input type="submit" class="tipsubmit" value="'.TipSubmit.'">';
			$html .=  '</th>';
			$html .=  '<th class="tipcolumnfour" colspan="2">';
				$html .=  '<b>'.PointsThisRound.': '.$results['points'];
			$html .=  '</th>';
		$html .=  '</tr>';

	$html .=  '</table>';
	}
if (!$games){
	$html .=  '<h3>'.NoGamesYet.'</h3>';
	}
	return $html;
}

/*
 * prints all tips and games
 */
function pl_print_overview($round, $competition, $pl_options){
global $wpdb, $user_ID, $page_id;
	if (!$round){$round = $competition['next_round']-1;}
	$games = pl_get_games($competition['id'], $round);
	$teams = pl_get_all_teams($competition['id']);
	if ($teams){
		foreach ($teams as $teams){
			$team[$teams['id']]['team_shortname'] = $teams['team_shortname'];
			}
		}
	if($games){
		$html .=  '<table class="tip" cellspacing= 1 cellpadding = 1>';
		$html .=  '<tr class="tiprow">';
		$html .=  '<th class="tipcolumnone">'.UserName.'</th>';
		foreach ($games as $game){

			$html .=  '<th class="tipcolumnone">';

			if (isset($team[$game['team1']]['team_shortname'])){
				$html .=  $team[$game['team1']]['team_shortname'].'<br/>';
				$html .=  $team[$game['team2']]['team_shortname'].'<br/>';
				if ($game['team1_score'] != NULL){
					$html .=  $game['team1_score'].':'.$game['team2_score'];
				}
			}
			else {
				$html .=  'n.n.<br/>n.n.';
				}
			$html .=  '</th>';
		}
		$html .=  '<th class="tipcolumnone">'.Points.'</th>';
		$html .=  '</tr>';

		$sql = 	'SELECT u.ID, u.display_name, r.user_id, r.points, r.round, r.competition_id '.
				' FROM '.$wpdb->prefix.'pl_results r, '.$wpdb->prefix.'users u '.
				' WHERE r.round = %d '.
				' AND r.competition_id = %d '.
				' AND u.id = r.user_id'.
				' ORDER by r.points DESC';
		$users = $wpdb->get_results($wpdb->prepare($sql, $round, $competition['id']),ARRAY_A);
		if ($users){
			foreach($users as $user){
				$html .=  '<tr class="tiprow">';
				$html .=  '<td class="tipcolumnone">';
				$html .=  $user['display_name'];
				$html .=  '</td>';
				$results = pl_get_results_user($competition['id'], $round, $user['ID']);
				foreach($games as $game){
					$recent_user_tips='';
					$recent_user_tips = pl_get_recent_user_tips($game['id'], $user['ID']);
					$html .=  '<td class="tipcolumnone">';
					if ($recent_user_tips){
						$points = pl_get_score($recent_user_tips['id']);
						/* don't show the usertips before the game is over */
						if(time() > $game['gametime'] OR $game['team1_score'] != NULL){
							if ($points['points'] == $competition['points_one']){$style ='font-weight: bold;';}
							elseif ($points['points'] == $competition['points_two']){$style='font-weight: bold;';}
							elseif ($points['points'] == $competition['points_three']){$style='font-weight: bold;';}
							elseif ($points['points'] == $competition['points_four']){$style='font-weight: bold;';}
							else {$style='font-weight: normal;';}
							$html .=  '<font style="'.$style.'">'.$recent_user_tips['team1tip'].':'.$recent_user_tips['team2tip'].'</font>';
							$html .=  ' <font style="font-size:70%;'.$style.'">'.$points['points'].'</font>';										}
						else {
							$html .=  '?';
							}
						}
					$html .=  '</td>';
					}
				$html .=  '<td class="tipcolumnone">';
				$html .=  $results['points'];
				$html .=  '</td>';
				$html .=  '</tr>';
				}
			}
		$html .=  '</table>';
	}
	return $html;
}


/*
 * returns the Team Data
 * @team array (row)
 *
 *
 */
function pl_tip_get_team($team_id) {
global $wpdb;

$sql = 	'SELECT * FROM '.$wpdb->prefix.'pl_teams '.
		'WHERE id = %d';
$team = $wpdb->get_row($wpdb->prepare($sql, $team_id), ARRAY_A);
if ($team){return $team;}
else {return FALSE;}
}

/*
 * get the recent tips of an user and a game
 * @recenttips array (row)
 */
function pl_get_recent_user_tips($game_id, $user_id){
global $wpdb;
$sql = 	'SELECT * FROM '.$wpdb->prefix.'pl_tips '.
		'WHERE game_id = %d '.
		'AND user_id = %d ';
$recenttips = $wpdb->get_row($wpdb->prepare($sql, $game_id, $user_id), ARRAY_A);
if ($recenttips) {return $recenttips;}
else {return FALSE;}
}


/*
 * get a game by ID
 * @game array (row)
 */
function pl_get_game($game_id){
global $wpdb;
$sql = 	'SELECT * FROM '.$wpdb->prefix.'pl_games '.
		'WHERE id = %d ';
$game = $wpdb->get_row($wpdb->prepare($sql, $game_id), ARRAY_A);
if ($game) {return $game;}
else {return FALSE;}
}


/*
 * Updates the pl_tips table
 * @userID
 * @gameID
 */
function pl_update_tip($game_id, $team1tip, $team2tip, $user_id){
global $wpdb;
if(pl_get_recent_user_tips($game_id, $user_id)){
	$wpdb->update(
		  $wpdb->prefix.'pl_tips',
		  array( 'team1tip' => $team1tip, 'team2tip' => $team2tip ),
		  array( 'game_id' => $game_id, 'user_id' => $user_id ),
		  array( '%d', '%d'),
		  array( '%d', '%d')
		);
	}
else {
	$wpdb->insert(
		$wpdb->prefix.'pl_tips',
		array(
			'user_id' => $user_id,
			'game_id' => $game_id,
			'team1tip' => $team1tip,
			'team2tip' => $team2tip
		),
		array('%d','%d','%d','%d')
		);
	}
}


/**
 * prints the top menubar
 */
function pl_print_top_menu_tip($number_of_competitions, $view, $round, $competition_id){
	global $page_id, $user_ID, $wp_version;
	$html =  '<a name="menutip"></a>';
	$html .=  '<div>';
		$html .=  '<ul class = "pl_menu">';
			if ($number_of_competitions > 1){pl_print_competition_menu($view, $round, $competition_id);}
			$html .= pl_print_navigation($view, $round, $competition_id);
			if ($view=="tipgames" OR $view=="overview"){
				$html .= pl_print_rounds_menu($view, $round, $competition_id);
				}
			if ($view=="results"){
				$html .= pl_print_rounds_menu($view, $round, $competition_id, 1);
				}
		/* if the user is logged in. print the logout link */
		if ($user_ID){
			/* function wp_logout_url since wordpress 2.7 */
			if ($wp_version >= 2.7){
				$html .=  '<li><a href="'.wp_logout_url(get_permalink()).'&amp;redirect_to=index.php?page_id='.$page_id.'">'.Logout.'</a></li>';
			}
			else {
				$html .=  '<li><a href="'.get_option('home').'/wp-login.php?action=logout&amp;redirect_to=index.php?page_id='.$page_id.'">'.Logout.'</a></li>';
			}
		}
		/* if the user is not logged in print the register and the login link */
		if (!$user_ID){
			$html .=  '<li><a href="'.get_option('home').'/wp-login.php?action=register&amp;redirect_to=index.php?page_id='.$page_id.'">'.Register.'</a></li>';
			$html .=  '<li><a href="'.get_option('home').'/wp-login.php?redirect_to=index.php?page_id='.$page_id.'">'.Login.'</a></li>';
		}
		$html .=  '</ul>';
	$html .=  '</div>';
	$html .=  '<div class="clear"></div>';
	return $html;
}

/**
 * prints the navigation - tip, register, results, login/logout, stats
 */
function pl_print_navigation($view, $round, $competition_id){
	global $user_ID, $pl_options, $wp_version;
	$page_id = $pl_options['page_id'];
	$class['results'] = '';
	$class['tipgames'] = '';
	$class['overview'] = '';
	$class[$view] = 'active';
	if ($view!="results"){$roundresults = 0;}
	else {$roundresults = $round;}

		if ($user_ID){
			$html .=  '<li><a class="'.$class['tipgames'].'" href="?page_id='.$page_id.'&amp;view=tipgames&amp;round='.$round.'&amp;competition_id='.$competition_id.'#menutip">'.Tip.'</a></li>';
		}
		$html .=  '<li><a class="'.$class['results'].'" href="?page_id='.$page_id.'&amp;view=results&amp;round='.$roundresults.'&amp;competition_id='.$competition_id.'#menutip">'.Results.'</a></li>';
		$html .=  '<li><a class="'.$class['overview'].'" href="?page_id='.$page_id.'&amp;view=overview&amp;round='.$round.'&amp;competition_id='.$competition_id.'#menutip">'.Overview.'</a></li>';
	return $html;
}

/**
 * prints the competition Menu
 */
function pl_print_competition_menu($view, $round, $competition_id) {
	global $user_ID, $pl_options;
	$page_id = $pl_options['page_id'];
	$html .=  '<li><a href="#">';
	$competitions = pl_get_all_competitions();
	if ($competitions){
		foreach($competitions as $competition){
			$class = '';
			if ($competition_id == $competition['id']){
				$html .=  $competition['name'];
			}
		}
		$html .=  '</a><ul>';
		foreach($competitions as $competition){
			$class = '';
			$html .=  '<li class="roundmenu'.$class.'"><a href="?page_id='.$page_id.'&amp;view='.$view.'&amp;round='.$round.'&amp;competition_id='.$competition['id'].'#menutip">'.$competition['name'].' </a></li>';
		}
		$html .=  '</ul>';
	}
	$html .=  '</li>';
	return $html;
}

/**
 * prints the rounds Menu
 */
function pl_print_rounds_menu($view, $round, $competition_id, $master='') {
	global $user_ID, $pl_options;
	$page_id = $pl_options['page_id'];
	$competition = pl_get_competition($competition_id);
	if ($competition['round_names']){
		$round_names = pl_get_all_round_names($competition['round_names']);
		}
	$html .=  '<li><a href="#">';
		if ($round != 0){
			if (isset($round_names)){
				$html .=  $round_names[$round-1];
			}else {
				$html .=  $round.'. Spieltag';
			}
		} else {
			$html .=  Master;

		}
	$html .=  '</a>';
	$html .=  '<ul>';




	if ($master){
		$class = '';
		if ($round == 0){$class = 'active';}
		$html .=  '<li class="roundmenu'.$class.'"><a href="?page_id='.$page_id.'&amp;view='.$view.'&amp;round=0&amp;competition_id='.$competition_id.'#menutip">'.Master.' </a></li>';

		}
	for ($i = 1; $i <= $competition['rounds']; $i++){
			$class = '';
			if ($round == $i){$class = 'active';}
				if (isset($round_names[$i-1])){$round_name = $round_names[$i-1];}
				else {$round_name = $i.'. Spieltag';}
				$html .=  '<li class="roundmenu'.$class.'"><a href="?page_id='.$page_id.'&amp;view='.$view.'&amp;round='.$i.'&amp;competition_id='.$competition_id.'#menutip">'.$round_name.' </a></li>';
			}
	$html .=  '</ul>';
	$html .= '</li>';
	?>
	<?php
	return $html;
}

/**
 * gets the number of active competitions
 */
function pl_get_number_of_competitions() {
global $wpdb;
$sql = 	'SELECT COUNT(id) FROM '.$wpdb->prefix.'pl_competitions WHERE active = %d';
$number_of_competitions = $wpdb->get_var($wpdb->prepare($sql, 1));
if ($number_of_competitions) {return $number_of_competitions;}
else {return FALSE;}
}

/**
 * get the first active competition
 */
function pl_get_first_competition() {
global $wpdb;
$sql = 	'SELECT * FROM '.$wpdb->prefix.'pl_competitions WHERE active = %d ORDER by ID ASC LIMIT 1';
$competition = $wpdb->get_row($wpdb->prepare($sql, 1), ARRAY_A);
if ($competition) {return $competition;}
else {return FALSE;}
}

/**
 * get an competition by ID
 */
function pl_get_competition($id) {
global $wpdb;
$sql = 	'SELECT * FROM '.$wpdb->prefix.'pl_competitions WHERE id = %d ';
$competition = $wpdb->get_row($wpdb->prepare($sql, $id), ARRAY_A);
if ($competition) {return $competition;}
else {return FALSE;}
}

/**
 * get all active competition by ID
 */
function pl_get_all_competitions() {
global $wpdb;
$sql = 	'SELECT * FROM '.$wpdb->prefix.'pl_competitions WHERE active = %d ';
$competitions = $wpdb->get_results($wpdb->prepare($sql, 1), ARRAY_A);
if ($competitions) {return $competitions;}
else {return FALSE;}
}

function pl_get_games($competition_id, $round){
global $wpdb;
$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."pl_games WHERE round = %d AND competition_id = %d ORDER by gametime ASC", $round, $competition_id ), ARRAY_A );
if ($result){return $result;}
}


/*
 * get the results table
 * oderd by points
 */
function pl_get_results_table($competition_id, $round){
global $wpdb;
/* join with the wp_user table */
$sql = 	'SELECT r.id, r.points, r.round, r.competition_id, r.user_id, u.id, u.display_name '.
		' FROM '.$wpdb->prefix.'pl_results r, '.$wpdb->prefix.'users u '.
		' WHERE r.round = %d '.
		' AND r.competition_id = %d '.
		' AND u.id = r.user_id'.
		' ORDER by r.points DESC';
$result = $wpdb->get_results($wpdb->prepare($sql, $round, $competition_id),ARRAY_A);
if ($result){return $result;}
}

/*
 * get an user result
 */
function pl_get_results_user($competition_id, $round, $user_id){
global $wpdb;
$sql = 	' SELECT * FROM '.$wpdb->prefix.'pl_results '.
		' WHERE user_id  = %d '.
		' AND competition_id = %d '.
		' AND round = %d ';
$results = $wpdb->get_row($wpdb->prepare($sql, $user_id, $competition_id, $round), ARRAY_A);
if ($results) {return $results;}
else {return FALSE;}
}


/**
 * calculates the last calculated round
 */
function pl_calculate_lastround($competition_id){
global $wpdb;
$sql = 	'SELECT round FROM '.$wpdb->prefix.'pl_results'.
		' WHERE competition_id = %d '.
		' AND points != %d '.
		' ORDER by round DESC LIMIT 1';
$lastround = $wpdb->get_var($wpdb->prepare($sql, $competition_id, 0));
return $lastround;
}

/**
 * returns the position of an user
 */
function pl_get_position($user_id, $round, $competition_id) {
global $wpdb;
$sql = 	'SELECT position FROM '.$wpdb->prefix.'pl_results '.
		'WHERE user_id = %d '.
		'AND round = %d '.
		'AND competition_id = %d ';
$lastposition = $wpdb->get_var($wpdb->prepare($sql, $user_id, $round, $competition_id));
return $lastposition;
}


/** gets the picture for the up and down arrows
 *
 */
function pl_get_updown_picture($position, $lastposition, $size='') {
$differenz = $position - $lastposition;
if ($differenz == 0) {$picture = "stay";}
if ($differenz < 0) {$picture = "up";}
if ($differenz < -5) {$picture = "upred";}
if ($differenz > 0) {$picture = "down";}
if ($differenz > 5){$picture = "downred";}
if ($size == "small"){
	$picture .= "_small";
}
$picture = '<img class="arrows" src="'.plugins_url('graphics/'. $picture.'.gif', __FILE__).'">';
return $picture;
}

/**
 *  gets a roundname
 *
 */
function pl_get_round_name($round, $string){
	$rounds = explode(";", $string);
	if (count($rounds > 1) AND isset($rounds[$round-1])){
		$roundname = $rounds[$round-1];
	}
	if (!isset($roundname)){$roundname = $round.'. '.Round;}
return $roundname;
}
/**
 * gets all round names as an arry
 */
function pl_get_all_round_names($string) {
	$rounds = explode(";", $string);
	return $rounds;
}

function pl_get_team_picture($team){
if(file_exists(plugin_dir_path(__FILE__).'/flags/'.$team.'.gif'))
	{
	$picture = '<img class="teampic" width="20" height="15" src="'.plugins_url("/flags/".$team.".gif", __FILE__).'">';
	}
if(isset($picture)){return $picture;}
else {return FALSE;}
}

/**
 * get all competitions
 */
function pl_get_all_competitions_admin() {
global $wpdb;
$sql = 	'SELECT * FROM '.$wpdb->prefix.'pl_competitions WHERE %d ORDER by active DESC';
$competitions = $wpdb->get_results($wpdb->prepare($sql, 1), ARRAY_A);
if ($competitions) {return $competitions;}
else {return FALSE;}
}

/**
 * get all teams of a competition
 */
function pl_get_all_teams($competition_id) {
global $wpdb;
$sql = 	'SELECT * FROM '.$wpdb->prefix.'pl_teams WHERE competition_id = %d ';
$teams = $wpdb->get_results($wpdb->prepare($sql, $competition_id), ARRAY_A);
if ($teams) {return $teams;}
else {return FALSE;}
}


/**
 * get all users of a competition
 */
function pl_get_all_users() {
global $wpdb;
$sql = 	'SELECT * FROM '.$wpdb->prefix.'users WHERE %d';
$users = $wpdb->get_results($wpdb->prepare($sql, 1), ARRAY_A);
if ($users) {return $users;}
else {return FALSE;}
}

/**
 * prints the form for the competition options
 */
function pl_print_competition_option_form($competition_id) {
	if ($competition_id){$competition = pl_get_competition($competition_id);}
	else {
		$competition['name'] = '';
		$competition['rounds'] = '';
		$competition['points_one'] = '';
		$competition['points_two'] = '';
		$competition['points_three'] = '';
		$competition['points_four'] = '';
		$competition['tiptime'] = '';
		$competition['round_names'] = '';
		$competition['active'] = '';
	}

	echo '<div class="wrap">';
	echo '<h3>'.CompetitionOptions.'</h3>';
	echo '<form method="POST" action ="?page=competitions&amp;action=edit&amp;competition_id='.$competition_id.'&amp;do=change">';
	echo '<table class="widefat">';
		echo '<tr><td>'.CompetitionName.'</td><td><input type="text" size="100" name="name" value="'.$competition['name'].'"></td></tr>';
		echo '<tr><td>'.Active.'</td><td><input type="checkbox" name="active" value="1" ';
			if ($competition['active'] == 1){echo 'checked = "checked"';}
		echo '></td></tr>';
		echo '<tr><td>'.NumberOfRounds.'</td><td><input type="text" size="3" name="rounds" value="'.$competition['rounds'].'"></td></tr>';
		echo '<tr><td>'.points_one.'</td><td><input type="text" size="3" name="points_one" value="'.$competition['points_one'].'"></td></tr>';
		echo '<tr><td>'.points_two.'</td><td><input type="text" size="3" name="points_two" value="'.$competition['points_two'].'"></td></tr>';
		echo '<tr><td>'.points_three.'</td><td><input type="text" size="3" name="points_three" value="'.$competition['points_three'].'"></td></tr>';
		echo '<tr><td>'.points_four.'</td><td><input type="text" size="3" name="points_four" value="'.$competition['points_four'].'"></td></tr>';
		echo '<tr><td>'.Tiptime.'</td><td><input type="text" size="3" name="tiptime" value="'.$competition['tiptime'].'"></td></tr>';
		echo '<tr><td>'.RoundsHaveNames.'</td><td><textarea name="round_names">'.$competition['round_names'].'</textarea></td></tr>';
		echo '<tr><td colspan="2"><input type="submit" value="'.CompetitionOptionSubmit.'"></td></tr>';

	echo '</table>';
	echo '</form>';
	echo '</div>';

}


/**
 * prints the main option form
 *
 */
function pl_print_main_option_form($pl_options) {
$languages = pl_get_language_files();
echo '<form name ="options" method = "POST" action="?page=predictionleague&amp;action=editoptions">';
	echo '<table class="widefat">';
		echo '<tr><td width="15%">'.PageId.'</td><td width="15%"><input type="text" maxlength="8" size="6" name="page_id" value="'.$pl_options['page_id'].'"></td><td>'.PageIdDesc.'</td></tr>';
		echo '<tr>';
			echo '<td width="15%">'.Color1.'</td>';
			echo '<td>';
			echo '<div id="color1_div" style="border:1px solid; background-color:#'.$pl_options['color1'].';float:left;width:25px; height:25px; cursor:pointer;" onclick="show_picker(\'color1\',\''.$pl_options['color1'].'\',\''.$pl_options['color1'].'\');">&nbsp;</div>';
			echo '<input type="text" id="color1" maxlength="6" size="6" name="color1" value="'.$pl_options['color1'].'"  onclick="show_picker(this.id, \'\',\'\');"></td>';
			echo '<td>'.Color1Desc.'</td>';
		echo '</tr>';

		echo '<tr>';
			echo '<td width="15%">'.Color2.'</td>';
			echo '<td>';
			echo '<div id="color2_div" style="border:1px solid; background-color:#'.$pl_options['color2'].';float:left;width:25px; height:25px; cursor:pointer;" onclick="show_picker(\'color2\',\''.$pl_options['color2'].'\',\''.$pl_options['color2'].'\');">&nbsp;</div>';
			echo '<input type="text" id="color2" maxlength="6" size="6" name="color2" value="'.$pl_options['color2'].'"  onclick="show_picker(this.id, \'\',\'\');"></td>';
			echo '<td>'.Color2Desc.'</td>';
		echo '</tr>';
		echo '<tr>';
			echo '<td width="15%">'.Color3.'</td>';
			echo '<td>';
			echo '<div id="color3_div" style="border:1px solid; background-color:#'.$pl_options['color3'].';float:left;width:25px; height:25px; cursor:pointer;" onclick="show_picker(\'color3\',\''.$pl_options['color3'].'\',\''.$pl_options['color3'].'\');">&nbsp;</div>';
			echo '<input type="text" id="color3" maxlength="6" size="6" name="color3" value="'.$pl_options['color3'].'"  onclick="show_picker(this.id, \'\',\'\');"></td>';
			echo '<td>'.Color3Desc.'</td>';
		echo '</tr>';
		echo '<tr>';
			echo '<td width="15%">'.Color4.'</td>';
			echo '<td>';
			echo '<div id="color4_div" style="border:1px solid; background-color:#'.$pl_options['color4'].';float:left;width:25px; height:25px; cursor:pointer;" onclick="show_picker(\'color4\',\''.$pl_options['color4'].'\',\''.$pl_options['color4'].'\');">&nbsp;</div>';
			echo '<input type="text" id="color4" maxlength="6" size="6" name="color4" value="'.$pl_options['color4'].'"  onclick="show_picker(this.id, \'\',\'\');"></td>';
			echo '<td>'.Color4Desc.'</td>';
		echo '</tr>';
		echo '<tr>';
			echo '<td>'.Language.'</td>';
			echo '<td><select name="language">';
				foreach ($languages as $language){
					$language_name = explode(".", $language);
					echo '<option value="'.$language_name[0].'" ';
						if ($pl_options['language'] == $language_name[0]){echo 'selected';}
					echo '>'.$language_name[0].'</option>'	;
				}
			echo '</select></td>';
			echo '<td>'.LanguageDesc.'</td>';
		echo '</tr>';
		echo '<tr>';
			echo '<td width="15%">'.ShowLink.'</td>';
			echo '<td>';
			echo '<input type="checkbox" name="showlink" ';
			if ($pl_options['showlink'] == "on"){
				echo 'checked = "checked"';
			}
			echo '></td>';
			echo '<td>'.ShowLinkDesc.'</td>';
		echo '</tr>';
		echo '<tr><td colspan="3"><input type="submit" value="'.ChangeOptions.'"></td></tr>';
	echo '</table>';
echo '</form>';
}



/*
 * print the menu for managing the teams
 */
function pl_print_manage_teams($competition_id){
	echo '<div class="wrap">';
	/* print advice */
	echo PrintManageTeamAdvice;

	/* print the new team form */
	echo '<h3>'.InsertNewTeam.'</h3>';
	echo '<form method="POST" action ="?page=competitions&amp;action=edit&amp;competition_id='.$competition_id.'&amp;do=changeteam">';
	echo '<table class="widefat">';
		echo '<tr><td>'.TeamShortName.'</td><td><input type="text" size="3" maxsize="3" name="team_shortname"></td><td>'.TeamName.'</td><td><input type="text" size="50" name="team_name"></td><td><input type="submit" value="'.SubmitNewTeam.'"></td></tr>';
	echo '</table>';
	echo '</form>';
	echo '<hr>';

	/* get all teams and put them into a form */
	$teams = pl_get_all_teams($competition_id);
	if ($teams){
		echo '<h3>'.ChangeTeams.'</h3>';
		echo '<table class="widefat">';
		foreach ($teams as $team){
			echo '<form method="POST" action ="?page=competitions&amp;action=edit&amp;competition_id='.$competition_id.'&amp;do=changeteam&amp;team_id='.$team['id'].'">';
			echo '<table class="widefat">';
				echo '<tr>';
				echo '<td>'.TeamShortName.'</td>';
				echo '<td><input type="text" size="3" maxsize="3" name="team_shortname" value="'.$team['team_shortname'].'"></td>';
				echo '<td>'.TeamName.'</td>';
				echo '<td><input type="text" size="50" name="team_name" value="'.$team['team_name'].'"></td>';
				echo '<td><input type="submit" value="'.ChangeTeam.'"></td>';
				echo '<td><a href="?page=competitions&amp;action=edit&amp;competition_id='.$competition_id.'&amp;do=deleteteam&amp;team_id='.$team['id'].'">'.DeleteTeam.'</td>';
				echo '</tr>';
			echo '</table>';
			echo '</form>';
			}
		echo '</table>';
		}
	echo '</div>';
}

/**
 * prints the form for managing the results
 */
function pl_print_manage_results($competition_id, $round= ''){
if ($competition_id){$competition = pl_get_competition($competition_id);}
if (!$round){$round = $competition['next_round'] - 1;}
if (!$round){$round = 1;}
//$teams = pl_get_all_teams($competition_id);
	echo '<div class="wrap">';
	/* print advice */
	echo PrintManageResultsAdvice;
	/* menu for the rounds  */
	$link = '?page=competitions&amp;action=manageresults&amp;competition_id='.$competition_id;
	pl_print_round_menu_admin($competition, $link);
	$games = pl_get_games($competition_id, $round);
	if ($games){
		/* set the counter */
		$numberofgames = 0;
		echo '<h3>'.ManageResults.': '.pl_get_round_name($round, $competition['round_names']).'</h3>';
		echo '<form method="POST" action ="?page=competitions&amp;action=manageresults&amp;competition_id='.$competition_id.'&amp;round='.$round.'&amp;do=sendresults">';
		echo '<table class="widefat">';
		foreach ($games as $game){
			$team1 = pl_tip_get_team($game['team1']);
			$team2 = pl_tip_get_team($game['team2']);
			$date = date("d.m.y",$game['gametime']);
			$time = date("G:i",$game['gametime']);
			$numberofgames = $numberofgames + 1;
			echo '<tr>';
				echo '<td>';
					echo $date.'<br/>'.$time.' '.Time;
				echo '</td>';
				echo '<td>';
					$picture1 = pl_get_team_picture($team1['team_shortname']);
					$picture2 = pl_get_team_picture($team2['team_shortname']);
					echo $picture1.' '.$team1['team_name'].'<br/>'.$picture2.' '.$team2['team_name'];
				echo '</td>';
				echo '<td>';
					echo '<input class = "tipinput" type="text" name="team1_score-'.$numberofgames.'" size=3 maxlength=3 value="'.$game['team1_score'].'">';
					echo '<br/>';
					echo '<input class = "tipinput" type="text" name="team2_score-'.$numberofgames.'" size=3 maxlength=3 value="'.$game['team2_score'].'">';
					// Sends the GameID
					echo '<input type="hidden" name="game_id-'.$numberofgames.'" value="'.$game['id'].'">';
				echo '</td>';
			echo '</tr>';
		}
		echo '<input type="hidden" name="numberofgames" value="'.$numberofgames.'">';

		echo '<tr><td colspan="4"><input name="savedraft" value = 1 type="checkbox"> ';
		echo SaveAsDraftIn;
		echo '</td></tr>';


		echo '<tr><td colspan="4"><input type="Submit" value="'.SendResultsAndCalculate.'"></td></tr>';
		echo '</table>';
	}
	echo '</div>';
}

/**
 * the game management section
 */
function pl_print_manage_games($competition_id, $round = ''){
if (!$round){$round = 1;}

if ($competition_id){$competition = pl_get_competition($competition_id);}
$teams = pl_get_all_teams($competition_id);
	echo '<div class="wrap">';
	/* print advice */
	echo PrintManageGamesAdvice;
	/* menu for the rounds  */
	$link = '?page=competitions&amp;action=edit&amp;competition_id='.$competition_id.'&amp;do=managegames';
	pl_print_round_menu_admin($competition, $link);
	/* new game */
	echo '<h3>'.InsertNewGame.': '.pl_get_round_name($round, $competition['round_names']).'</h3>';
	$numberofgames = 1;
	echo '<form method="POST" action ="?page=competitions&amp;action=edit&amp;competition_id='.$competition_id.'&amp;round='.$round.'&amp;do=changegame">';
	echo '<table class="widefat">';
		echo '<tr>';
			echo '<td>'.InsertDate.' ';
				echo '<input name = "day-'.$numberofgames.'" type="text" size = "2" maxlength="2">';
				echo '<input name = "month-'.$numberofgames.'" type="text" size = "2" maxlength="2">';
				echo '<input name = "year-'.$numberofgames.'" type="text" size = "4" maxlength="4">';
			echo '</td>';
			echo '<td>'.InsertTime.' ';
				echo '<input name = "hour-'.$numberofgames.'" type="text" size = "2" maxlength="2">';
				echo '<input name = "minute-'.$numberofgames.'" type="text" size = "2" maxlength="2">';
			echo '</td>';
			echo '<td>';
				echo '<select name="team1-'.$numberofgames.'">';
				echo pl_print_teams_in_select($teams, $competition_id, '');
				echo '</select>';
				echo ' vs. ';
				echo '<select name="team2-'.$numberofgames.'">';
				echo pl_print_teams_in_select($teams, $competition_id, '');
				echo '</select>';
			echo '</td>';
			echo '<input type="hidden" name="numberofgames" value="'.$numberofgames.'">';
			echo '<td><input type="submit" value="'.InsertNewGame.'"></td>';
		echo '</tr>';
	echo '</table>';
	echo '</form>';
	echo '<hr>';
	/* update game */
	$games = pl_get_games($competition_id, $round);
	if ($games){
		/* set the counter */
		$numberofgames = 0;
		echo '<h3>'.ChangeGames.': '.pl_get_round_name($round, $competition['round_names']).'</h3>';
		echo '<form method="POST" action ="?page=competitions&amp;action=edit&amp;competition_id='.$competition_id.'&amp;round='.$round.'&amp;do=changegame">';
		echo '<table class="widefat">';
		foreach ($games as $game){
			$numberofgames = $numberofgames + 1;
			echo '<tr>';
				echo '<td>'.InsertDate.' ';
					echo '<input name = "day-'.$numberofgames.'" type="text" size = "2" maxlength="2" value="'.date("d",$game['gametime']).'">';
					echo '<input name = "month-'.$numberofgames.'" type="text" size = "2" maxlength="2" value="'.date("m",$game['gametime']).'">';
					echo '<input name = "year-'.$numberofgames.'" type="text" size = "4" maxlength="4" value="'.date("Y",$game['gametime']).'">';
				echo '</td>';
				echo '<td>'.InsertTime.' ';
					echo '<input name = "hour-'.$numberofgames.'" type="text" size = "2" maxlength="2" value="'.date("H",$game['gametime']).'">';
					echo '<input name = "minute-'.$numberofgames.'" type="text" size = "2" maxlength="2" value="'.date("i",$game['gametime']).'">';
				echo '</td>';
				echo '<td>';
					echo '<select name="team1-'.$numberofgames.'">';
					echo pl_print_teams_in_select($teams, $competition_id, $game['team1']);
					echo '</select>';
					echo ' vs. ';
					echo '<select name="team2-'.$numberofgames.'">';
					echo pl_print_teams_in_select($teams, $competition_id, $game['team2']);
					echo '</select>';
					echo '<input type="hidden" name="game_id-'.$numberofgames.'" value="'.$game['id'].'">';
				echo '</td>';
				echo '<td><a href="?page=competitions&amp;action=edit&amp;competition_id='.$competition_id.'&amp;round='.$round.'&amp;do=deletegame&amp;game_id='.$game['id'].'">'.DeleteGame.'</a></td>';
			echo '</tr>';
		}
		echo '<input type="hidden" name="numberofgames" value="'.$numberofgames.'">';
		echo '<tr><td colspan="4"><input type="Submit" value="'.ChangeRound.'"></td></tr>';
		echo '</table>';
	}
	echo '</div>';
}

function pl_print_manage_usertips($competition_id, $round) {
/* round menu */
global $wpdb, $page_id;
if ($competition_id){$competition = pl_get_competition($competition_id);}
if (!$round){$round = $competition['next_round'] - 1;}
if (!$round){$round = 1;}
	$games = pl_get_games($competition_id, $round);
	$teams = pl_get_all_teams($competition_id);
	echo '<div class="wrap">';
	/* menu for the rounds  */
	$link = '?page=competitions&amp;action=manageusertips&amp;competition_id='.$competition_id;
	pl_print_round_menu_admin($competition, $link);
	if ($teams){
		foreach ($teams as $teams){
			$team[$teams['id']]['team_shortname'] = $teams['team_shortname'];
			}
	}
	if($games){
		echo '<table class="widefat">';
		echo '<tr class="tiprow">';
		echo '<th class="tipcolumnone">'.User.'</th>';
		foreach ($games as $game){
			echo '<th class="tipcolumnone">';
			echo $team[$game['team1']]['team_shortname'].'<br/>';
			echo $team[$game['team2']]['team_shortname'].'<br/>';
			if ($game['team1_score'] != NULL){
				echo $game['team1_score'].':'.$game['team2_score'];
			}
			echo '</th>';
		}
		echo '</tr>';
		$users = pl_get_all_users();
		if ($users){
			foreach($users as $user){
				echo '<form method="POST" action ="?page=competitions&amp;action=manageusertips&amp;competition_id='.$competition_id.'&amp;round='.$round.'&amp;do=editusertips&amp;userID='.$user['ID'].'">';
				echo '<tr class="tiprow">';
				echo '<td class="tipcolumnone">';
				echo $user['display_name'];
				echo '</td>';
				$results = pl_get_results_user($competition['id'], $round, $user['ID']);
				foreach($games as $game){
					$recent_user_tips='';
					$recent_user_tips = pl_get_recent_user_tips($game['id'], $user['ID']);
					echo '<td class="tipcolumnone">';
						echo '<input class = "tipinput" type="text" name="team1-'.$user['ID'].'-'.$game['id'].'" size=3 maxlength=3 value="'.$recent_user_tips['team1tip'].'">';
						echo '<input class = "tipinput" type="text" name="team2-'.$user['ID'].'-'.$game['id'].'" size=3 maxlength=3 value="'.$recent_user_tips['team2tip'].'">';
					echo '</td>';
					}
				echo '<td class="tipcolumnone">';
					echo '<input type = "submit" value="'.EditUserTips.'">';
				echo '</td>';
				echo '</tr>';
				echo '</form>';
				}
			}
		echo '</table>';
	}
echo '</div>';
}


function pl_print_teams_in_select($teams, $competition_id, $selected_team){
	$print_teams_in_select_string = '';
	$print_teams_in_select_string .= '<option value="0">n.n.</option>';
	if(!empty($teams))
			{
			foreach ($teams as $team) {
			$print_teams_in_select_string .= '<option value="'.$team['id'].'"';
			if ($selected_team == $team['id']){$print_teams_in_select_string .= ' selected';}
			$print_teams_in_select_string .= '>'.$team['team_name'].'</option>';
			}}
	return $print_teams_in_select_string;
}


/*
 * print the round menu for managing the games and results
 */
function pl_print_round_menu_admin($competition, $link) {
if ($competition['round_names']){
	$round_names = pl_get_all_round_names($competition['round_names']);
	}
echo '<table class="widefat">';
echo '<tr><td><h3>'.Round.':</h3>';
for ($i = 1; $i <=$competition['rounds']; $i++){
	if (isset($round_names[$i-1])){$round_name = $round_names[$i-1];}
	else {$round_name = $i.'.';}
	echo '<div style="width=10px; float: left; margin-right: 5px;"><a href="'.$link.'&amp;round='.$i.'">'.$round_name.'</a></div>';
	}
echo '</td></tr></table>';
echo '<hr>';
}


function pl_print_main_menu_competitions() {
$competitions = pl_get_all_competitions_admin();
?>
<div class="wrap">
<table class="widefat">
  <thead>
  <tr>
    <th scope="col" style="text-align: center"><?php _e('ID') ?></th>
    <th scope="col"><?php echo Competition; ?></th>
	<th scope="col"><?php echo Status ?></th>
	<th scope="col" style="text-align: center"><?php _e('Action'); ?></th>
  </tr>
  </thead>
  <tbody id="the-list">
<?php

if ($competitions){
	foreach ($competitions as $competition){
		if ($competition['active'] == 1){$activestring = active;}
		if ($competition['active'] == 0){$activestring = notactive;}
		?>
		<tr>
		    <td><?php echo $competition['id']; ?></td>
	    	<td><?php echo $competition['name']; ?></td>
	    	<td><?php echo $activestring; ?></td>
	    	<td>
	    		<a href="admin.php?page=competitions&amp;action=edit&amp;competition_id=<?php echo $competition['id'];?>"><?php echo EditCompetition;?></a>
	    		<a href="admin.php?page=competitions&amp;action=edit&amp;do=deletecompetition&amp;competition_id=<?php echo $competition['id'];?>"><?php echo DeleteCompetition;?></a>
	    		<a href="admin.php?page=competitions&amp;action=manageresults&amp;competition_id=<?php echo $competition['id'];?>"><?php echo ManageResults;?></a>
	    		<a href="admin.php?page=competitions&amp;action=export&amp;competition_id=<?php echo $competition['id'];?>"><?php echo ExportCompetition;?></a>

	    	</td>
	    </tr>
		<?php
	}}
?>
  </tbody>
</table>

<h3><a href="admin.php?page=competitions&amp;action=edit&amp;do=new"><?php echo NewCompetition; ?></a></h3>
<h3><a href="admin.php?page=competitions&amp;action=importcompetition_buli"><?php echo ImportBuli; ?></a></h3>
<h3><a href="admin.php?page=competitions&amp;action=importcompetition_buli2"><?php echo ImportBuli2; ?></a></h3>
<h3><a href="admin.php?page=competitions&amp;action=importcompetition_buliopenDB"><?php echo ImportOpenDB; ?></a></h3>

<form enctype="multipart/form-data" method="POST" action ="admin.php?page=competitions&amp;action=importcompetition">
	<input type="file" name="importxml">
	<input type="submit" value="<?php echo ImportCompetition; ?>">
</form>
</div>
<?php
}



function pl_print_sub_menu_competitions($competition_id){
	echo '<div class="wrap">';
	echo '<table class="widefat">';
		echo '<tr>';
			echo '<td><h3><a href="?page=competitions&amp;action=edit&amp;competition_id='.$competition_id.'&amp;do=">'.ManageOptions.'</a></h3></td>';
			echo '<td><h3><a href="?page=competitions&amp;action=edit&amp;competition_id='.$competition_id.'&amp;do=manageteams">'.ManageTeams.'</a></h3></td>';
			echo '<td><h3><a href="?page=competitions&amp;action=edit&amp;competition_id='.$competition_id.'&amp;do=managegames">'.ManageGames.'</a></h3></td>';
			echo '<td><h3><a href="?page=competitions&amp;action=manageresults&amp;competition_id='.$competition_id.'&amp;do=manageresults">'.ManageResults.'</a></h3></td>';

			echo '<td><h3><a href="?page=competitions&amp;action=manageusertips&amp;competition_id='.$competition_id.'&amp;do=manageusertips">'.ManageUserTips.'</a></h3></td>';


		echo '</tr>';
	echo '</table>';
	echo '</div>';
}




/*
 * updates or inserts a competition
 * since 1.0
 * updated 2.0
 */
function pl_save_competition($form) {
// if there is no id, insert a new entry
if (!isset($form['id']) OR !($form['id'])){
	$values[] = array("col" => "id", "value" => "");
	$form['id'] = pl_insert_record('competitions', $values);
	unset($values);
	}
if ($form['id']){
	foreach ($form as $key => $value){
		$values[] = array("col" => $key, "value" => $value);
	}
	$cond[] = array("col" => "id", "value" => $form['id']);
	pl_update_record('competitions', $cond, $values);
	}
	return $form['id'];
}


/*
 * updates or inserts a game
 */
function pl_save_game($form) {
global $wpdb;
/* if there is no id, insert a new entry */
if (!isset($form['id'])){
	foreach ($form as $key => $value){
		$values[] = array("col" => $key, "value" => $value);
		}
	$thisid = pl_insert_record('games', $values);
	unset($values);
	}
if (isset($form['id']) AND $form['id']){
	foreach ($form as $key => $value){
		$values[] = array("col" => $key, "value" => $value);
	}
	$cond[] = array("col" => "id", "value" => $form['id']);
	pl_update_record('games', $cond, $values);
	}
}


/*
 * deletes a game
 */
function pl_delete_game($form) {
global $wpdb;
if ($form['id']){
	if ($wpdb->delete($wpdb->prefix.'pl_games', array('id' => $form['id']))){return TRUE;}
	}
}


/**
 * updates the game entry with the results
 */
function pl_save_results($form) {
global $wpdb;
if ($form['id']){
	foreach ($form as $key => $value){
		$values[] = array("col" => $key, "value" => $value);
		}
	$cond[] = array("col" => "id", "value" => $form['id']);
	pl_update_record('games', $cond, $values);
	}
}


/*
 * updates or inserts a team
 * since 1.0
 * updated 2.0
 */
function pl_save_team($form) {
	foreach ($form as $key => $value){
		$values[] = array("col" => $key, "value" => $value);
	}
	if (!isset($form['id']) OR $form['id'] < 1){
		$id = pl_insert_record("teams", $values);
		}
	elseif ($form['id']){
		$cond[] = array("col" => "id", "value" => $form['id']);
		$id = pl_update_record("teams", $cond, $values);
		}
	if (isset($id)){return $id;}
}

/*
 * deletes a team
 * since 1.0
 * updated 2.0
 */
function pl_delete_team($form) {
if ($form['id']){
	$cond[] = array("col" => "id", "value" => $form['id']);
	pl_delete_record("teams", $cond);
	}
}

/*
 * deletes a competition and all entries
 */
function pl_delete_competition($competition_id) {
global $wpdb;
if ($competition_id){
	$competition = pl_get_competition($competition_id);
	/* get all games and delete the user and user points tips for these games */
	for ($x=1; $x<=$competition['rounds']; $x++){
		$games = pl_get_games($competition_id, $x);
		if($games){
			foreach ($games as $game){
			$wpdb->delete($wpdb->prefix.'pl_tips', array("game_id" => $game['id']));
			$wpdb->delete($wpdb->prefix.'pl_points', array("game_id" => $game['id']));
			}}
	}
	$wpdb->delete($wpdb->prefix.'pl_teams', array("competition_id" => $competition_id));
	$wpdb->delete($wpdb->prefix.'pl_games', array("competition_id" => $competition_id));
	$wpdb->delete($wpdb->prefix.'pl_competitions', array("id" => $competition_id));
	}
}

/*
 * checks if all tables are created
 * there must be 6 tables
 */
function pl_check_tables(){
	global $wpdb;
	$result = $wpdb->query('SHOW TABLES LIKE "'.$wpdb->prefix.'pl_%"');
	if ($result == 6){return TRUE;}
	elseif ($result == 7){
		//Update pl_br_bracket
		$sql = 	' DROP TABLE IF EXISTS `'.$wpdb->prefix.'pl_br_userbrackets`';
			if(!$wpdb->query($sql)){return TRUE;}
	}
	else {return FALSE;}
	}

/*
 * creates all tip tables
 */
 /* TODO Check ob alle Tabellen immer noch so heissen und alle Felder besonders die NULL gesetzten richtig angelegt werden */
function pl_create_tables(){
	global $wpdb;
	$error = FALSE;
	$sql = 	' CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'pl_competitions` ('.
	  		'`id` int(10) unsigned NOT NULL auto_increment,'.
	  		'`name` varchar(100) collate latin1_general_ci NOT NULL,'.
	  		'`rounds` int(2) NOT NULL,' .
	  		'`round_names` text collate latin1_general_ci,'.
	  		'`next_round` int(2) NOT NULL,'.
			'`points_one` int(11) NOT NULL,'.
		  	'`points_two` int(11) NOT NULL,'.
		  	'`points_three` int(11) NOT NULL,'.
	 	 	'`points_four` int(11) NOT NULL,'.
			'`active` tinyint(1) NOT NULL,'.
			'`tiptime` int(11) NOT NULL,'.
			'PRIMARY KEY  (`id`)'.
			')';
	if(!$wpdb->query($sql)){$error = TRUE;}
	$sql = 	'CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'pl_games` ('.
			'  `id` int(10) unsigned NOT NULL auto_increment,'.
			'  `gametime` int(11) NOT NULL,'.
			'  `team1` int(11) NOT NULL,'.
			'  `team2` int(11) NOT NULL,'.
			'  `team1_score` int(11),'.
			'  `team2_score` int(11),'.
			'  `competition_id` int(11) NOT NULL ,'.
			'  `round` int(11) NOT NULL,'.
			'  PRIMARY KEY  (`id`)'.
			'	)';
	if(!$wpdb->query($sql)){$error = TRUE;}
	$sql = 'CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'pl_points` ('.
	  		'`id` int(10) unsigned NOT NULL auto_increment,'.
	 		'`user_id` int(11) NOT NULL,'.
	 		'`game_id` int(11) NOT NULL,'.
	 		'`tip_id` int(11) NOT NULL,'.
	  		'`points` int(11) default NULL,'.
	 		' PRIMARY KEY  (`id`)'.
			')';
	if(!$wpdb->query($sql)){$error = TRUE;}
	$sql = 'CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'pl_results` ('.
			'`id` int(10) unsigned NOT NULL auto_increment,'.
			' `user_id` int(11) NOT NULL,'.
			' `points` int(11) NOT NULL,'.
			' `round` int(11) NOT NULL,'.
			' `competition_id` int(11) NOT NULL,'.
			' `position` int(11) default NULL,'.
			' PRIMARY KEY  (`id`)'.
			' )';
	if(!$wpdb->query($sql)){$error = TRUE;}
	$sql = 'CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'pl_teams` ('.
			' `id` int(10) unsigned NOT NULL auto_increment,'.
			'`team_shortname` char(3) character set utf8 NOT NULL,'.
			'`team_name` varchar(50) character set utf8 NOT NULL,'.
			'`competition_id` int(11) NOT NULL,'.
			' PRIMARY KEY  (`id`)'.
			')';
	if(!$wpdb->query($sql)){$error = TRUE;}
	$sql = 'CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'pl_tips` ('.
			' `id` int(10) unsigned NOT NULL auto_increment,'.
			' `user_id` int(11) NOT NULL,'.
			' `game_id` int(11) NOT NULL,'.
			' `team1tip` int(11) default NULL,'.
			' `team2tip` int(11) default NULL,'.
			' PRIMARY KEY  (`id`)'.
			')	';
	if(!$wpdb->query($sql)){$error = TRUE;}
	if ($error == "TRUE"){return TRUE;}
	else {return FALSE;}
}

/*
 * delete all tip tables
 */
function pl_delete_tables(){
	global $wpdb;
	$error = FALSE;
	$sql = 'DROP TABLE IF EXISTS`'.$wpdb->prefix.'pl_competitions`';
	if(!$wpdb->query($sql)){$error = TRUE;}
	$sql = 'DROP TABLE IF EXISTS`'.$wpdb->prefix.'pl_games`';
	if(!$wpdb->query($sql)){$error = TRUE;}
	$sql = 'DROP TABLE IF EXISTS`'.$wpdb->prefix.'pl_points`';
	if(!$wpdb->query($sql)){$error = TRUE;}
	$sql = 'DROP TABLE IF EXISTS`'.$wpdb->prefix.'pl_results`';
	if(!$wpdb->query($sql)){$error = TRUE;}
	$sql = 'DROP TABLE IF EXISTS`'.$wpdb->prefix.'pl_teams`';
	if(!$wpdb->query($sql)){$error = TRUE;}
	$sql = 'DROP TABLE IF EXISTS`'.$wpdb->prefix.'pl_tips`';
	if(!$wpdb->query($sql)){$error = TRUE;}
	$sql = 'DROP TABLE IF EXISTS`'.$wpdb->prefix.'pl_br_userbrackets`';
	if(!$wpdb->query($sql)){$error = TRUE;}
	if ($error == "TRUE"){return TRUE;}
	else {return FALSE;}
}


/**
 * calculates the points of the competition and the round
 */
function pl_calculate_points($competition_id, $round){
	/* get the competition settings for the points */
	if ($competition_id){$competition = pl_get_competition($competition_id);}
	/* get all games of the round */
	$games = pl_get_games($competition_id, $round);
	if ($games){
		foreach ($games as $game){
		if ($game['team1_score'] != NULL){
			$difference = $game['team1_score'] - $game['team2_score'];
			if ($difference == 0){$tendency = 0;}
			if ($difference < 0 ){$tendency = 2;}
			if ($difference > 0 ){$tendency = 1;}
			$tips = pl_get_all_tips($game['id']);
			/* get all tips for this game */
			if ($tips){
				foreach ($tips as $tip){
					$tip_difference = $tip['team1tip'] - $tip['team2tip'];
					if ($tip_difference == 0){$tip_tendency = 0;}
					if ($tip_difference < 0 ){$tip_tendency = 2;}
					if ($tip_difference > 0 ){$tip_tendency = 1;}
					/* now check the points */
					/* points1 */
					if ($difference == $tip_difference AND $tip['team1tip'] == $game['team1_score']){
						$points = $competition['points_one'];}
					elseif ($difference == $tip_difference AND $tendency != 0){
						$points = $competition['points_two'];}
					elseif ($difference == $tip_difference AND $tendency == 0){
						$points = $competition['points_three'];}
					elseif ($tendency == $tip_tendency){
						$points = $competition['points_four'];}
					else {$points = 0;}
					/* write the score */
					pl_update_score($points, $tip['id'], $game['id']);
				}}
			}
		}}
		/* calculate the user points for the round	 */
		pl_calculate_user_points($competition_id, $round);
		/* calculate the master user points	 */
		pl_calculate_user_points_master($competition_id);
		pl_calculate_user_positions($competition_id, $round);
}


/**
 * calculates and writes the user positions
 */
function pl_calculate_user_positions($competition_id, $round){
global $wpdb;
$sql = 	'SELECT SUM(points), user_id '.
		' FROM '.$wpdb->prefix.'pl_results '.
		' WHERE round <= %d '.
		' AND round != %d '.
		' AND competition_id = %d '.
		' group by user_id '.
		' ORDER by SUM(points) DESC';
$results = $wpdb->get_results($wpdb->prepare($sql, $round, 0, $competition_id),ARRAY_A);
$place = 0; 			/* Counter */
$position = 0;			/* Position */
$lastuserpoints = 0;  	/* To manage even scores of the users */
if($results){
  foreach ($results as $user){
		/* To manage even scores of the users */
		$showplace = TRUE;
		if ($lastuserpoints == $user['SUM(points)']) {$showplace = "FALSE";}
		$lastuserpoints = $user['SUM(points)'];
		$place = $place + 1;
		if ($showplace == "TRUE"){$position = $place;}
		pl_update_user_position($competition_id, $round, $user['user_id'], $place);
		}
	}
}



/**
 * calculates the user points for one round
 * gets all the points of the games and write a new entry to the results table
 */
function pl_calculate_user_points($competition_id, $round){
global $wpdb;

$sql = 	'SELECT SUM(p.points), p.game_id, p.tip_id, t.id, t.user_id, t.game_id, g.id, g.round, g.competition_id '.
		' FROM '.$wpdb->prefix.'pl_points p, '.$wpdb->prefix.'pl_games g, '.$wpdb->prefix.'pl_tips t'.
		' WHERE g.round = %d '.
		' AND g.competition_id = %d '.
		' AND t.game_id = g.id'.
		' AND p.game_id = g.id'.
		' AND t.id = p.tip_id'.
		' GROUP by t.user_id';
$users = $wpdb->get_results($wpdb->prepare($sql, $round, $competition_id), ARRAY_A);
if($users){
	foreach ($users as $user){
		pl_update_user_results($competition_id, $round, $user['user_id'], $user['SUM(p.points)']);
	}}
}


/**
 * calculates the maser user points for one competition
 * gets all the points of the games and write a new entry to the results table
 * the master score has the round attribute 0
 */
function pl_calculate_user_points_master($competition_id){
global $wpdb;
$sql = 	'SELECT SUM(points), user_id, competition_id '.
		' FROM '.$wpdb->prefix.'pl_results '.
		' WHERE competition_id = %d'.
		' AND round != %d '.
		' GROUP by user_id';
$users = $wpdb->get_results($wpdb->prepare($sql, $competition_id, 0), ARRAY_A);
if($users){
	foreach ($users as $user){
		pl_update_user_results($competition_id, 0, $user['user_id'], $user['SUM(points)']);
	}}
}


/**
 * get all tips for one game
 */
function pl_get_all_tips($game_id){
global $wpdb;
$sql = 	'SELECT * FROM '.$wpdb->prefix.'pl_tips WHERE game_id = %d';
$tips = $wpdb->get_results($wpdb->prepare($sql, $game_id), ARRAY_A);
if ($tips) {return $tips;}
else {return FALSE;}
}

/**
 * updates or inserts a score in the pl_points table
 */
function pl_update_score($points, $tip_id, $game_id){
global $wpdb;
if(pl_get_score($tip_id)){
	$values[] = array("col" => "points", "value" => $points);
	$cond[] = array("col" => "tip_id", "value" => $tip_id);
	$cond[] = array("col" => "game_id", "value" => $game_id);
	pl_update_record("points", $cond, $values);
	}
else {

	$values[] = array("col" => "points", "value" => $points);
	$values[] = array("col" => "tip_id", "value" => $tip_id);
	$values[] = array("col" => "game_id", "value" => $game_id);
	pl_insert_record("points", $values);
	}
}

/**
 * get a score entry in the pl_points table by the tip_id
 */
function pl_get_score($tip_id){
global $wpdb;
$sql = 	'SELECT * FROM '.$wpdb->prefix.'pl_points '.
		'WHERE tip_id = %d ';
$points = $wpdb->get_row($wpdb->prepare($sql, $tip_id), ARRAY_A);
if ($points) {return $points;}
else {return FALSE;}
}


/**
 * updates or inserts an entry in the results table
 */
function pl_update_user_results($competition_id, $round, $user_id, $points){
global $wpdb;
if(pl_get_user_results($competition_id, $round, $user_id)){
	$cond[] = array("col" => "competition_id", "value" => $competition_id);
	$cond[] = array("col" => "round", "value" => $round);
	$cond[] = array("col" => "user_id", "value" => $user_id);
	$values[] = array("col" => "points", "value" => $points);

	pl_update_record("results", $cond, $values);
	}
else {
	$values[] = array("col" => "competition_id", "value" => $competition_id);
	$values[] = array("col" => "round", "value" => $round);
	$values[] = array("col" => "user_id", "value" => $user_id);
	$values[] = array("col" => "points", "value" => $points);
	pl_insert_record("results", $values);
	}
}

/**
 * updates the user position in the results table
 */
function pl_update_user_position($competition_id, $round, $user_id, $place){
	$values[] = array("col" => "position", "value" => $place);
	$cond[] = array("col" => "competition_id", "value" => $competition_id);
	$cond[] = array("col" => "round", "value" => $round);
	$cond[] = array("col" => "user_id", "value" => $user_id);
	pl_update_record("results", $cond, $values);
}


/**
 * get the results of an user for a round and a competition
 */

function pl_get_user_results($competition_id, $round, $user_id){
global $wpdb;
$sql = 	'SELECT * FROM '.$wpdb->prefix.'pl_results '.
		' WHERE competition_id = %d '.
		' AND round = %d '.
		' AND user_id = %d';
$points = $wpdb->get_row($wpdb->prepare($sql, $competition_id, $round, $user_id), ARRAY_A);
if ($points) {return $points;}
else {return FALSE;}

}

/**
 * exports a competition
 * collects all data and put it into a xml file
 */
function pl_export_competition($competition_id){
global $wpdb;
$competition 	= pl_get_competition($competition_id);
$teams 			= pl_get_all_teams($competition_id);
$xmlstring = '';
	/* start */
	$xmlstring = "<competition>\r\n";
		/* the competition main data*/
		$xmlstring .= "\t<options>\r\n";
		$xmlstring .= "\t\t<name>".$competition['name']."</name>\r\n";
		$xmlstring .= "\t\t<rounds>".$competition['rounds']."</rounds>\r\n";
		$xmlstring .= "\t\t<round_names>".$competition['round_names']."</round_names>\r\n";
		$xmlstring .= "\t</options>\r\n";
		/* the teams */
		if($teams){
		$xmlstring .= "\t<teams>\r\n";
			foreach($teams as $team){
				$xmlstring .= "\t\t<team id=\"".$team['id']."\">\r\n";
					$xmlstring .= "\t\t\t<team_name>".$team['team_name']."</team_name>\r\n";
					$xmlstring .= "\t\t\t<team_shortname>".$team['team_shortname']."</team_shortname>\r\n";
				$xmlstring .= "\t\t</team>\r\n";
			}
		$xmlstring .= "\t</teams>\r\n";
		}
		/* the games */
		$xmlstring .= "\t<games>\r\n";
		for($x=1; $x<=$competition['rounds'];$x++){
			$games = pl_get_games($competition_id, $x);
				$xmlstring .=  "\t\t<round number=\"".$x."\">\r\n";
				if($games){foreach ($games as $game){
					$xmlstring .=  "\t\t\t<game id=\"".$game['id']."\">\r\n";
						$xmlstring .=  "\t\t\t\t<gametime>".$game['gametime']."</gametime>\r\n";
						$xmlstring .=  "\t\t\t\t<team1>".$game['team1']."</team1>\r\n";
						$xmlstring .=  "\t\t\t\t<team2>".$game['team2']."</team2>\r\n";
					$xmlstring .=  "\t\t\t</game>\r\n";
				}}
				$xmlstring .=  "\t\t</round>\r\n";
			}
		$xmlstring .= "\t</games>\r\n";
	/* end */
	$xmlstring .= "</competition>\r\n";

	$filename = $competition['name'] . date("d-m-Y") . ".xml";
	header("Content-Type: text/plain");
	header("Content-Disposition: attachment; filename=$filename");
	echo $xmlstring;
}

/* option array, all options for initialize and reset */
	function pl_set_default_options_array() {
		$pl_options = array();
		$pl_options["page_id"] 	=  "-";
		$pl_options["language"] = "english";
		$pl_options["color1"] 	= "EEEEEE";
		$pl_options["color2"] 	= "CCCCCC";
		$pl_options["color3"] 	= "ffffff";
		$pl_options["color4"] 	= "000000";
		$pl_options["showlink"] = "off";
		return $pl_options;
	}

/*
 * converts the string from the wp_option table to an array
 */
function pl_read_option_string($pl_optionstring){
	$part1 = explode("<page_id>", $pl_optionstring);
	$part2 = explode("</page_id>", $part1[1]);
	$pl_options['page_id'] = $part2[0];
	$part1 = explode("<language>", $pl_optionstring);
	$part2 = explode("</language>", $part1[1]);
	$pl_options['language'] = $part2[0];
	$part1 = explode("<color1>", $pl_optionstring);
	$part2 = explode("</color1>", $part1[1]);
	$pl_options['color1'] = $part2[0];
	$part1 = explode("<color2>", $pl_optionstring);
	$part2 = explode("</color2>", $part1[1]);
	$pl_options['color2'] = $part2[0];
	$part1 = explode("<color3>", $pl_optionstring);
	$part2 = explode("</color3>", $part1[1]);
	$pl_options['color3'] = $part2[0];
	$part1 = explode("<color4>", $pl_optionstring);
	$part2 = explode("</color4>", $part1[1]);
	$pl_options['color4'] = $part2[0];
	$part1 = explode("<showlink>", $pl_optionstring);
	$part2 = explode("</showlink>", $part1[1]);
	$pl_options['showlink'] = $part2[0];
	return $pl_options;
}

	/*
	 * creates the string for the wordpress option table
	 * syntax = <option>value</option>
	 */
function pl_create_option_string($pl_options) {
		$option_string = '';
		foreach ($pl_options as $key => $option){
			$option_string .= "<".$key.">".$option."</".$key.">";
			}
	return $option_string;
	}



/* message handling and confirmation */
	function pl_admin_message($message_text, $top, $left, $confirm ='', $link = '') {
		$date=date("H:i:s");
		echo "<div style='position:absolute; top:" . $top . "px; left:" . $left . "px;' id='message' class='updated fade'><p>
		$message_text <br /></p>";
		if ($confirm == 1){
			echo '<a href="'.$link.'&amp;confirm=yes">'.AreYouSure.'</a>';
			}
		echo "</div>";
}

/* create db while the activation process */
	function prediction_league_install(){
		pl_create_tables();
	}



function pl_get_language_files(){
	if ($handle = opendir(plugin_dir_path(__FILE__). "/language")) {
	    while (false !== ($file = readdir($handle))) {
	        if (preg_match("/php/",$file)){
	       	 	$languague_files[] = $file;
	        }
	    }
	    closedir($handle);
	}
if ($languague_files){return $languague_files;}
else {return FALSE;}
}

/**
 * checks if the next_round in the competition table is correct
 */
function pl_check_next_day(){
global $wpdb;
	$timestamp = time();
	$competitions = pl_get_all_competitions_admin();
	if($competitions){
		foreach($competitions as $competition){
			/* get the next game */
			$sql = 	'SELECT round FROM '.$wpdb->prefix.'pl_games '.
					'WHERE gametime > %d '.
					'AND competition_id = %d ORDER by gametime ASC LIMIT 1';
			$nextround = $wpdb->get_var($wpdb->prepare($sql, $timestamp, $competition['id']));
		if (!$nextround){
			$sql =  'SELECT round FROM '.$wpdb->prefix.'pl_games '.
					' WHERE gametime < %d AND competition_id = %d ORDER by gametime DESC LIMIT 1';
			$nextround = $wpdb->get_var($wpdb->prepare($sql, $timestamp, $competition['id']));
		}
		if ($competition['next_round'] != $nextround){
			pl_update_next_round($competition['id'], $nextround);
			}
		}
	}
}

/**
 * updates the next_round in the competition_table
 *
 */
function pl_update_next_round($competition_id, $round){
global $wpdb;
	$values[] = array("col" => "next_round", "value" => $round);
	$cond[] = array("col" => "id", "value" => $competition_id);
	pl_update_record("competitions", $cond, $values);
}

function pl_get_results_for_draft($competition_id, $round) {
$html = '';

/* get the table for the round */
$results = pl_get_results_table($competition_id, $round);
if (!empty($results)){
if (!empty($results)){
	if (!$competition['round_names']){
		$roundname = $round.'. '.Round;}
	else {$roundname = pl_get_round_name($round, $competition['round_names']);}
	if ($round == 0){$roundname = Master;}
	$place = 0; 			/* Counter */
	$position = 0;			/* Position */
	$lastuserpoints = 0;  	/* To manage even scores of the users */
	$lastround = pl_calculate_lastround($competition['id']) - 1;
		if ($round != 0){
		$html .= '<b>'.$roundname.'</b><br/>';
		}
		else {
		$html .= '<b>'.Master.'</b><br/>';
		}
		$html .= '<b>'.Place.'</b> ';
		$html .= '<b>'.UserName.'</b> ';
		$html .= '<b>'.Points.'</b><br/>';
	foreach ($results as $user){
		/* To manage even scores of the users */
		$showplace = TRUE;
		if ($lastuserpoints == $user['points']) {$showplace = "FALSE";}
		$lastuserpoints = $user['points'];
		$place = $place + 1;
		if ($showplace == "TRUE"){$position = $place;}
		if ($lastround > 0 and $round == 0){
				$lastposition = pl_get_position($user['id'], $lastround, $competition['id']);
				$lastposition_string = '('.$lastposition.'.)';
				$picture = pl_get_updown_picture($position, $lastposition);
				}
			$html .= $position.'. '.$lastposition_string.' ';
			$html .= $user['display_name'].' ';
			$html .= $user['points'].'<br/>';
		}
	}
}
return $html;
}

/*
 * get the results table
 * oderd by points
 * limited by the option value
 */
function pl_get_results_mini_table($competition_id, $round, $limit = ''){
global $wpdb, $pl_options, $page_id;
$competition = pl_get_competition($competition_id);
/* join with the wp_user table */
if ($limit >= 0){$limit = 'LIMIT '.$limit;}
$sql = 	'SELECT r.id, r.points, r.round, r.competition_id, r.user_id, u.id, u.display_name '.
		' FROM '.$wpdb->prefix.'pl_results r, '.$wpdb->prefix.'users u '.
		' WHERE r.round = %d '.
		' AND r.competition_id = %d '.
		' AND u.id = r.user_id'.
		' ORDER by r.points DESC '.
		$limit;
$results = $wpdb->get_results($wpdb->prepare($sql, $round, $competition_id),ARRAY_A);
if (!empty($results)){
	if (!$competition['round_names']){
		$roundname = $round.'. '.Round;}
	else {$roundname = pl_get_round_name($round, $competition['round_names']);}
	if ($round == 0){$roundname = Master;}
	$place = 0; 			/* Counter */
	$position = 0;			/* Position */
	$lastuserpoints = 0;  	/* To manage even scores of the users */
	$lastround = pl_calculate_lastround($competition['id']) - 1;

	$html .= '<style type = "text/css">';
	$html .= '.predictionleague_widget tr:nth-child(odd)    { background-color:#'.$pl_options['tablecolor1'].';}';
	$html .= '.predictionleague_widget tr:nth-child(even)    { background-color:#'.$pl_options['navigationcolor1'].';}';


	$html .= '</style>';

	$html .=  '<div class="predictionleague_widget">';
	$html .=  '<table cellspacing=1 cellpadding = 1 style="background-color: #'.$pl_options['bordercolor'].'; width: '.$pl_options['tablewidth'].'%; text-align: left;">';
	$html .=  '<tr class="tiprow" style="color: #'.$pl_options['fontcolor'].';">';
		$html .=  '<th class="pl_widget" colspan="3" style="padding: 2px; background-color: #'.$pl_options['tablecolor2'].';">'.$competition['name'].' | <a style="color: #'.$pl_options['linkcolor'].';" href="?page_id='.$pl_options['page_id'].'&amp;view=results&amp;round='.$round.'&amp;competition_id='.$competition_id.'#menutip"">'.Results.' '.$roundname.'</a></th>';
	$html .=  '</tr>';
	$html .=  '<tr class="tiprow" style="color: #'.$pl_options['fontcolor'].';">';
		$html .=  '<th class="pl_widget" width="20%" style="padding: 2px; background-color: #'.$pl_options['tablecolor2'].';">'.Place.'</th>';
		$html .=  '<th class="pl_widget" width="55%" style="padding: 2px; background-color: #'.$pl_options['tablecolor2'].';">'.UserName.'</th>';
		$html .=  '<th class="pl_widget" width="25%" style="padding: 2px; background-color: #'.$pl_options['tablecolor2'].';">'.Points.'</th>';
	$html .=  '</tr>';

	foreach ($results as $user){
		/* To manage even scores of the users */
		$showplace = TRUE;
		if ($lastuserpoints == $user['points']) {$showplace = "FALSE";}
		$lastuserpoints = $user['points'];
		$place = $place + 1;
		if ($showplace == "TRUE"){$position = $place;}
		if ($lastround > 0 and $round == 0){
				$lastposition = pl_get_position($user['id'], $lastround, $competition['id']);
				$lastposition_string = '('.$lastposition.'.)';
				$picture = pl_get_updown_picture($position, $lastposition, 'small');
				}
		$html .=  '<tr class="tiprow">';
			$html .=  '<td style="padding: 2px;">'.$picture.' '.$position.'. '.$lastposition_string.'</td>';
			$html .=  '<td style="padding: 2px;">'.$user['display_name'].'</td>';
			$html .=  '<td style="padding: 2px;">'.$user['points'].'</td>';
		$html .=  '</tr>';
		}
	$html .=  '</table>';
	$html .=  '</div>';
	}
return $html;
}


/* infopage function
 *
 */
function pl_info_page() {
global $wpdb, $pl_options;
?>
<div class="wide">
<h3>Prediction League Plugin 2.0</h3>
<p>Lizenz: <a href="http://creativecommons.org/licenses/by-nc/3.0/de/">CC Lizenz/Namensnennung/Nichtkommerziell</a></p>

<p>In Kürze: <br/>
Sie dürfen:<br/>
Teilen — das Material in jedwedem Format oder Medium vervielfältigen und weiterverbreiten<br/>
Bearbeiten — das Material remixen, verändern und darauf aufbauen<br/>
Der Lizenzgeber kann diese Freiheiten nicht widerrufen solange Sie sich an die Lizenzbedingungen halten.<br/>
<br/>
Unter folgenden Bedingungen:<br/>
Namensnennung — Es wäre nett, wenn Sie das Häkchen in den Optionen aktivieren und einen Link auf die Seite des Plugins setzen. Eine Pflicht ist das nicht.<br/>
Nicht kommerziell — Sie dürfen das Material nicht für eindeutig kommerzielle Zwecke nutzen. Im Zeifelsfall bitte ich um Rücksprache. <br/>
Ich übernehme selbstverständlich keine Garantie für eventuelle Fehler, Macken, Folgeschäden oder das Scheitern der favorisierten Mannschaft im zu tippenden Wettbewerb. Viel Spaß.
</p>

<p>Weitere Informationen und eine Bedienungsanleitung unter <a href="http://liga.parkdrei.de/2014/05/27/prediction-league-plugin-2-0">http://liga.parkdrei.de/2014/05/27/prediction-league-plugin-2-0</a></p>




</div>

<?php


}


/**
 *
 * importiert die Bundesliga 2016 von der Datenbank OpenDB
 */
function import_opendb(){
	global $wpdb, $ID, $options_new, $teams, $value, $games;

	$options_new['round_names'] = '';
	$options_new['rounds'] = '';
	$options_new['name'] = '';

	/* write the new competiton */
	$form['name'] = "Bundesliga 2016/17";
	$form['rounds'] = 34;
	$form['round_names'] = '';
	/* get the new competition_id and save */

	$competition_id = pl_save_competition($form);

	for ($x = 1; $x <= 34; $x++){

		$url = "http://www.openligadb.de/api/getmatchdata/bl1/2016/".$x;
		$data = file_get_contents($url);
		$json = json_decode($data, true);
		if ($json){
			foreach ($json as $game){
				//print_r($game);
				if ($x == 1){
					$teams[$game['Team1']['TeamId']]['team_name'] = $game['Team1']['TeamName'];
					$teams[$game['Team2']['TeamId']]['team_name'] = $game['Team2']['TeamName'];
				}
				$games[$game['MatchID']]['gametime'] = strtotime($game['MatchDateTime']);
				$games[$game['MatchID']]['team1'] = $game['Team1']['TeamId'];
				$games[$game['MatchID']]['team2'] = $game['Team2']['TeamId'];
				$games[$game['MatchID']]['round'] = $x;

			}
		}
	}

	/* write the teams */
	/* get the new team ids */
	foreach ($teams as $key => $team){

		$team['competition_id'] = $competition_id;
		$team['team_name'] = trim($team['team_name']);


		$new_teams[$key]['team_id'] = pl_save_team($team);
		$new_teams[$key]['team_name'] = $team['team_name'];
	}

	/* convert the old team ids to new team ids */
	/* write the games */
	foreach ($games as $game){
			/* using the old ids to get the new team_ids for the game */
			unset($form);
			$form['gametime'] = $game['gametime'];
			if (isset($game['team1']) AND isset($new_teams[$game['team1']]['team_id'])){
				$form['team1'] = $new_teams[$game['team1']]['team_id'];
			} else {
				$form['team1'] = "NULL";
			}
			if (isset($game['team2']) AND isset($new_teams[$game['team2']]['team_id'])){
				$form['team2'] = $new_teams[$game['team2']]['team_id'];
			} else {
				$form['team2'] = "NULL";
			}
			$form['round'] = $game['round'];
			$form['competition_id'] = $competition_id;
			pl_save_game($form);

	}

}




/**
 * aktualisiert einen Eintrag
 * updated with wpdb->update v2.1
 */
function pl_update_record($table, array $conditions = NULL, array $values = NULL, $sql=''){
	global $wpdb;
	if (!$table){return false;}
	$table = $wpdb->prefix.'pl_'.$table;
	/* Bedingungen */
	$thisCond = array();
	$thisValue = array();
	if(count($conditions) > 0) {
		$criteriaArray = array();
		foreach($conditions as $condition) {
			$thisCond[$condition['col']] = $condition['value'];
		}
	}

	if(count($values) > 0) {
		foreach($values as $value) {
			$thisValue[$value['col']] = $value['value'];
		}
	}
	if (isset($thisCond) && isset($thisValue)){
		if($wpdb->update($table, $thisValue, $thisCond)){return TRUE;}
		else {return FALSE;}
	}
}


/**
 * inserts a record
 * updated with wpdb->insert v2.1
 */
function pl_insert_record($table, array $values = NULL, $sql=''){
	global $wpdb;
	if (!$table){return false;}
	$table = $wpdb->prefix.'pl_'.$table;
	$valueFieldsArray = array();
	$valueValuesArray = array();
	$valueValuesQuery = '';
	$valueFieldsQuery = '';
	$thisValues = array();
	if(count($values) > 0) {
		foreach($values as $value) {
			$thisValues[$value['col']] = $value['value'];
		}
	}

	$wpdb->insert($table, $thisValues);
	if($wpdb->insert_id){return $wpdb->insert_id;}
	else {return FALSE;}
}

/**
 * deletes one or many entries
 * @param unknown_type $table
 * @param array $conditions
 * @return string|string|string
 */
function pl_delete_record($table, array $conditions = NULL){
	global $wpdb;
	if (!$table){return false;}
	$table = $wpdb->prefix.'pl_'.$table;
	/* Bedingungen */
	if(count($conditions) > 0) {
		foreach($conditions as $condition) {
			$thisCond[$condition['col']] = $condition['value'];
		}
	}
	if (isset($thisCond)){
		$wpdb->delete($table, $thisCond);
	}
}


?>
