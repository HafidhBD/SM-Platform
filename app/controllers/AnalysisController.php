<?php
/**
 * Analysis Controller - AI analysis management
 */
class AnalysisController extends BaseController
{
    private AnalysisModel $analysisModel;
    private ProjectModel $projectModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->analysisModel = new AnalysisModel();
        $this->projectModel = new ProjectModel();
    }

    public function index(array $params = []): void
    {
        $projectId = (int) ($this->get('project', 0));
        $projects = $this->projectModel->getAllActive();

        $analysisStats = $projectId ? $this->analysisModel->getProjectAnalysisStats($projectId) : [
            'total_posts' => 0, 'analyzed_posts' => 0, 'unanalyzed_posts' => 0
        ];

        $csrfToken = $this->generateCsrfToken();
        $this->view('analysis/index', [
            'projects' => $projects,
            'current_project_id' => $projectId,
            'analysis_stats' => $analysisStats,
            'csrf_token' => $csrfToken,
            'page_title' => 'تحليل الذكاء الاصطناعي'
        ]);
    }

    /**
     * Run analysis on unanalyzed posts
     */
    public function run(array $params = []): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/analysis');
        }

        if (!$this->verifyCsrf()) {
            flash('error', 'خطأ في التحقق من الأمان');
            $this->redirect('/analysis');
            return;
        }

        $projectId = (int) $this->post('project_id', 0);
        $forceReanalyze = (bool) $this->post('force_reanalyze', false);

        if (!$projectId) {
            flash('error', 'يرجى اختيار الجهة');
            $this->redirect('/analysis');
            return;
        }

        $this->log('analysis', 'بدء تحليل الذكاء الاصطناعي', ['project_id' => $projectId, 'force' => $forceReanalyze]);

        $result = $this->analysisModel->analyzeProjectPosts($projectId, $forceReanalyze);

        if ($result['success']) {
            $msg = "تم تحليل {$result['analyzed']} منشور";
            if (isset($result['tokens_used'])) {
                $msg .= " ({$result['tokens_used']} tokens)";
            }
            flash('success', $msg);
        } else {
            flash('error', 'فشل التحليل: ' . ($result['error'] ?? 'خطأ غير معروف'));
        }

        $this->redirect('/analysis?project=' . $projectId);
    }

    /**
     * Generate executive summary
     */
    public function summary(array $params = []): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/analysis');
        }

        if (!$this->verifyCsrf()) {
            flash('error', 'خطأ في التحقق من الأمان');
            $this->redirect('/analysis');
            return;
        }

        $projectId = (int) $this->post('project_id', 0);
        $type = $this->post('summary_type', 'manual');
        $periodStart = $this->post('period_start', null);
        $periodEnd = $this->post('period_end', null);

        if (!$projectId) {
            flash('error', 'يرجى اختيار الجهة');
            $this->redirect('/analysis');
            return;
        }

        $summaryModel = new SummaryModel();
        $result = $summaryModel->generateSummary($projectId, $type, $periodStart, $periodEnd);

        if ($result['success']) {
            flash('success', 'تم إنشاء الملخص التنفيذي بنجاح');
        } else {
            flash('error', 'فشل إنشاء الملخص: ' . ($result['error'] ?? 'خطأ'));
        }

        $this->redirect('/analysis?project=' . $projectId);
    }

    /**
     * View a saved summary
     */
    public function viewSummary(array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $summaryModel = new SummaryModel();
        $summary = $summaryModel->find($id);

        if (!$summary) {
            flash('error', 'الملخص غير موجود');
            $this->redirect('/analysis');
            return;
        }

        $this->view('analysis/summary', [
            'summary' => $summary,
            'page_title' => 'الملخص التنفيذي'
        ]);
    }

    /**
     * Run crisis detection
     */
    public function crisisCheck(array $params = []): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/analysis');
        }

        if (!$this->verifyCsrf()) {
            flash('error', 'خطأ في التحقق');
            $this->redirect('/analysis');
            return;
        }

        $projectId = (int) $this->post('project_id', 0);
        if (!$projectId) {
            flash('error', 'يرجى اختيار الجهة');
            $this->redirect('/analysis');
            return;
        }

        $project = $this->projectModel->find($projectId);
        $postModel = new PostModel();
        $openai = new OpenAIService();

        $stats = $postModel->getDashboardStats($projectId);
        $context = [
            'project_name' => $project['name'] ?? '',
            'total_posts' => $stats['total_posts'],
            'negative_percent' => $stats['negative_percent'],
            'negative_count' => $stats['negative_count'],
            'positive_count' => $stats['positive_count']
        ];

        $result = $openai->detectCrisis($context);

        if ($result['success'] && !empty($result['crisis'])) {
            $crisis = $result['crisis'];
            if (!empty($crisis['crisis_detected']) || ($crisis['crisis_level'] ?? 'none') !== 'none') {
                $alertModel = new AlertModel();
                $severity = match ($crisis['crisis_level'] ?? 'none') {
                    'critical' => 'critical',
                    'active' => 'high',
                    'emerging' => 'medium',
                    default => 'low'
                };
                $alertModel->createAlert(
                    $projectId,
                    'attack_detected',
                    $severity,
                    'تنبيه أزمة: ' . ($crisis['crisis_type'] ?? 'غير محدد'),
                    json_encode($crisis, JSON_UNESCAPED_UNICODE),
                    $crisis
                );
            }
            flash('success', 'تم فحص الأزمة - النتيجة: ' . ($crisis['crisis_level'] ?? 'لا توجد أزمة'));
        } else {
            flash('error', 'فشل فحص الأزمة');
        }

        $this->redirect('/analysis?project=' . $projectId);
    }
}
