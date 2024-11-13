<?php
// Function to read and filter the log file
function readErrorLog($logFile, $search = '', $errorType = '', $startDate = '', $endDate = '', $page = 1, $perPage = 50) {
    if (!file_exists($logFile)) {
        return ['error' => 'Log file not found.'];
    }

    $logs = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$logs) {
        return ['error' => 'Unable to read the log file.'];
    }

    // Initialize an array to store the filtered logs
    $filteredLogs = [];

    foreach ($logs as $log) {
        // Filter by date range (if specified)
        if ($startDate && $endDate) {
            preg_match('/(\d{4}-\d{2}-\d{2})/', $log, $matches);
            $logDate = $matches[0] ?? '';
            if ($logDate && ($logDate < $startDate || $logDate > $endDate)) {
                continue;
            }
        }

        // Filter by error type (if specified)
        if ($errorType && stripos($log, $errorType) === false) {
            continue;
        }

        // Filter by keyword (if specified)
        if ($search && stripos($log, $search) === false) {
            continue;
        }

        // Add log to filtered array
        $filteredLogs[] = $log;
    }

    // Pagination: Slice the logs array to get the logs for the current page
    $totalLogs = count($filteredLogs);
    $totalPages = ceil($totalLogs / $perPage);
    $startIndex = ($page - 1) * $perPage;
    $paginatedLogs = array_slice($filteredLogs, $startIndex, $perPage);

    return [
        'logs' => $paginatedLogs,
        'totalLogs' => $totalLogs,
        'totalPages' => $totalPages,
        'currentPage' => $page
    ];
}

// Define default variables (You may want to fetch these from user input or a form)
$logFile = '/home/username/logs/error_log'; // Path to your error log
$search = isset($_GET['search']) ? $_GET['search'] : '';
$errorType = isset($_GET['errorType']) ? $_GET['errorType'] : '';
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : '';
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 50; // Logs per page

// Read and filter logs
$logData = readErrorLog($logFile, $search, $errorType, $startDate, $endDate, $page, $perPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP cPanel Error Log Reader</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .log-entry { border-bottom: 1px solid #ccc; padding: 5px 0; }
        .log-header { font-weight: bold; }
        .pagination { text-align: center; margin-top: 20px; }
        .pagination a { padding: 5px 10px; text-decoration: none; color: #000; border: 1px solid #ddd; margin: 0 3px; }
        .pagination a.active { background-color: #007bff; color: white; }
    </style>
</head>
<body>
<div class="container">
    <h1>PHP cPanel Error Log Reader</h1>

    <!-- Search Form -->
    <form method="get" action="">
        <div>
            <label for="search">Search for Keywords:</label>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" />
        </div>
        <div>
            <label for="errorType">Filter by Error Type:</label>
            <select name="errorType">
                <option value="">All</option>
                <option value="Fatal error" <?php echo ($errorType == 'Fatal error') ? 'selected' : ''; ?>>Fatal error</option>
                <option value="Warning" <?php echo ($errorType == 'Warning') ? 'selected' : ''; ?>>Warning</option>
                <option value="Notice" <?php echo ($errorType == 'Notice') ? 'selected' : ''; ?>>Notice</option>
            </select>
        </div>
        <div>
            <label for="startDate">Start Date (YYYY-MM-DD):</label>
            <input type="date" name="startDate" value="<?php echo htmlspecialchars($startDate); ?>" />
        </div>
        <div>
            <label for="endDate">End Date (YYYY-MM-DD):</label>
            <input type="date" name="endDate" value="<?php echo htmlspecialchars($endDate); ?>" />
        </div>
        <div>
            <button type="submit">Filter Logs</button>
        </div>
    </form>

    <!-- Display Logs -->
    <div class="logs">
        <?php if (isset($logData['error'])): ?>
            <p><?php echo $logData['error']; ?></p>
        <?php else: ?>
            <?php foreach ($logData['logs'] as $log): ?>
                <div class="log-entry">
                    <div class="log-header"><?php echo $log; ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <div class="pagination">
        <?php for ($i = 1; $i <= $logData['totalPages']; $i++): ?>
            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&errorType=<?php echo urlencode($errorType); ?>&startDate=<?php echo urlencode($startDate); ?>&endDate=<?php echo u
