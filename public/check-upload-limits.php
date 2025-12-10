<?php
/**
 * Upload Limits Checker
 * Access this file at: https://backend.ubiqent.com/check-upload-limits.php
 * DELETE THIS FILE after checking!
 */

header('Content-Type: application/json');

$limits = [
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_execution_time' => ini_get('max_execution_time'),
    'max_input_time' => ini_get('max_input_time'),
    'memory_limit' => ini_get('memory_limit'),
    'max_file_uploads' => ini_get('max_file_uploads'),
];

// Convert to bytes for comparison
function convertToBytes($value) {
    $value = trim($value);
    $last = strtolower($value[strlen($value)-1]);
    $value = (int) $value;
    
    switch($last) {
        case 'g':
            $value *= 1024;
        case 'm':
            $value *= 1024;
        case 'k':
            $value *= 1024;
    }
    
    return $value;
}

$uploadMaxBytes = convertToBytes($limits['upload_max_filesize']);
$postMaxBytes = convertToBytes($limits['post_max_size']);

$recommendations = [];

if ($uploadMaxBytes < 5368709120) { // 5GB
    $recommendations[] = "upload_max_filesize should be at least 5120M (currently: {$limits['upload_max_filesize']})";
}

if ($postMaxBytes < 5368709120) { // 5GB
    $recommendations[] = "post_max_size should be at least 5120M (currently: {$limits['post_max_size']})";
}

if ((int)$limits['max_execution_time'] < 3600) {
    $recommendations[] = "max_execution_time should be at least 3600 (currently: {$limits['max_execution_time']})";
}

if ((int)$limits['max_input_time'] < 3600) {
    $recommendations[] = "max_input_time should be at least 3600 (currently: {$limits['max_input_time']})";
}

$memoryBytes = convertToBytes($limits['memory_limit']);
if ($memoryBytes < 536870912) { // 512MB
    $recommendations[] = "memory_limit should be at least 512M (currently: {$limits['memory_limit']})";
}

$response = [
    'status' => empty($recommendations) ? 'OK' : 'NEEDS_CONFIGURATION',
    'current_limits' => $limits,
    'recommendations' => $recommendations,
    'php_version' => phpversion(),
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
];

echo json_encode($response, JSON_PRETTY_PRINT);
