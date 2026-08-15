<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Error</title></head>
<body>
<h1><?php echo isset($heading) ? $heading : 'Error'; ?></h1>
<div><?php echo isset($message) ? $message : ''; ?></div>
</body>
</html>
