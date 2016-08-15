<?php
header("Content-type: text/css; charset: UTF-8");

$string = $_REQUEST['colors'];
$pl_options = explode("-",$string);
$pl_options['color1'] = $pl_options[0];
$pl_options['color2'] = $pl_options[1];
$pl_options['color3'] = $pl_options[2];
$pl_options['color4'] = $pl_options[3];
$pluginurl = urldecode($_REQUEST['pluginurl']);
?>

/* Reset */
.pl_menu,
.pl_menu ul,
.pl_menu li,
.pl_menu a {
	margin: 0;
	padding: 0;
	border: none;
	outline: none;
}

.pl_menu {	
	height: 40px;
	width: 100%;
	background: #<? echo $pl_options['color4']; ?>;
}

.pl_menu li {
	position: relative;
	list-style: none;
	float: left;
	display: block;
	height: 40px;
}

.pl_menu li a {
	display: block;
	padding: 0 8px;
	margin: 6px 0;
	line-height: 28px;
	text-decoration: none;
	border-left: 1px solid #<? echo $pl_options['color2']; ?>;
	font-family: Helvetica, Arial, sans-serif;
	font-size: 13px;
	color: #<? echo $pl_options['color2']; ?>;
	-webkit-transition: color .1s ease-in-out;
	-moz-transition: color .1s ease-in-out;
	-o-transition: color .1s ease-in-out;
	-ms-transition: color .1s ease-in-out;
	transition: color .1s ease-in-out;
}

.pl_menu li a:hover {
	background: none;
}

.pl_menu li:first-child a { border-left: none; }
.pl_menu li:last-child a{ border-right: none; }

.pl_menu li:hover > a { color: #<? echo $pl_options['color3']; ?>; }

.pl_menu li a.active {
	color: #<? echo $pl_options['color3']; ?>;
}

/* Sub Menu */

.pl_menu ul {
	position: absolute;
	top: 40px;
	left: 0;
	opacity: 0;
	background: #<? echo $pl_options['color4']; ?>;
	-webkit-transition: opacity .25s ease .1s;
	-moz-transition: opacity .25s ease .1s;
	-o-transition: opacity .25s ease .1s;
	-ms-transition: opacity .25s ease .1s;
	transition: opacity .25s ease .1s;
}

.pl_menu li:hover > ul { opacity: 1; }

.pl_menu ul li {
	height: 0;
	overflow: hidden;
	padding: 0;
	-webkit-transition: height .25s ease .1s;
	-moz-transition: height .25s ease .1s;
	-o-transition: height .25s ease .1s;
	-ms-transition: height .25s ease .1s;
	transition: height .25s ease .1s;
}

.pl_menu li:hover > ul li {
	height: 36px;
	overflow: visible;
	padding: 0;
}

.pl_menu ul li a {
	width: 160px;
	padding: 4px 0 4px 4px;
	margin: 0;
	border: none;
	border-bottom: 1px solid #<? echo $pl_options['color1']; ?>;
}


.pl_menu ul li:last-child a { border: none; }
.pl_menu li ul {
	z-index: 100;
}

/* structure */
#tipmenu {
	margin: 0 auto;
	padding: 0px;
	width: 100%;
	text-align: left;
	border: 0;
	float: left;
	}
	#tipnavimenu {
		padding: 0;
		margin: 0 auto;
		float: left;
		width: 100%;
	}
	.navmenu, .navmenuactive, .pl_menuheadline {
			margin: 1px;
			padding: 5px;
			float: left;
		}
	#tipcompmenu {
		width: 100%;
		margin: 0 auto;
		padding: 0;
		float: left;
	}
	.compmenu, .compmenuactive {
		margin: 1px;
		padding: 5px;
		float: left;
		}
/* end structure menu */

.pl_headline {
	padding: 2px;
	margin-top: 5px;
}

