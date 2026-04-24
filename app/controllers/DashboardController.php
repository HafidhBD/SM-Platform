<?php
/**
 * Dashboard Controller
 */
class DashboardController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
    }

    public function index(array $params = []): void
    {
        $projectModel = new ProjectModel();
        $postModel = new PostModel();
        $alertModel = new AlertModel();

        $projectId = (int) ($this->get('project', 0));
        $projects = $projectModel->getAllActive();

        // If no project selected, use first active
        if (!$projectId && !empty($projects)) {
            $projectId = $projects[0]['id'];
        }

        $stats = $projectId ? $postModel->getDashboardStats($projectId) : [
            'total_posts' => 0, 'positive_count' => 0, 'negative_count' => 0,
            'neutral_count' => 0, 'negative_percent' => 0, 'alerts_count' => 0,
            'last_collection' => null, 'last_analysis' => null
        ];

        $timeline = $projectId ? $postModel->getTimeline($projectId, 'day', date('Y-m-d', strtotime('-30 days'))) : [];
        $sentimentTimeline = $projectId ? $postModel->getSentimentTimeline($projectId, 'day', date('Y-m-d', strtotime('-30 days'))) : [];
        $topTopics = $projectId ? $postModel->getTopTopics($projectId, 10) : [];
        $topWords = $projectId ? $postModel->getTopWords($projectId, 15) : [];
        $topAuthors = $projectId ? $postModel->getTopAuthors($projectId, 5) : [];
        $mostEngaged = $projectId ? $postModel->getMostEngaged($projectId, 5) : [];
        $unreadAlerts = $alertModel->getUnread($projectId);

        $csrfToken = $this->generateCsrfToken();

        $this->view('dashboard/index', [
            'projects' => $projects,
            'current_project_id' => $projectId,
            'stats' => $stats,
            'timeline' => $timeline,
            'sentiment_timeline' => $sentimentTimeline,
            'top_topics' => $topTopics,
            'top_words' => $topWords,
            'top_authors' => $topAuthors,
            'most_engaged' => $mostEngaged,
            'unread_alerts' => $unreadAlerts,
            'csrf_token' => $csrfToken,
            'page_title' => 'لوحة التحكم'
        ]);
    }
}
