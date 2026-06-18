<?php
$lines = file('resources/views/chatbot/menu.blade.php');
// Remove the wizard block from line 59 to 180 (indices 58 to 180)
// Actually we want to remove until the start of cardTambahMenu
$startIndex = -1;
$endIndex = -1;
foreach($lines as $i => $line) {
    if (strpos($line, '@if($selectedDeviceId && isset($selectedDevice))') !== false && $startIndex === -1) {
        $startIndex = $i;
    }
    if (strpos($line, '{{-- Card Tambah Menu') !== false && $endIndex === -1) {
        $endIndex = $i - 1;
    }
}

if ($startIndex !== -1 && $endIndex !== -1) {
    array_splice($lines, $startIndex, $endIndex - $startIndex + 1);
}

file_put_contents('resources/views/chatbot/menu.blade.php', implode("", $lines));
echo "Done";
