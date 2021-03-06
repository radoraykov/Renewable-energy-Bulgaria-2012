<?php

/*********************************************************

(CC) Boyan Yurukov http://yurukov.net/blog

Usage: http://...../pack.php?secret
Forsed rebuild: http://...../pack.php?secret&force

"secret" is defined in secret.php

*********************************************************/

include("secret.php");

$base="."; 

$ignore = array();

$force=isset($_GET['force']);

$all1=0;
$all2=0;

pack_file($base);

if ($all1>0) 
	echo "<br/><span style='color:green'>Overall: ".$all1."/".$all2." ".round(100-$all2/$all1*100)."% reduction</span>";

function pack_file($path) {
	global $force, $all1, $all2, $ignore;
	$gzpath=createGzName($path);
	if (is_dir($path) && $path!="./pos") {
		if ($handle = opendir($path)) {
			while (false !== ($file = readdir($handle)))
				if ($file != "." && $file != "..") 
					pack_file($path."/".$file);
		}
		closedir($handle);
	} else 
	if (!in_array(substr($path,2),$ignore) && (substr($path,-4)==".css" || substr($path,-3)==".js" || substr($path,-5)==".html" || substr($path,-5)==".json" || substr($path,-4)==".csv" || substr($path,-4)==".tsv")) {
		if (!$force && file_exists($gzpath) && filectime($path)<filectime($gzpath)) {
			echo "<span style='color:gray'>$path ... no update. Skip</span><br/>";
		} else {
			$content=file_get_contents($path);
			$content=str_replace("?ynocache","",$content);
			$encoded=gzencode ( $content , 9 );
			$all1+=strlen($content);
			$all2+=strlen($encoded);
			echo "$path <span style='color:green'> ".strlen($content)."/".strlen($encoded)." ".round(100-strlen($encoded)/strlen($content)*100)."% </span><br/>";

			$f = fopen ( $gzpath, 'w' );
			fwrite ( $f,  $encoded);
			fclose ( $f );
		}
	}
}

function createGzName($path) {
	if (strpos($path,"/")===false)
		return ".".$path.".gz";
	else
		return substr($path,0,strrpos($path,"/"))."/.".substr($path,strrpos($path,"/")+1).".gz";

}

?>
