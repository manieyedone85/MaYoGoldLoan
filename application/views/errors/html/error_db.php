<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Database Error</title></head>
<body>
<h1><?php echo isset($heading) ? $heading : 'Database Error'; ?></h1>
<p><?php echo isset($message) ? $message : 'A database error occurred.'; ?></p>
</body>
</html>
