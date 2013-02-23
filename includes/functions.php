<?php

function use_db($this_query) {
	global $db_name, $db_user, $db_pass, $db_host;
	mysql_connect($db_host, $db_user, $db_pass);
	@mysql_select_db($db_name) or die( "Unable to select database");
	$result = mysql_query_jcache($this_query);
	mysql_close();
	return $result;
}

function bootstrap($search = false, $page_start=0, $list_start=false) {
	if ($search) {
		$all_query = "select id,title,artist,album,track,fullpath from files where title like '%".$search."%' or artist like '%".$search."%' or album like '%".$search."%' order by artist,album,cast(track as int);";
	}
	else {
		$all_query = "select id,title,artist,album,track,fullpath from files order by artist,album,track;";
	}

	$result = use_db($all_query);
	$unsorted = $result;
	if ($list_start) {
		$i=0;
		$result=array_values($result);
		foreach ($result as $test) {
			if ($test["id"] != $list_start) {
				array_push($result, $test);
				array_shift($result);
			} else {
				break;
			}
			$i+=1;
		}
                $fh = fopen("/tmp/mplayerplaylist", "w");
		foreach ($result as $to_write) {
			fwrite($fh, $to_write['fullpath']."\n");
		}
		fclose($fh);
		file_put_contents("/tmp/mplayercontrol", "loadlist /tmp/mplayerplaylist\n");
	}
	echo '<table><tr><td>Title</td><td>Artist</td><td>Album</td></tr>';
	$every_other=0;
	$count = count($result);
	if (!$page_start) {
		$page_start = 0;
	}
	if ($page_start + 100 > $count) {
		$end = $count;
	} else {
		$end = $page_start + 100;
	}
	for ($i=$page_start; $i < $end; $i++) {
		$row = $unsorted[$i];
		if ($every_other % 2 == 0){
       		 	echo '<tr bgcolor="white"><td><a href="/index.php?text='.$row['artist'].' - '.$row['title'].'&search='.$search.'&play_start='.$row['id'].'">'.substr($row['title'], 0, 64).'</a></td><td>'.$row['artist'].'</td><td>'.$row['album'].'</td></tr>';
		} else {
			echo '<tr bgcolor="#D8D8D8"><td><a href="/index.php?text='.$row['artist'].' - '.$row['title'].'&search='.$search.'&play_start='.$row['id'].'">'.substr($row['title'], 0, 64).'</a></td><td>'.$row['artist'].'</td><td>'.$row['album'].'</td></tr>';
		}
		$every_other+=1;	
	}
	echo '</table><br><a href="/index.php?next='.($page_start+100).'">Next</a>';
}

function mysql_query_jcache($sql, $expire=3600, $key=false, $instance=11211) {
	$memcache = new Memcache;
	$memcache->connect('localhost', $instance);
	if ($key===false) {
		$key=md5($sql);
	}
	if ($result = $memcache->get($key)) {
		return unserialize($result);
	}
	else {
		$result = (mysql_query($sql));
		$result_arr=array();
		while ($row=mysql_fetch_array($result)) {
			$result_arr[]=$row;
		}
		$memcache->set($key, serialize($result_arr), 0, $expire); // Store the result of the query $expire seconds
		return $result_arr;
	} 
}

function startup($sound_card, $fifo_path) {
	if (!file_exists($fifo_path)) {
       		posix_mkfifo($fifo_path,  0664);
	}
	if (!exec("ps auwwx | grep 'mplayer -ao " . $sound_card . " -slave -idle -input file=".$fifo_path."' | grep -v grep", $out, $ret)) {
       		exec("mplayer -ao " . $sound_card . " -slave -idle -input file=".$fifo_path." >/dev/null &");
	}
}

?>
