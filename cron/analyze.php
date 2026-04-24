<?php
/**
 * Cron: Run AI Analysis
 * 
 * Run via cron job:
 * 30 */6 * * * php /path/to/project/cron/analyze.php >> /path/to/project/storage/logs/cron.log 2>&1
 * 
 * Analyzes unanalyzed posts for all active projects.
 */

define('APP_ROOT', dirname(__DIR__));
define('CRON_MODE', true);

require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/app/core/Database.php';
require_once APP_ROOT . '/app/core/Helpers.php';
require_once APP_ROOT . '/app/services/OpenAIService.php';
require_once APP_ROOT . '/app/models/ProjectModel.php';
require_once APP_ROOT . '/app/models/PostModel.php';
require_once APP_ROOT . '/app/models/AnalysisModel.php';

echo "[" . date('Y-m-d H:i:s') . "] Starting AI analysis cron\n";

$db = Database::getInstance();
if (!$db->isConnected()) {
    die("Database connection failed\n");
}

$projectModel = new ProjectModel();
$analysisModel = new AnalysisModel();

$projects = $projectModel->getAllActive();

foreach ($projects as $project) {
    echo "Analyzing posts for: {$project['name']}\n";
    
    $result = $analysisModel->analyzeProjectPosts($project['id'], false);
    
    if ($result['success']) {
        echo "  Analyzed: {$result['analyzed']} posts | Tokens: " . ($result['tokens_used'] ?? 0) . "\n";
    } else {
        echo "  Failed: " . ($result['error'] ?? 'Unknown') . "\n";
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Analysis cron completed\n";
