<?php
$output = shell_exec('php c:\xampp\htdocs\flashbot\public\test_edit.php 2>&1');
file_put_contents('test_out2.txt', $output);
echo "Done";
