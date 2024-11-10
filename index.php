<?php

// Function to parse a single log line
function parseLogLine($logLine) {
    $pattern = '/\[(?<date>\d{2}-\w{3}-\d{4} \d{2}:\d{2}:\d{2} \w+\/\w+)\] PHP (?<type>\w+):\s+(?<message>.*) in (?<file>.*) on line (?<line>\d+)/';
    if (preg_match($pattern, $logLine, $matches)) {
        return [
            'date' => $matches['date'],
            'type' => $matches['type'],
            'message' => $matches['message'],
            'file' => $matches['file'],
            'line' => $matches['line']
        ];
    }
    return null; // Return null if the line does not match the pattern
}

// Function to load and parse the entire log file
function loadLogFile($filePath) {
    $logs = [];
    $file = fopen($filePath, 'r');
    if ($file) {
        while (($line = fgets($file)) !== false) {
            $parsedLog = parseLogLine($line);
            if ($parsedLog) {
                $logs[] = $parsedLog;
            }
        }
        fclose($file);
    }
    return $logs;
}

// Function to filter logs by a keyword in the message
function searchLogs($logs, $keyword) {
    return array_filter($logs, function($log) use ($keyword) {
        return stripos($log['message'], $keyword) !== false;
    });
}

// Function to filter logs by error type
function filterLogsByType($logs, $type) {
    return array_filter($logs, function($log) use ($type) {
        return strcasecmp($log['type'], $type) === 0;
    });
}

// Function to filter logs by a date range
function filterLogsByDateRange($logs, $startDate, $endDate) {
    return array_filter($logs, function($log) use ($startDate, $endDate) {
        $logDate = DateTime::createFromFormat('d-M-Y H:i:s e', $log['date']);
        return $logDate >= $startDate && $logDate <= $endDate;
    });
}

// Main code to execute the log reader
$filePath = '/path/to/cpanel/error_log'; // Update with the path to your log file
$logs = loadLogFile($filePath);

echo "Total logs loaded: " . count($logs) . "\n";

// Example: Search for logs containing a specific keyword
$keyword = 'Undefined variable'; // Set your search keyword
$searchResults = searchLogs($logs, $keyword);
echo "Logs containing '$keyword':\n";
printLogs($searchResults);

// Example: Filter by error type (e.g., "Warning" or "Error")
$type = 'Warning';
$typeFilteredLogs = filterLogsByType($logs, $type);
echo "Logs of type '$type':\n";
printLogs($typeFilteredLogs);

// Example: Filter by date range
$startDate = new DateTime('10-Nov-2024 00:00:00 Asia/Jakarta');
$endDate = new DateTime('10-Nov-2024 23:59:59 Asia/Jakarta');
$dateFilteredLogs = filterLogsByDateRange($logs, $startDate, $endDate);
echo "Logs from {$startDate->format('d-M-Y')} to {$endDate->format('d-M-Y')}:\n";
printLogs($dateFilteredLogs);

// Function to print logs in a readable format
function printLogs($logs) {
    foreach ($logs as $log) {
        echo "Date: " . $log['date'] . "\n";
        echo "Type: " . $log['type'] . "\n";
        echo "Message: " . $log['message'] . "\n";
        echo "File: " . $log['file'] . "\n";
        echo "Line: " . $log['line'] . "\n";
        echo str_repeat("-", 50) . "\n";
    }
}
