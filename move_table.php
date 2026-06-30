<?php
$file = 'c:\xampp\htdocs\flashbot\resources\views\welcome.blade.php';
$content = file_get_contents($file);

$tableStartStr = '            <!-- Comparison Table -->';
$tableEndStr = "            @endif\n\n            <!-- Single Call To Action Button -->"; // include @endif to ensure we match the right one

$tableStartPos = strpos($content, $tableStartStr);
$tableEndPos = strpos($content, $tableEndStr, $tableStartPos);

if ($tableStartPos === false || $tableEndPos === false) {
    die("Could not find table block.");
}

$tableBlock = substr($content, $tableStartPos, $tableEndPos - $tableStartPos + strlen("            @endif\n"));

// remove the block from the original content
$content = str_replace($tableBlock, '', $content);

// insert the block before <div class="row g-4 justify-content-center">
$targetStr = '            <div class="row g-4 justify-content-center">';
$targetPos = strpos($content, $targetStr);

if ($targetPos === false) {
    die("Could not find target insert position.");
}

$tableBlockWithSpacing = $tableBlock . "\n";

$content = substr_replace($content, $tableBlockWithSpacing, $targetPos, 0);

file_put_contents($file, $content);
echo "Moved successfully.";
