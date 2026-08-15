<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>404 Page Not Found</title></head>
<body>
<h1><?php echo isset($heading) ? $heading : '404 Page Not Found'; ?></h1>
<p><?php echo isset($message) ? $message : 'The page you requested was not found.'; ?></p>
</body>
</html>
