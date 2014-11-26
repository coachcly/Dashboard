<?php
if ($handle = opendir('images/Logos')) {
	while (false !== ($entry = readdir($handle))) {
		if ($entry != "." && $entry != "..") {
			echo "$entry <br>";
		}
	}
	closedir($handle);
}
?> 
