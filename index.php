<?php

include('includes/conf.php');
include('includes/functions.php');

?>
<head>
 <link rel=stylesheet href="main.css" type="text/css">
</head>
<form name="myform" action="/" method="GET">
<input type="text" name="search" id="search" size="40" value="<?php if (isset($_GET['search'])) { echo $_GET['search']; }?>">
<input type="submit" value="Filter">
</form>

<br>
<a href="/index.php?pause=toggle<?php if (isset($_GET['search'])) { echo "&search=".$_GET['search']; }?>">Pause/Unpause</a> || 
<a href="/index.php?skip=true<?php if (isset($_GET['search'])) { echo "&search=".$_GET['search']; }?>">Next</a> || 
<a href="/index.php?skip=false<?php if (isset($_GET['search'])) { echo "&search=".$_GET['search']; }?>">Previous</a> ||
<a href="/index.php">All</a><br><br>

<?php

// set up mplayer if it isn't running yet
startup();

if (isset($_GET['skip'])) {
	if ($_GET['skip']) {
		file_put_contents("/tmp/mplayercontrol", "pt_step +1\n");
	} else {
		file_put_contents("/tmp/mplayercontrol", "pt_step -1\n");
	}
}

if (isset($_GET['next'])) {
	$start = $_GET['next'];
} else {
	$start = false;
}

if (isset($_GET['pause'])) {
	if ($_GET['pause'] == 'toggle') {
		file_put_contents("/tmp/mplayercontrol", "pause\n");
	}
}

if (isset($_GET['play_start'])) {
	$list_start = $_GET['play_start'];
} else {
	$list_start = false;
}
	
if (isset($_GET['search'])) {
	bootstrap(mysql_real_escape_string($_GET['search']), $start, $list_start);
} else {
	bootstrap(false, $start, $list_start);
}

?>
