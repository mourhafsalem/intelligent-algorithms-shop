<?php
declare(strict_types=1);

/**
 * نظام دخول مبسط - بدون قاعدة بيانات
 * - المستخدم يكتب user_id (1-1000) من ملف users.csv
 * - كلمة المرور الموحّدة: "password" (للعرض الأكاديمي)
 * - الأدمن: user_id = 1 + كلمة المرور admin123
 */
class Auth
{
    public const ADMIN_ID = 1;
    public const ADMIN_PASS = 'admin123';
    public const DEFAULT_PASS = 'password';

    public static function login(string $userIdStr, string $password): ?array
    {
        if (!is_numeric($userIdStr)) return null;
        $userId = (int)$userIdStr;
        $user = DataStore::userById($userId);
        if (!$user) return null;

        if ($userId === self::ADMIN_ID) {
            if ($password !== self::ADMIN_PASS) return null;
            $user['is_admin'] = true;
        } else {
            if ($password !== self::DEFAULT_PASS) return null;
            $user['is_admin'] = false;
        }

        $_SESSION['user'] = $user;
        return $user;
    }

    public static function logout(): void
    {
        unset($_SESSION['user']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function isAdmin(): bool
    {
        return !empty($_SESSION['user']['is_admin']);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: /shop/login.php');
            exit;
        }
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            http_response_code(403);
            exit('Access denied');
        }
    }
}
