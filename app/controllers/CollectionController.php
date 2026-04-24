<?php
/**
 * Collection Controller - Data collection from Apify
 */
class CollectionController extends BaseController
{
    private CollectionRunModel $runModel;
    private ProjectModel $projectModel;
    private PostModel $postModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->runModel = new CollectionRunModel();
        $this->projectModel = new ProjectModel();
        $this->postModel = new PostModel();
    }

    public function index(array $params = []): void
    {
        $projects = $this->projectModel->getAllActive();
        $projectId = (int) ($this->get('project', 0));
        $runs = $projectId ? $this->runModel->getByProject($projectId, 30) : [];

        $csrfToken = $this->generateCsrfToken();
        $this->view('collection/index', [
            'projects' => $projects,
            'current_project_id' => $projectId,
            'runs' => $runs,
            'csrf_token' => $csrfToken,
            'page_title' => 'جلب البيانات'
        ]);
    }

    /**
     * Start a new collection run
     */
    public function start(array $params = []): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/collection');
        }

        if (!$this->verifyCsrf()) {
            flash('error', 'خطأ في التحقق من الأمان');
            $this->redirect('/collection');
            return;
        }

        $projectId = (int) $this->post('project_id', 0);
        $customTargets = $this->post('custom_targets', '');
        $maxTweets = (int) $this->post('max_tweets', 50);
        $timeWindow = (int) $this->post('time_window', 7);
        $searchType = $this->post('search_type', 'latest');

        if (!$projectId) {
            flash('error', 'يرجى اختيار الجهة');
            $this->redirect('/collection');
            return;
        }

        $project = $this->projectModel->find($projectId);
        if (!$project) {
            flash('error', 'الجهة غير موجودة');
            $this->redirect('/collection');
            return;
        }

        // Build targets
        $targets = $this->projectModel->getSearchTargets($projectId);
        if (!empty($customTargets)) {
            $customList = array_filter(explode("\n", $customTargets), fn($t) => trim($t));
            $targets = array_merge($targets, array_map('trim', $customList));
        }

        if (empty($targets)) {
            flash('error', 'لا توجد أهداف للبحث. أضف كلمات مفتاحية أو هاشتاقات أو حسابات للجهة');
            $this->redirect('/collection');
            return;
        }

        // Create collection run record
        $runId = $this->runModel->create([
            'project_id' => $projectId,
            'actor_id' => getSetting('apify_actor_id', APIFY_ACTOR_ID),
            'status' => 'pending',
            'targets' => json_encode($targets, JSON_UNESCAPED_UNICODE),
            'input_config' => json_encode(['max_tweets' => $maxTweets, 'time_window' => $timeWindow, 'search_type' => $searchType], JSON_UNESCAPED_UNICODE)
        ]);

        // Try to run Apify collection
        $apify = new ApifyService();
        $options = [
            'max_tweets' => $maxTweets,
            'time_window' => $timeWindow,
            'search_type' => $searchType
        ];

        $this->runModel->updateStatus($runId, 'running');

        $result = $apify->runCollection($targets, $options);

        if (!$result['success']) {
            $this->runModel->updateStatus($runId, 'failed', [
                'error_message' => $result['error'] ?? 'فشل في جلب البيانات'
            ]);
            $this->log('collection', 'فشل جلب البيانات', ['project_id' => $projectId, 'error' => $result['error'] ?? '']);
            flash('error', 'فشل في جلب البيانات: ' . ($result['error'] ?? 'خطأ غير معروف'));
            $this->redirect('/collection');
            return;
        }

        // Store posts
        $items = $result['items'] ?? [];
        $storeResult = $this->postModel->storeFromApify($items, $projectId, $runId);

        $this->runModel->updateStatus($runId, 'completed', [
            'run_id' => $result['run_id'] ?? null,
            'posts_found' => count($items),
            'posts_stored' => $storeResult['stored']
        ]);

        $this->log('collection', 'جلب بيانات ناجح', [
            'project_id' => $projectId,
            'found' => count($items),
            'stored' => $storeResult['stored'],
            'duplicates' => $storeResult['duplicates']
        ]);

        flash('success', "تم جلب البيانات بنجاح: {$storeResult['stored']} منشور جديد، {$storeResult['duplicates']} مكرر");
        $this->redirect('/collection');
    }

    /**
     * Check run status (AJAX)
     */
    public function checkStatus(array $params = []): void
    {
        $runId = (int) ($params['id'] ?? 0);
        $run = $this->runModel->find($runId);

        if (!$run) {
            $this->json(['error' => 'Run not found'], 404);
        }

        $this->json([
            'id' => $run['id'],
            'status' => $run['status'],
            'posts_found' => $run['posts_found'],
            'posts_stored' => $run['posts_stored'],
            'error_message' => $run['error_message'],
            'started_at' => $run['started_at'],
            'completed_at' => $run['completed_at']
        ]);
    }

    /**
     * Re-run a previous collection
     */
    public function rerun(array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $oldRun = $this->runModel->find($id);

        if (!$oldRun) {
            flash('error', 'عملية الجلب غير موجودة');
            $this->redirect('/collection');
            return;
        }

        $targets = json_decode($oldRun['targets'], true) ?? [];
        $config = json_decode($oldRun['input_config'], true) ?? [];

        $newRunId = $this->runModel->create([
            'project_id' => $oldRun['project_id'],
            'actor_id' => $oldRun['actor_id'],
            'status' => 'pending',
            'targets' => $oldRun['targets'],
            'input_config' => $oldRun['input_config']
        ]);

        $apify = new ApifyService();
        $this->runModel->updateStatus($newRunId, 'running');

        $result = $apify->runCollection($targets, $config);

        if (!$result['success']) {
            $this->runModel->updateStatus($newRunId, 'failed', [
                'error_message' => $result['error'] ?? 'فشل'
            ]);
            flash('error', 'فشل في إعادة الجلب');
        } else {
            $items = $result['items'] ?? [];
            $storeResult = $this->postModel->storeFromApify($items, $oldRun['project_id'], $newRunId);
            $this->runModel->updateStatus($newRunId, 'completed', [
                'posts_found' => count($items),
                'posts_stored' => $storeResult['stored']
            ]);
            flash('success', "تم إعادة الجلب: {$storeResult['stored']} منشور جديد");
        }

        $this->redirect('/collection');
    }
}
