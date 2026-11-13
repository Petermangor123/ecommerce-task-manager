
<?php
// setup: timezone, errors, base path, session
date_default_timezone_set('Africa/Accra');

function shoppn_http_host(): string
{
    $host = strtolower($_SERVER['HTTP_HOST'] ?? '');
    return (string)preg_replace('/:\d+$/', '', $host);
}

function shoppn_is_local_request(): bool
{
    return in_array(shoppn_http_host(), ['localhost', '127.0.0.1', '::1'], true);
}

function shoppn_is_https(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }
    if (isset($_SERVER['SERVER_PORT']) && (string)$_SERVER['SERVER_PORT'] === '443') {
        return true;
    }
    return strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
}

// show php errors on localhost only
error_reporting(E_ALL);
ini_set('log_errors', '1');
if (shoppn_is_local_request()) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
}

// path to this project on the site, with a trailing slash
function shoppn_app_base(): string
{
    $appRoot = realpath(__DIR__ . '/..');
    $scriptFile = realpath($_SERVER['SCRIPT_FILENAME'] ?? '');
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');

    if ($appRoot && $scriptFile && $scriptName !== '') {
        $appNorm = str_replace('\\', '/', $appRoot);
        $scriptNorm = str_replace('\\', '/', $scriptFile);
        $prefixLen = strlen($appNorm);
        if (stripos($scriptNorm, $appNorm) === 0) {
            $inside = substr($scriptNorm, $prefixLen);
            $inside = '/' . ltrim(str_replace('\\', '/', $inside), '/');
            if ($inside !== '/' && substr($scriptName, -strlen($inside)) === $inside) {
                $base = substr($scriptName, 0, strlen($scriptName) - strlen($inside));
                $base = '/' . trim($base, '/') . '/';
                return $base === '//' ? '/' : $base;
            }
        }
    }

    // try the document root if the script path didn't match
    $docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    if ($appRoot && $docRoot) {
        $appNorm = str_replace('\\', '/', $appRoot);
        $docNorm = str_replace('\\', '/', $docRoot);
        if (stripos($appNorm, $docNorm) === 0) {
            $relative = substr($appNorm, strlen($docNorm));
            $relative = '/' . trim(str_replace('\\', '/', $relative), '/') . '/';
            return $relative === '//' ? '/' : $relative;
        }
    }

    return '/';
}

define('APP_BASE', shoppn_app_base());

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => APP_BASE,
        'secure' => shoppn_is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
// Load the database connection class
require_once __DIR__ . '/db_class.php';

// Return the visitor's IP address
function get_ip() {
    return $_SERVER['REMOTE_ADDR'] ?? '';
}
// Send a redirect header and stop execution
function redirect($url) {
    header("Location: $url");
    exit();
}

// Redirect to a same-host referrer under APP_BASE, otherwise home
function redirect_back($fallback = null) {
    $fallback = $fallback ?: (APP_BASE . 'index.php');
    $referer  = $_SERVER['HTTP_REFERER'] ?? '';
    if ($referer === '') {
        redirect($fallback);
    }

    $parts = parse_url($referer);
    if ($parts === false || empty($parts['host'])) {
        redirect($fallback);
    }

    $currentHost = $_SERVER['HTTP_HOST'] ?? '';
    if (strcasecmp($parts['host'], $currentHost) !== 0) {
        redirect($fallback);
    }

    $path     = $parts['path'] ?? '/';
    $basePath = parse_url(APP_BASE, PHP_URL_PATH);
    if (!is_string($basePath) || $basePath === '') {
        $basePath = '/';
    }
    if (strpos($path, $basePath) !== 0) {
        redirect($fallback);
    }

    $query = [];
    if (!empty($parts['query'])) {
        parse_str($parts['query'], $query);
        unset($query['add_cart']);
    }

    $scheme = $parts['scheme'] ?? (shoppn_is_https() ? 'https' : 'http');
    $safe   = $scheme . '://' . $parts['host'];
    if (isset($parts['port'])) {
        $safe .= ':' . $parts['port'];
    }
    $safe .= $path;
    if ($query) {
        $safe .= '?' . http_build_query($query);
    }
    redirect($safe);
}

// Session CSRF token for cart modification forms
function csrf_token() {
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_validate() {
    $token = $_POST['csrf_token'] ?? '';
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

// Check whether a customer is logged in
function is_logged_in() {
    return isset($_SESSION['customer_id']);
}
// Check whether the logged-in user is an admin
function is_admin() {
    return isset($_SESSION['user_role'])
        && (int) $_SESSION['user_role'] === 1;
}
// Redirect unauthenticated users to login
function require_login() {
    if (!is_logged_in()) {
        redirect(APP_BASE . 'views/login.php');
    }
}

// Redirect non-admin users to the home page
function require_admin() {
    if (!is_admin()) {
        $_SESSION['error'] = 'Access denied. Admins only.';
        redirect(APP_BASE . 'index.php');
    }
}
