<?php

if (!$_POST) {

	$filename = $_SERVER["PHP_SELF"];
	print '<form method="post" action="' . $filename . '" enctype="multipart/form-data">';

?>
  <p>
  	<label for="filename">Upload a TX81Z compatible sysex file (.syx files only): </label>
  	<input type="file" name="filename" id="filename" />
  </p>
  <input type="submit" name="submit" value="Parse">
<?php

	print '</form>';

} else {

	$allowedExts = array("syx");
	$temp = explode(".", $_FILES["filename"]["name"]);
	$extension = end($temp);

	if (in_array($extension, $allowedExts) && ($_FILES["filename"]["size"] < 200000)) {
		if ($_FILES["filename"]["error"] > 0) {
	  	echo "Error: " . $_FILES["filename"]["error"] . "<br />";
	  	break;
		} else {
	  	echo "Results from " . $_FILES["filename"]["name"] . "<hr />";
		}
	} else {
	  echo "Invalid file";
	  break;
	}

	$filename = $_FILES["filename"]["tmp_name"];
	$rawpatch = file_get_contents($filename);
	$patch = bin2hex($rawpatch);
	$chars = array();
	preg_match_all("/\w{20}(?=636363323232)/", $patch, $chars);

	foreach($chars[0] as $key => $voice) {
			$num = sprintf("%02d.\n", $key+1);
			$asciiversion = pack("H*" , $voice);
	    print $num . htmlentities($asciiversion) . '<br />';
	}

}

?>