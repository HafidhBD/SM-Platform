<?php
/**
 * Cron: Collect Data
 * 
 * Run via cron job:
 * 0 */6 * * * php /path/to/project/cron/collect.php >> /path/to/project/storage/logs/cron.log 2>&1
 * 
 * This script collects data for all active projects.
 */

define('APP_ROOT', dirname(__DIR__));
define('CRON_MODE', true);

require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/app/core/Database.php';
require_once APP_ROOT . '/app/core/Helpers.php';
require_once APP_ROOT . '/app/services/ApifyService.php';
require_once APP_ROOT . '/app/models/ProjectModel.php';
require_once APP_ROOT . '/app/models/PostModel.php';
require_once APP_ROOT . '/app/models/CollectionRunModel.php';

echo "[" . date('Y-m-d H:i:s') . "] Starting data collection cron\n";

$db = Database::getInstance();
if (!$db->isConnected()) {
    die("Database connection failed\n");
}

$projectModel = new ProjectModel();
$postModel = new PostModel();
$runModel = new CollectionRunModel();
$apify = new ApifyService();

$projects = $projectModel->getAllActive();

foreach ($projects as $project) {
    echo "Collecting data for: {$project['name']}\n";
    
    $targets = $projectModel->getSearchTargets($project['id']);
    if (empty($targets)) {
        echo "  No targets configured, skipping\n";
        continue;
    }

    // Create run record
    $runId = $runModel->create([
        'project_id' => $project['id'],
        'actor_id' => getSetting('apify_actor_id', APIFY_ACTOR_ID),
        'status' => 'running',
        'targets' => json_encode($targets, JSON_UNESCAPED_UNICODE),
        'started_at' => date('Y-m-d H:i:s')
    ]);

    $result = $apify->runCollection($targets);

    if (!$result['success']) {
        $runModel->updateStatus($runId, 'failed', [
            'error_message' => $result['error'] ?? 'Unknown error'
        ]);
        echo "  Failed: {$result['error']}\n";
        continue;
    }

    $items = $result['items'] ?? [];
    $storeResult = $postModel->storeFromApify($items, $project['id'], $runId);

    $runModel->updateStatus($runId, 'completed', [
        'run_id' => $result['run_id'] ?? null,
        'posts_found' => count($items),
        'posts_stored' => $storeResult['stored']
    ]);

    echo "  Found: " . count($items) . " | Stored: {$storeResult['stored']} | Duplicates: {$storeResult['duplicates']}\n";
}

echo "[" . date('Y-m-d H:i:s') . "] Collection cron completed\n";
