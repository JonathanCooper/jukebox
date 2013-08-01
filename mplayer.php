<?php

include('includes/conf.php');

function startup() {
	if (!file_exists($fifo_path)) {
		posix_mkfifo($fifo_path, 0664);
	}
	if (!exec("ps auwwx | grep 'mplayer -ao " . $sound_card . " -slave -idle -input file=".$fifo_path."' | grep -v grep", $out, $ret)) {
		exec("mplayer -ao " . $sound_card . " -slave -idle -input file=".$fifo_path." >/dev/null &");
	}
}


function play($start) {
	$memcache = new Memcache;
	$memcache->connect('localhost');

	$current_list = unserialize($memcache->get('jukebox_latest'));

	foreach ($current_list as $row) {
		if ($row['id'] != $start) {
			array_push($current_list, $row);
			array_shift($current_list);
		}
		else {
			break;
		}
	}

	$fh = fopen("/tmp/mplayerplaylist", "w");
	foreach ($current_list as $to_write) {
		fwrite($fh, $to_write['fullpath']."\n");
	}
	fclose($fh);
	file_put_contents("/tmp/mplayercontrol", "loadlist /tmp/mplayerplaylist\n");
}

function pause() {
	file_put_contents("/tmp/mplayercontrol", "pause\n");
}

function skip($direction) {
	if ($direction == 'forward') {
		file_put_contents("/tmp/mplayercontrol", "pt_step +1\n");
	} else {
		file_put_contents("/tmp/mplayercontrol", "pt_step -1\n");
	}
}

startup();

if ($_POST['action'] == 'play') {
	play(intval($_POST['param']));
} elseif ($_POST['action'] == 'skip') {
	//TODO: if ($_POST['param'] != 'forward' && $_POST['param' != 'back') {
	skip($_POST['param']);
} elseif ($_POST['action'] == 'pause') {
	pause();
} //TODO: else {
	

?>
