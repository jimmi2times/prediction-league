<?php
global $filename;

// Zuerst definieren wir die Funktionen, die spaeter auf
// die diversen Ereignisse reagieren sollen
/**
 * Diese Funktion behandelt ein oeffnendes Element.
 * Alle Parameter werden automatisch vom Parser uebergeben
 *
 * @param    parser    Object    Parserobjekt
 * @param    name      string    Name des oeffnenden Elements
 * @param    atts      array     Array mit Attributen
 */
function pl_startElement($parser, $name, $atts) {
  global $html, $competition, $ID, $game_id, $round, $value;

  // Die XML-Namen werden in Grossbuchstaben uebergeben.
  // Deshalb wandeln wir sie mit strtolower() in Klein-
  // buchstaben um.
  switch (strtolower($name)) {
  case "competition";
	$value = 'competition';
    break;
  case "options";
	$value = 'options';
    break;
  case "name";
	$value = 'name';
    break;
  case "rounds";
	$value = 'rounds';
    break;
  case "round_names";
	$value = 'round_names';
    break;
  case "teams";
	$value = 'teams';
    break;
  case "team";
	$value = 'team';
    $ID = $atts["ID"];
    break;
  case "team_name";
	$value = 'team_name';
    break;
  case "team_shortname";
	$value = 'team_shortname';
    break;

  case "games";
	$value = 'games';
    break;
  case "round";
	$value = 'round';
    $round = $atts["NUMBER"];
    break;
  case "game";
	$value = 'game';
	$game_id = $atts["ID"];
    break;
  case "gametime";
	$value = 'gametime';
    break;
  case "team1";
	$value = 'team1';
    break;
  case "team2";
	$value = 'team2';
    break;

  default:
    // Ein ungueltiges Element ist vorgekommen.
    $error = "Undefiniertes Element <".$name.">";
    die($error . " in Zeile " .
        xml_get_current_line_number($parser));
    break;
  }
}
/**
 * Diese Funktion behandelt ein abschlie�endes Element
 * Alle Parameter werden automatisch vom Parser �bergeben
 *
 * @param  parser    Object    Parserobjekt
 * @param  name      string    Name des schlie�enden Elements
 */
function pl_endElement($parser, $name) {
  global $html;
  switch (strtolower($name)) {
  case "ref":
    $html .= "</a>";
    break;
  }
}

/**
 * Diese Funktion behandelt normalen Text
 * Alle Parameter werden automatisch vom Parser uebergeben
 *
 * @param    parser    Object    Parserobjekt
 * @param    text      string    Der Text
 */
function pl_cdata($parser, $text) {
  global $competition, $ID, $game_id, $value, $options_new, $teams, $round, $games;

	/* get the options */
	if ($value == "name" AND $text){
		$options_new['name'] .= $text;
		}
	if ($value == "rounds" AND $text){
		$options_new['rounds'] .= $text;
		}
	if ($value == "round_names" AND $text){
		$options_new['round_names'] .= $text;
		}

	/* get the teams */
	if ($value== "team_shortname" AND $text) {
		if (!isset($teams[$ID]['team_shortname'])){$teams[$ID]['team_shortname'] = '';}
		$teams[$ID]['team_shortname'] .= $text;
		}
	if ($value== "team_name" AND $text) {
		if (!isset($teams[$ID]['team_name'])){$teams[$ID]['team_name'] = '';}
		$teams[$ID]['team_name'] .= $text;
		}

	/* get the games */
	if ($value== "gametime" AND $text > 0) {
		if (!isset($games[$game_id]['gametime'])){$games[$game_id]['gametime'] = '';}
		if (!isset($games[$game_id]['round'])){$games[$game_id]['round'] = '';}

		$games[$game_id]['gametime'] .= $text;
		$games[$game_id]['round'] .= $round;
		}
	if ($value== "team1" AND $text > 0) {
		if (!isset($games[$game_id]['team1'])){$games[$game_id]['team1'] = '';}
		$games[$game_id]['team1'] .= $text;
		}
	if ($value== "team2" AND $text > 0) {
		if (!isset($games[$game_id]['team2'])){$games[$game_id]['team2'] = '';}
		$games[$game_id]['team2'] .= $text;
		}
}






global $wpdb, $ID, $options_new, $teams, $value, $games;

$xmlFile = implode("", file($filename));

$options_new['round_names'] = '';
$options_new['rounds'] = '';
$options_new['name'] = '';




$parser = xml_parser_create();
// Setzen der Handler
xml_set_element_handler($parser,"pl_startElement","pl_endElement");
// Setzen des CDATA-Handlers
xml_set_character_data_handler($parser, "pl_cdata");
// Parsen
xml_parse($parser, $xmlFile);
// Gibt alle verbrauchten Ressourcen wieder frei.
xml_parser_free($parser);



/* write the new competiton */
$form['name'] = trim($options_new['name']);
$form['rounds'] = $options_new['rounds'];
$form['round_names'] = trim($options_new['round_names']);
/* get the new competition_id and save */

$competition_id = pl_save_competition($form);

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
