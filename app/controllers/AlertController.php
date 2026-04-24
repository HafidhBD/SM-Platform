<?php
/**
 * Alert Controller
 */
class AlertController extends BaseController
{
    private AlertModel $alertModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->alertModel = new AlertModel();
    }

    public function index(array $params = []): void
    {
        $projectId = (int) ($this->get('project', 0));
        $severity = $this->get('severity', '');
        $showResolved = (bool) $this->get('resolved', false);

        $projectModel = new ProjectModel();
        $projects = $projectModel->getAllActive();

        if ($projectId) {
            $alerts = $showResolved 
                ? $this->alertModel->getByProject($projectId, $severity, 100)
                : $this->alertModel->getUnresolved($projectId);
        } else {
            $alerts = $showResolved
                ? $this->db->query("SELECT * FROM alerts ORDER BY created_at DESC LIMIT 100")
                : $this->alertModel->getUnresolved();
        }

        $severityCounts = $this->alertModel->countBySeverity($projectId);

        $csrfToken = $this->generateCsrfToken();
        $this->view('alerts/index', [
            'projects' => $projects,
            'current_project_id' => $projectId,
            'alerts' => $alerts,
            'severity_counts' => $severityCounts,
            'current_severity' => $severity,
            'show_resolved' => $showResolved,
            'csrf_token' => $csrfToken,
            'page_title' => 'التنبيهات'
        ]);
    }

    public function markRead(array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        if ($id) {
            $this->alertModel->markRead($id);
        }
        if ($this->isAjax()) {
            $this->json(['success' => true]);
        } else {
            $this->redirect('/alerts');
        }
    }

    public function markAllRead(array $params = []): void
    {
        $projectId = (int) ($this->get('project', 0));
        if ($this->verifyCsrf()) {
            $this->alertModel->markAllRead($projectId);
            flash('success', 'تم تحديد جميع التنبيهات كمقروءة');
        }
        $this->redirect('/alerts');
    }

    public function resolve(array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->verifyCsrf()) {
            $this->alertModel->resolve($id, Auth::userId());
            if ($this->isAjax()) {
                $this->json(['success' => true]);
            } else {
                flash('success', 'تم حل التنبيه');
                $this->redirect('/alerts');
            }
        }
    }
}
