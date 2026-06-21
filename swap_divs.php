<?php
$content = file_get_contents('resources/views/chatbot/device/index.blade.php');

$parts = explode('<div class="row mb-4 g-4">', $content);
$before = $parts[0];
$rest = $parts[1];

$parts2 = explode('<div class="row g-4">', $rest);
$blockA = $parts2[0];
$rest2 = $parts2[1];

$parts3 = explode('{{-- ====================================================== --}}', $rest2);
$blockB = $parts3[0];
$after = $parts3[1];

$new_content = $before . '<div class="row mb-4 g-4">' . $blockB . '<div class="row g-4">' . $blockA . '{{-- ====================================================== --}}' . $after;

file_put_contents('resources/views/chatbot/device/index.blade.php', $new_content);
echo "Done";
