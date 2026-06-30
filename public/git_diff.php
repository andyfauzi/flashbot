<?php
$output = shell_exec('git diff HEAD resources/views/welcome.blade.php');
echo "DIFF:\n" . $output;
