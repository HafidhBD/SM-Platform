<?php
/**
 * Posts Controller - Browse and filter posts
 */
class PostController extends BaseController
{
    private PostModel $postModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->postModel = new PostModel();
    }

    public function index(array $params = []): void
    {
        $projectId = (int) ($this->get('project', 0));
        $page = (int) ($this->get('page', 1));
        $filters = [
            'sentiment' => $this->get('sentiment', ''),
            'topic' => $this->get('topic', ''),
            'reputation' => $this->get('reputation', ''),
            'risk_min' => $this->get('risk_min', ''),
            'author' => $this->get('author', ''),
            'date_from' => $this->get('date_from', ''),
            'date_to' => $this->get('date_to', ''),
            'search' => $this->get('search', ''),
            'sort' => $this->get('sort', 'p.posted_at DESC'),
            'crisis_flag' => $this->get('crisis_flag', '')
        ];

        $projectModel = new ProjectModel();
        $projects = $projectModel->getAllActive();

        $posts = $projectId ? $this->postModel->getWithAnalysis($projectId, $filters, $page, 25) : [
            'items' => [], 'total' => 0, 'page' => 1, 'per_page' => 25, 'total_pages' => 0
        ];

        $csrfToken = $this->generateCsrfToken();
        $this->view('posts/index', [
            'projects' => $projects,
            'current_project_id' => $projectId,
            'posts' => $posts,
            'filters' => $filters,
            'csrf_token' => $csrfToken,
            'page_title' => 'استكشاف المنشورات'
        ]);
    }

    public function view(array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $post = $this->db->queryOne(
            "SELECT p.*, pa.sentiment, pa.sentiment_score, pa.reputation_label,
                    pa.crisis_flag, pa.attack_flag, pa.complaint_flag, pa.sarcasm_flag,
                    pa.topic_label, pa.risk_score, pa.ai_summary, pa.ai_keywords, pa.analyzed_at
             FROM posts p LEFT JOIN post_ai_analysis pa ON p.id = pa.post_id
             WHERE p.id = ? AND p.deleted_at IS NULL",
            [$id]
        );

        if (!$post) {
            $this->json(['error' => 'المنشور غير موجود'], 404);
            return;
        }

        if ($this->isAjax()) {
            $this->json($post);
        } else {
            $this->view('posts/detail', [
                'post' => $post,
                'page_title' => 'تفاصيل المنشور'
            ]);
        }
    }
}
