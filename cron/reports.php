<?php
/**
 * Cron: Generate Daily Reports
 * 
 * Run via cron job:
 * 0 8 * * * php /path/to/project/cron/reports.php >> /path/to/project/storage/logs/cron.log 2>&1
 * 
 * Generates daily reports for all active projects.
 */

define('APP_ROOT', dirname(__DIR__));
define('CRON_MODE', true);

require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/app/core/Database.php';
require_once APP_ROOT . '/app/core/Helpers.php';
require_once APP_ROOT . '/app/services/OpenAIService.php';
require_once APP_ROOT . '/app/models/ProjectModel.php';
require_once APP_ROOT . '/app/models/SummaryModel.php';

echo "[" . date('Y-m-d H:i:s') . "] Starting daily reports cron\n";

$db = Database::getInstance();
if (!$db->isConnected()) {
    die("Database connection failed\n");
}

$projectModel = new ProjectModel();
$summaryModel = new SummaryModel();

$projects = $projectModel->getAllActive();
$today = date('Y-m-d');

foreach ($projects as $project) {
    echo "Generating daily report for: {$project['name']}\n";
    
    $result = $summaryModel->generateSummary($project['id'], 'daily', $today, $today);
    
    if ($result['success']) {
        echo "  Report generated: ID {$result['summary_id']}\n";
    } else {
        echo "  Failed: " . ($result['error'] ?? 'Unknown') . "\n";
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Reports cron completed\n";
