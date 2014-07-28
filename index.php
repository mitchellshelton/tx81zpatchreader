<?php

if (!$_POST) {

	// self posting form
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

	// Specify out extension
	$extensions = array("syx", "SYX");
	$tempfilename = explode(".", $_FILES["filename"]["name"]);
	$ext = end($tempfilename);

	// Prevent large or non-syx files from being fed into the system
	if (in_array($ext, $extensions) && ($_FILES["filename"]["size"] < 200000)) {
		// Confirm that our file was uploaded
		if ($_FILES["filename"]["error"] > 0) {
	  	echo "Error: " . $_FILES["filename"]["error"] . "<br />";
	  	break;
		} else {
	  	echo "Results from " . $_FILES["filename"]["name"] . "<hr />";
		}
	} else {
	  echo "This file is unreadable by this system.";
	  break;
	}

	// Get the location of our uploaded file
	$filename = $_FILES["filename"]["tmp_name"];
	// Grab the file contents (note that this converts the hex to ascii automatically)
	$rawpatch = file_get_contents($filename);
	// Convert the contents back into hex
	$patch = bin2hex($rawpatch);
	// Initialize an array
	$chars = array();
	// Get the 20 hex characters before our ccc222 pattern
	preg_match_all("/\w{20}(?=636363323232)/", $patch, $chars);

	// Loop over the array of voice names
	foreach($chars[0] as $key => $voice) {
		// Add a leading zero to the voice names
		$num = sprintf("%02d.\n", $key+1);
		// Convert the voice name from hex to ascii
		$asciiversion = pack("H*" , $voice);
		// Display the name
	  print $num . htmlentities($asciiversion) . '<br />';
	}

}

?>