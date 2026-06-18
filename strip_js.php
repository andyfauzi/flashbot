<?php
$lines = file('resources/views/chatbot/menu.blade.php');
// Remove from "// Logic Wizard Terpadu" to just before "function toggleInteraktif"
$startIndex = -1;
$endIndex = -1;
foreach($lines as $i => $line) {
    if (strpos($line, '// Logic Wizard Terpadu') !== false && $startIndex === -1) {
        $startIndex = $i;
    }
    if (strpos($line, 'function toggleInteraktif()') !== false && $endIndex === -1) {
        $endIndex = $i - 1;
    }
}

if ($startIndex !== -1 && $endIndex !== -1) {
    array_splice($lines, $startIndex, $endIndex - $startIndex + 1);
}

file_put_contents('resources/views/chatbot/menu.blade.php', implode("", $lines));
echo "Done";
