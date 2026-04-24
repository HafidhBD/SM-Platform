<?php
/**
 * Report Controller
 */
class ReportController extends BaseController
{
    private SummaryModel $summaryModel;
    private ProjectModel $projectModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->summaryModel = new SummaryModel();
        $this->projectModel = new ProjectModel();
    }

    public function index(array $params = []): void
    {
        $projectId = (int) ($this->get('project', 0));
        $projects = $this->projectModel->getAllActive();

        $summaries = $projectId ? $this->summaryModel->getByProject($projectId, 30) : [];

        $csrfToken = $this->generateCsrfToken();
        $this->view('reports/index', [
            'projects' => $projects,
            'current_project_id' => $projectId,
            'summaries' => $summaries,
            'csrf_token' => $csrfToken,
            'page_title' => 'التقارير'
        ]);
    }

    /**
     * Generate a new report
     */
    public function generate(array $params = []): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->verifyCsrf()) {
            $this->redirect('/reports');
            return;
        }

        $projectId = (int) $this->post('project_id', 0);
        $type = $this->post('report_type', 'manual');
        $periodStart = $this->post('period_start') ?: null;
        $periodEnd = $this->post('period_end') ?: null;

        if (!$projectId) {
            flash('error', 'يرجى اختيار الجهة');
            $this->redirect('/reports');
            return;
        }

        // Auto-set period based on type
        if ($type === 'daily' && !$periodStart) {
            $periodStart = date('Y-m-d');
            $periodEnd = date('Y-m-d');
        } elseif ($type === 'weekly' && !$periodStart) {
            $periodStart = date('Y-m-d', strtotime('-7 days'));
            $periodEnd = date('Y-m-d');
        }

        $result = $this->summaryModel->generateSummary($projectId, $type, $periodStart, $periodEnd);

        if ($result['success']) {
            flash('success', 'تم إنشاء التقرير بنجاح');
            $this->redirect('/reports/view/' . $result['summary_id']);
        } else {
            flash('error', 'فشل إنشاء التقرير: ' . ($result['error'] ?? 'خطأ'));
            $this->redirect('/reports');
        }
    }

    /**
     * View a report
     */
    public function view(array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $summary = $this->summaryModel->find($id);

        if (!$summary) {
            flash('error', 'التقرير غير موجود');
            $this->redirect('/reports');
            return;
        }

        $this->view('reports/view', [
            'summary' => $summary,
            'page_title' => 'تقرير تحليلي'
        ]);
    }

    /**
     * Export report as HTML (printable)
     */
    public function exportHtml(array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $summary = $this->summaryModel->find($id);

        if (!$summary) {
            flash('error', 'التقرير غير موجود');
            $this->redirect('/reports');
            return;
        }

        $this->view('reports/export', [
            'summary' => $summary,
            'page_title' => 'تصدير التقرير'
        ]);
    }
}