#tipmenu {
	color: #<?php echo $pl_options['color1'];?>;
} 
#tipmenu a, #tipmenu a:hover {
	color: #<?php echo $pl_options['color2'];?>;
	text-decoration: none;
} 

/* end colors menu */
table.tip {
	margin-top: 10px;
	width: 100%;
	text-align: left;
	background-color:#<?php echo $pl_options['color1'];?>;
}

td.tipcolumnone, td.tipcolumntwo, td.tipcolumnthree, td.tipcolumnfour, td.tipcolumnfive {
	font-size: 1.0em;
	padding: 2px;
}

.tipcolumnthree input {
	width: 40%;
}
	
.tip tr:nth-child(odd)    { background-color:<?php echo $pl_options['color1'];?>; }
.tip tr:nth-child(even)    { background-color:#<?php echo $pl_options['color2'];?>; }


td.tipcolumnthree, td.tipcolumnfour, td.tipcolumnfive, th.tipcolumnthree, th.tipcolumnfour, th.tipcolumnfive {
text-align: center;
vertical-align: middle;
}

th.tipcolumnone, th.tipcolumntwo, th.tipcolumnthree, th.tipcolumnfour, th.tipcolumnfive {
	font-size: 0.9em;
	padding: 2px;
	background-color:#<?php echo $pl_options['color4'];?>;
	color: #<?php echo $pl_options['color3'];?>;
	text-transform: none;
}

td.tipcolumnone span.time {
	font-size: 0.8em;
}

/* arrows */
img.arrows {
	border: 0;
}

th.pl_widget {
	
	font-weight: normal;
}

img.teampic {
	border: 1px solid;
	padding: 0;
	margin: 2px;
}

.clear {
	clear: both;
	margin: 0;
	padding: 0;
	}
	
/***************************/
/* Special WM Bracket 2014 */
/***************************/
	
ul.br_results {
	margin: 0;
	padding: 0;
}

.bracketContent {
	display: none;
}

.br_results li.bracketentry {
	list-style-type: none;
	text-color: #7FBE47;
	background: #ddd;
	display: block;
	float: left;
	width: 100%;
	margin: 0; 
	padding: 0; 
}

#brackets a, #brackets a:hover {
	text-decoration: none;
	color: #000;
	background: none;
	display:block;
	float: left;
	width: 100%;
}

span.name, span.winner, span.percentage, span.score {
	width: 15%;
	float: left;
	margin-left: 0;
	padding: 4px;
}

span.winner {
	width: 25%;
}

span.name {
	width: 35%;
}

span.score {
}			
.bracketentry .winner span.flags {
margin-top: 7px;
}
	
.bracket {
background: #<?php echo $pl_options['color1'];?>;
}

.bracket h4 {
margin-bottom: 1px;
margin-left: 3px;

}

.bracket ul {
margin-bottom: 10px;
padding: 0;
}

.bracket li {
list-style-type: none;
font-size: 12px;
line-height: 18px;
}


.singleGroup, .quarterfinal {
	width: 23%;
	float: left;
	margin-left: 7px;
}

.semifinal {
	width: 23%;
	float: left;
	margin-left: 23%;
}

.semifirst {
	margin-left: 15%;
}

.final, .winner {
	width: 23%;
	float: left;
	margin-left: 38%;
}

