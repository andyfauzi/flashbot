<?php
$file = 'c:\xampp\htdocs\flashbot\resources\views\welcome.blade.php';
$content = file_get_contents($file);

$tableStartStr = '            <!-- Comparison Table -->';
$tableEndStr = '            <!-- Single Call To Action Button -->';

$tableStartPos = strpos($content, $tableStartStr);
$tableEndPos = strpos($content, $tableEndStr, $tableStartPos);

if ($tableStartPos === false || $tableEndPos === false) {
    die("Could not find table block. Start: " . ($tableStartPos !== false ? "Found" : "Not Found") . ", End: " . ($tableEndPos !== false ? "Found" : "Not Found"));
}

$tableBlock = substr($content, $tableStartPos, $tableEndPos - $tableStartPos);

// remove the block from the original content
$content = str_replace($tableBlock, '', $content);

// insert the block before <div class="row g-4 justify-content-center">
$targetStr = '            <div class="row g-4 justify-content-center">';
$targetPos = strpos($content, $targetStr);

if ($targetPos === false) {
    die("Could not find target insert position.");
}

$content = substr_replace($content, rtrim($tableBlock) . "\r\n\r\n", $targetPos, 0);

file_put_contents($file, $content);
echo "Moved successfully.";
