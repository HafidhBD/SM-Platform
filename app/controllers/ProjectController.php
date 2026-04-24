<?php
/**
 * Project Controller - CRUD for brands/entities
 */
class ProjectController extends BaseController
{
    private ProjectModel $projectModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->projectModel = new ProjectModel();
    }

    public function index(array $params = []): void
    {
        $projects = $this->projectModel->findAll('1=1', [], 'name ASC');
        $csrfToken = $this->generateCsrfToken();

        $this->view('projects/index', [
            'projects' => $projects,
            'csrf_token' => $csrfToken,
            'page_title' => 'الجهات والمشاريع'
        ]);
    }

    public function create(array $params = []): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCsrf()) {
                flash('error', 'خطأ في التحقق من الأمان');
                $this->redirect('/projects');
                return;
            }

            $name = $this->sanitize($this->post('name', ''));
            $description = $this->sanitize($this->post('description', ''));

            if (empty($name)) {
                flash('error', 'اسم الجهة مطلوب');
                $this->redirect('/projects/create');
                return;
            }

            $projectId = $this->projectModel->create([
                'name' => $name,
                'description' => $description,
                'is_active' => 1
            ]);

            if ($projectId) {
                $this->saveProjectRelations($projectId);
                $this->log('project', 'إنشاء جهة جديدة', ['id' => $projectId, 'name' => $name]);
                flash('success', 'تم إنشاء الجهة بنجاح');
                $this->redirect('/projects');
            } else {
                flash('error', 'فشل في إنشاء الجهة');
                $this->redirect('/projects/create');
            }
            return;
        }

        $csrfToken = $this->generateCsrfToken();
        $this->view('projects/form', [
            'csrf_token' => $csrfToken,
            'page_title' => 'إضافة جهة جديدة',
            'project' => null,
            'mode' => 'create'
        ]);
    }

    public function edit(array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $project = $this->projectModel->getWithStats($id);

        if (!$project) {
            flash('error', 'الجهة غير موجودة');
            $this->redirect('/projects');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCsrf()) {
                flash('error', 'خطأ في التحقق من الأمان');
                $this->redirect('/projects');
                return;
            }

            $name = $this->sanitize($this->post('name', ''));
            $description = $this->sanitize($this->post('description', ''));
            $isActive = (int) $this->post('is_active', 0);

            if (empty($name)) {
                flash('error', 'اسم الجهة مطلوب');
                $this->redirect("/projects/edit/{$id}");
                return;
            }

            $this->projectModel->update($id, [
                'name' => $name,
                'description' => $description,
                'is_active' => $isActive
            ]);

            $this->saveProjectRelations($id);
            $this->log('project', 'تعديل جهة', ['id' => $id]);
            flash('success', 'تم تحديث الجهة بنجاح');
            $this->redirect('/projects');
            return;
        }

        $csrfToken = $this->generateCsrfToken();
        $this->view('projects/form', [
            'csrf_token' => $csrfToken,
            'page_title' => 'تعديل الجهة',
            'project' => $project,
            'mode' => 'edit'
        ]);
    }

    public function delete(array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->verifyCsrf()) {
            $this->projectModel->delete($id, true);
            $this->log('project', 'حذف جهة', ['id' => $id]);
            flash('success', 'تم حذف الجهة');
        }
        $this->redirect('/projects');
    }

    private function saveProjectRelations(int $projectId): void
    {
        // Keywords
        $searchKeywords = array_filter(explode("\n", $this->post('search_keywords', '')), fn($k) => trim($k));
        $crisisKeywords = array_filter(explode("\n", $this->post('crisis_keywords', '')), fn($k) => trim($k));
        $this->projectModel->clearKeywords($projectId, 'search');
        $this->projectModel->clearKeywords($projectId, 'crisis');
        $this->projectModel->addKeywords($projectId, $searchKeywords, 'search');
        $this->projectModel->addKeywords($projectId, $crisisKeywords, 'crisis');

        // Competitors
        $competitors = [];
        $compNames = array_filter(explode("\n", $this->post('competitors', '')), fn($c) => trim($c));
        foreach ($compNames as $name) {
            $competitors[] = ['name' => trim($name), 'username' => '', 'notes' => ''];
        }
        $this->projectModel->clearCompetitors($projectId);
        $this->projectModel->addCompetitors($projectId, $competitors);

        // Hashtags
        $hashtags = array_filter(explode("\n", $this->post('hashtags', '')), fn($h) => trim($h));
        $this->projectModel->clearHashtags($projectId);
        $this->projectModel->addHashtags($projectId, $hashtags);

        // Accounts
        $accounts = array_filter(explode("\n", $this->post('accounts', '')), fn($a) => trim($a));
        $this->projectModel->clearAccounts($projectId);
        $this->projectModel->addAccounts($projectId, $accounts);
    }
}
