<?php

include('includes/conf.php');
if (!file_exists($fifo_path)) {
	posix_mkfifo($fifo_path, 0664);
}
if (!exec("ps auwwx | grep 'mplayer -ao " . $sound_card . " -slave -idle -input file=".$fifo_path."' | grep -v grep", $out, $ret)) {
	exec("mplayer -ao " . $sound_card . " -slave -idle -input file=".$fifo_path." >/dev/null &");
}

$id = intval($_POST['id']);
$memcache = new Memcache;
$memcache->connect('localhost');

/*
$fh = fopen('out.log', 'a');
fwrite($fh, $_POST['id']);
fclose($fh);
*/

$current_list = unserialize($memcache->get('jukebox_latest'));

foreach ($current_list as $row) {
	if ($row['id'] != $id) {
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

?>
