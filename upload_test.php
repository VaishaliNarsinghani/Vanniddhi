<?php
ini_set('display_errors',1); error_reporting(E_ALL);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo '<pre>'; print_r($_FILES); echo '</pre>';
    // show human readable error codes
    foreach ($_FILES as $k=>$v) {
        echo "$k error: " . $v['error'] . "<br>";
    }
}
?>
<form method="post" enctype="multipart/form-data">
    <input type="file" name="testfile">
    <button type="submit">Upload</button>
</form>
