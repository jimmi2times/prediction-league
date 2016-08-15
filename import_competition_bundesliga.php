<?php
global $filename;



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

print_r($teams);



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

/* ready */
?>