.groups li, .quarterfinal li, .semifinal li, .final li, .winner li {
color: #000;
background-color: #<?php echo $pl_options['color2'];?>;
-moz-box-shadow: inset 0px 0px 3px #000000;
-webkit-box-shadow: inset 0px 0px 3px #000000;
box-shadow: inset 0px 0px 3px #000000;
filter: progid: DXImageTransform.Microsoft.gradient(startColorstr = '#<?php echo $pl_options['color1'];?>', endColorstr = '#<?php echo $pl_options['color2'];?>');
-ms-filter: "progid: DXImageTransform.Microsoft.gradient(startColorstr = '#<?php echo $pl_options['color1'];?>', endColorstr = '#<?php echo $pl_options['color2'];?>')";
background-image: -moz-linear-gradient(top, #<?php echo $pl_options['color1'];?>, #<?php echo $pl_options['color2'];?>);
background-image: -ms-linear-gradient(top, #<?php echo $pl_options['color1'];?>, #<?php echo $pl_options['color2'];?>);
background-image: -o-linear-gradient(top, #<?php echo $pl_options['color1'];?>, #<?php echo $pl_options['color2'];?>);
background-image: -webkit-gradient(linear, center top, center bottom, from(#<?php echo $pl_options['color1'];?>), to(#<?php echo $pl_options['color2'];?>));
background-image: -webkit-linear-gradient(top, #<?php echo $pl_options['color1'];?>, #<?php echo $pl_options['color2'];?>);
background-image: linear-gradient(top, #<?php echo $pl_options['color1'];?>, #<?php echo $pl_options['color2'];?>);
display: block;
margin: 0 3px 3px 3px; 
padding: 0.4em; 
padding-left: 1.5em; 
height: 22px;
}

.quarterfinal li, .semifinal li, .final li, .winner li {
padding-left: 1em; 

}

.groups li span.ui-icon { position: absolute; margin-left: -1.3em; }

span.flags {
width: 16px;
height: 11px;
margin-right: 5px;
display: block;
float: left;
margin-top: 3px;
	background:url('<?php echo $pluginurl.'/flags/flags.png'; ?>') no-repeat

}

span.ARG {background-position: -16px 0}
span.AUS {background-position: -32px 0}
span.BIH {background-position: -48px 0}
span.BEL {background-position: -64px 0}
span.BRA {background-position: -80px 0}
span.SUI {background-position: 0 -11px}
span.CIV {background-position: -16px -11px}
span.CHI {background-position: -32px -11px}
span.CMR {background-position: -48px -11px}
span.COL {background-position: -64px -11px}
span.CRC {background-position: -80px -11px}
span.GER {background-position: 0 -22px}
span.ALG {background-position: -16px -22px}
span.ECU {background-position: -32px -22px}
span.ENG {background-position: -48px -22px}
span.ESP {background-position: -64px -22px}
span.FRA {background-position: -80px -22px}
span.GHA {background-position: 0 -33px}
span.GRE {background-position: -16px -33px}
span.HON {background-position: -32px -33px}
span.CRO {background-position: -48px -33px}
span.IRI {background-position: -64px -33px}
span.ITA {background-position: -80px -33px}
span.JPN {background-position: 0 -44px}
span.KOR {background-position: -16px -44px}
span.MEX {background-position: -32px -44px}
span.NIG {background-position: -64px -44px}
span.NED {background-position: -80px -44px}
span.POR {background-position: 0 -55px}
span.RUS {background-position: -16px -55px}
span.USA {background-position: -32px -55px}
span.URU {background-position: -48px -55px}


.bracket h4 {
	font-size: 0.9em;
}
	
.bracket ul {
	margin:0;
	margin-bottom: 20px;
}
.finalround li {
	cursor: pointer;
}
.singleGroup li {
	cursor: row-resize;
}
.bracket p {
	padding: 4px;
}

.bracket li.wrong {
	opacity: 0.5;
}

span.ui-icon.ui-icon-arrowthick-2-n-s {
	-webkit-hyphens: auto;
	background-image: url('<?php echo $pluginurl.'/graphics/ui-icons_222222_256x240.png'; ?>');
	background-position: -128px -48px;
	background-repeat: no-repeat;
	box-sizing: border-box;
	cursor: row-resize;
	display: block;
	font-size: 12px;
	height: 16px;
	line-height: 18px;
	margin-left: -15.600000381469727px;
	overflow-x: hidden;
	overflow-y: hidden;
	position: absolute;
	text-indent: -99999px;
	width: 16px;
	}
/***********/