<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>PHP Error</title></head>
<body>
<h1>PHP Error</h1>
<p><strong><?php echo isset($severity) ? $severity : ''; ?></strong>: <?php echo isset($message) ? $message : ''; ?></p>
<p><?php echo isset($filepath) ? $filepath : ''; ?> : <?php echo isset($line) ? $line : ''; ?></p>
</body>
</html>
