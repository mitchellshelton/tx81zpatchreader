<?php
// Start our Session
session_start();

// Define our maximum file size
define('MAX_FILE_SIZE', 200000);

// Generate CSRF token and store it in the session
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$token = $_SESSION['csrf_token'];

// Validate our file
function isSyxFile($filename, $tmpFile) {
	$allowedExtensions = ['syx'];
	$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

	// Check the file extension
	if (!in_array($ext, $allowedExtensions)) {
		return false;
	}

	// Check the file size
	if (filesize($tmpFile) > MAX_FILE_SIZE) {
		return false;
	}

	// Check MIME type
	$finfo = new finfo(FILEINFO_MIME_TYPE);
	$mimeType = $finfo->file($tmpFile);
	if ($mimeType !== 'application/octet-stream') {
		return false;
	}

	return true;
}

// Display our form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		$filename = htmlspecialchars($_SERVER["PHP_SELF"], ENT_QUOTES, 'UTF-8');
		echo <<<HTML
<form method="post" action="$filename" enctype="multipart/form-data">
  <input type="hidden" name="csrf_token" value="$token">
	<p>
		<label for="filename">Upload a TX81Z compatible sysex file (.syx files only): </label>
		<input type="file" name="filename" id="filename" accept=".syx" />
	</p>
	<input type="submit" name="submit" value="Parse">
</form>
HTML;
		return;
}

// Validate the CSRF token
if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) || 
	!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
	echo "Invalid CSRF token.";
	return;
}

// Validate file upload
if (!isset($_FILES['filename']) || $_FILES['filename']['error'] !== UPLOAD_ERR_OK) {
	echo "File upload error.";
	return;
}

$upload = $_FILES["filename"];
$originalName = basename($upload["name"]);
$tmpFile = $upload["tmp_name"];

if (!isSyxFile($originalName, $tmpFile)) {
	echo "This file is unreadable by this system.";
	return;
}

echo "Results from " . htmlentities($originalName, ENT_QUOTES, 'UTF-8') . "<hr />";

// Read and parse the file
$rawpatch = file_get_contents($tmpFile);
$patch = bin2hex($rawpatch);

preg_match_all("/\w{20}(?=636363323232)/", $patch, $matches);

if (empty($matches[0])) {
	echo "No voice names found in this sysex file.";
	// Invalidate the CSRF token after use
	unset($_SESSION['csrf_token']);
	return;
}

foreach ($matches[0] as $index => $voice) {
	$num = sprintf("%02d.\n", $index + 1);	
	
	if (ctype_xdigit($voice) && strlen($voice) % 2 === 0) {
		$asciiversion = htmlentities(pack("H*", $voice), ENT_QUOTES, 'UTF-8');
	} else {
		$asciiversion = '[Invalid Hex]';
	}	
	echo $num . $asciiversion . "<br />";
}

// Invalidate the CSRF token after use
unset($_SESSION['csrf_token']);
session_regenerate_id(true);

?>
