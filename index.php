<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
<title>music</title>
<style type="text/css">
  body {
    background-color: #FDF6E3;
    font-family: sans-serif;
    text-align: center;
  }
  h1 {
    color: #444;
  }
  table,tr,td {
    font-size: small;
  }
  a:link, a:visited, a:hover, a:active {
    color: grey;
    text-decoration: none;
  }
</style>
</head>
<body>
<h1>music</h1>


<?php

/* apply this function to array_filter to get rid of unwanted items */
function sanitize($list) {
  $blacklist = array('.', '..', 'audiojs', 'includes');
  foreach ($blacklist as $word) {
    if (strpos($list, $word) !== false) {
      return false;
    }
  }
  return true;
}

/* function to generate html index file of folder */
function createindex($item) {
  $header = file_get_contents("includes/header.html");
  $header = str_replace("NAME", $item, $header);
  // $header = str_replace(" - ", "<br>", $header);
  $footer = file_get_contents("includes/footer.html");


  $filename = "$item.html"; 
 
  if (!$handle = fopen($filename, 'w')) { 
    echo "Cannot open file ($filename)"; 
    exit; 
  } 
 
  // Write $somecontent to our opened file. 
  if (fwrite($handle, $header) === false) { 
    echo "Cannot write to file ($filename)"; 
    exit; 
  } else { 
    //file is ok so write the other elements to it 
    $files = array();
    $dir = opendir("$item");
    while (false != ($file = readdir($dir))) {
      if(($file != ".") and ($file != "..") and ($file != "AlbumArt.jpg")) {
        $files[] = $file;
      }
    }
    sort($files);
    foreach($files as $file) {
      $trackname = substr($file,3);  // remove first three characters to get rid of track number
      $trackname = str_replace(".mp3","", $trackname);  // remove file suffix
      $thisline = "     <li><a href='#' data-src=\"$item/$file\">$trackname</a></li>\n";
      fwrite($handle, $thisline);
    }

    fwrite($handle, $footer); 
  } 
 
  fclose($handle); 
 
}


/* put only directories into $list */
$list = array_filter(glob('*'), 'is_dir');

/* filter out any items we don't want */
$to_print = array_filter($list, 'sanitize');

/* sort our sanitized list */
sort($to_print);

/* parse each folder to generate thumb and index */
foreach($to_print as $item) {

/* generate thumbnails for each folder */
  if (!file_exists("$item.jpg")) {
    $img = imagecreatefromjpeg("$item/AlbumArt.jpg");
    $tmp_img = imagecreatetruecolor(200, 200);
    imagecopyresampled($tmp_img, $img, 0, 0, 0, 0, 200, 200, imagesx($img), imagesy($img));
    imagejpeg($tmp_img, "$item.jpg", 90);
  }

  if (!file_exists("$item.html")) {
    createindex($item);
  }
}

/* generate index table */

echo "<table border='0' width='960' cellpadding='5' cellspacing='5' align='center'>\n";

$cols = 3;
$rows = ceil(count($to_print)/$cols); /* round up rows to get an integer */
$addr = 0;  /* array address */
for($tr = 0; $tr < $rows; $tr++) {  /* loop thru each row */
  echo " <tr>\n";
    for($td = 0; $td < $cols; $td++) {  /* loop thru each column */
      if ($addr < count($to_print)) {  /* if there are still items left in the array */
        $name = str_replace(" - ", "<br>", $to_print[$addr]);  /* generate a pretty name for the table */
        echo "  <td><a href=\"$to_print[$addr].html\"><img src=\"$to_print[$addr].jpg\"><br>$name</a></td>\n";  /* print and link to the name */
      } else {  /* otherwise there are no names left in the array */
        echo "  <td></td>\n";  /* just print an empty cell */
      }
      $addr++;  /* increment the counter into the array */
    }
  echo " </tr>\n";
} 

/* close the table */
echo "</table>\n";

?>

</body>
</html>
