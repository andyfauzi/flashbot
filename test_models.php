<?php
$env = parse_ini_file('.env');
$key = $env['GEMINI_API_KEY'];
$json = file_get_contents('https://generativelanguage.googleapis.com/v1beta/models?key=' . $key);
echo $json;
