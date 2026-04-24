<?php
/**
 * Market Intelligence Platform - Entry Point
 * 
 * All requests are routed through this file.
 * Place this in /public as index.php for Hostinger.
 */

// Define app root
define('APP_ROOT', dirname(__DIR__));

// Load configuration
require_once APP_ROOT . '/config/config.php';

// Load core files
require_once APP_ROOT . '/app/core/Database.php';
require_once APP_ROOT . '/app/core/BaseModel.php';
require_once APP_ROOT . '/app/core/BaseController.php';
require_once APP_ROOT . '/app/core/Auth.php';
require_once APP_ROOT . '/app/core/Router.php';
require_once APP_ROOT . '/app/core/Helpers.php';

// Load services
require_once APP_ROOT . '/app/services/ApifyService.php';
require_once APP_ROOT . '/app/services/OpenAIService.php';

// Load models
require_once APP_ROOT . '/app/models/UserModel.php';
require_once APP_ROOT . '/app/models/ProjectModel.php';
require_once APP_ROOT . '/app/models/PostModel.php';
require_once APP_ROOT . '/app/models/CollectionRunModel.php';
require_once APP_ROOT . '/app/models/AnalysisModel.php';
require_once APP_ROOT . '/app/models/AlertModel.php';
require_once APP_ROOT . '/app/models/SummaryModel.php';

// Load controllers
require_once APP_ROOT . '/app/controllers/AuthController.php';
require_once APP_ROOT . '/app/controllers/DashboardController.php';
require_once APP_ROOT . '/app/controllers/ProjectController.php';
require_once APP_ROOT . '/app/controllers/CollectionController.php';
require_once APP_ROOT . '/app/controllers/PostController.php';
require_once APP_ROOT . '/app/controllers/AnalysisController.php';
require_once APP_ROOT . '/app/controllers/AlertController.php';
require_once APP_ROOT . '/app/controllers/ReportController.php';
require_once APP_ROOT . '/app/controllers/SettingsController.php';

// Start session
Auth::start();
Auth::checkExpiry();

// Create admin user if not exists
Auth::createAdmin();

// Define routes
$router = new Router();

// Auth
$router->get('/login', 'AuthController', 'login');
$router->post('/login', 'AuthController', 'login');
$router->get('/logout', 'AuthController', 'logout');

// Dashboard
$router->get('/', 'DashboardController', 'index');

// Projects
$router->get('/projects', 'ProjectController', 'index');
$router->any('/projects/create', 'ProjectController', 'create');
$router->any('/projects/edit/{id}', 'ProjectController', 'edit');
$router->post('/projects/delete/{id}', 'ProjectController', 'delete');

// Collection
$router->get('/collection', 'CollectionController', 'index');
$router->post('/collection/start', 'CollectionController', 'start');
$router->get('/collection/status/{id}', 'CollectionController', 'checkStatus');
$router->post('/collection/rerun/{id}', 'CollectionController', 'rerun');

// Posts
$router->get('/posts', 'PostController', 'index');
$router->get('/posts/view/{id}', 'PostController', 'view');

// Analysis
$router->get('/analysis', 'AnalysisController', 'index');
$router->post('/analysis/run', 'AnalysisController', 'run');
$router->post('/analysis/summary', 'AnalysisController', 'summary');
$router->get('/analysis/summary/{id}', 'AnalysisController', 'viewSummary');
$router->post('/analysis/crisis-check', 'AnalysisController', 'crisisCheck');

// Alerts
$router->get('/alerts', 'AlertController', 'index');
$router->post('/alerts/mark-read/{id}', 'AlertController', 'markRead');
$router->post('/alerts/mark-all-read', 'AlertController', 'markAllRead');
$router->post('/alerts/resolve/{id}', 'AlertController', 'resolve');

// Reports
$router->get('/reports', 'ReportController', 'index');
$router->post('/reports/generate', 'ReportController', 'generate');
$router->get('/reports/view/{id}', 'ReportController', 'view');
$router->get('/reports/export/{id}', 'ReportController', 'exportHtml');

// Settings
$router->get('/settings', 'SettingsController', 'index');
$router->post('/settings/save-apify', 'SettingsController', 'saveApify');
$router->post('/settings/save-openai', 'SettingsController', 'saveOpenai');
$router->post('/settings/save-alerts', 'SettingsController', 'saveAlerts');
$router->post('/settings/save-general', 'SettingsController', 'saveGeneral');
$router->post('/settings/change-password', 'SettingsController', 'changePassword');
$router->get('/health', 'SettingsController', 'health');

// Dispatch
$router->dispatch();
