<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Exception</title></head>
<body>
<h1>Exception</h1>
<p><?php echo nl2br(htmlspecialchars($message)); ?></p>
<?php if (defined('ENVIRONMENT') && ENVIRONMENT === 'development'): ?>
<pre><?php echo htmlspecialchars($exception->getTraceAsString()); ?></pre>
<?php endif; ?>
</body>
</html>
