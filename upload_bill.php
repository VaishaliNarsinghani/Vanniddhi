<?php
session_start();
if(!isset($_SESSION['admin'])) { header("Location: admin_login.php"); exit; }
?>
<!DOCTYPE html>
<html>
<head>
  <title>Upload Order Bill</title>
</head>
<body>
  <h2>Upload Order Bill (PDF/Image)</h2>
<form action="process_bill.php" method="post" enctype="multipart/form-data">
    <input type="file" name="billFile" accept=".pdf,.jpg,.png,.jpeg">
    <button type="submit">Upload</button>
</form>

</body>
</html>
