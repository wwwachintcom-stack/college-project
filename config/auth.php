<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function requireLogin(): void {
    if (!isset($_SESSION['uid'])) { header('Location: /auth/login.php'); exit; }
}

function requireRole(string|array $roles): void {
    requireLogin();
    if (!in_array($_SESSION['role'], (array)$roles)) { header('Location: /auth/login.php?err=unauthorized'); exit; }
}

function isLoggedIn(): bool { return isset($_SESSION['uid']); }

function me(): array {
    return ['id'=>$_SESSION['uid']??null,'name'=>$_SESSION['name']??'','email'=>$_SESSION['email']??'','role'=>$_SESSION['role']??''];
}

function loginUser(array $u): void {
    $_SESSION['uid']   = oid($u['_id']);
    $_SESSION['name']  = $u['name'];
    $_SESSION['email'] = $u['email'];
    $_SESSION['role']  = $u['role'];
}

function initials(string $name): string {
    preg_match_all('/\b\w/', $name, $m);
    return strtoupper(substr(implode('', $m[0]), 0, 2));
}
