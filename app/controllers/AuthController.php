<?php
/**
 * Auth Controller - Login/Logout
 */
class AuthController extends BaseController
{
    public function login(array $params = []): void
    {
        if (Auth::check()) {
            $this->redirect('/');
        }

        $error = '';
        $csrfToken = $this->generateCsrfToken();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCsrf()) {
                $error = 'خطأ في التحقق من الأمان';
            } else {
                $username = $this->post('username', '');
                $password = $this->post('password', '');

                if (empty($username) || empty($password)) {
                    $error = 'يرجى إدخال اسم المستخدم وكلمة المرور';
                } elseif (Auth::attempt($username, $password)) {
                    $this->log('auth', 'تسجيل دخول ناجح', ['username' => $username]);
                    $this->redirect('/');
                } else {
                    $error = 'اسم المستخدم أو كلمة المرور غير صحيح';
                    $this->log('auth', 'محاولة دخول فاشلة', ['username' => $username]);
                }
            }
        }

        $expired = $this->get('expired', '');
        if ($expired) {
            $error = 'انتهت صلاحية الجلسة، يرجى تسجيل الدخول مجددًا';
        }

        $this->view('auth/login', [
            'error' => $error,
            'csrf_token' => $csrfToken
        ]);
    }

    public function logout(array $params = []): void
    {
        $this->log('auth', 'تسجيل خروج');
        Auth::logout();
        $this->redirect('/login');
    }
}
