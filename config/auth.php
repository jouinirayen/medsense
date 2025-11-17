<?php
/**
 * Gestion de l'authentification et des sessions
 */

session_start();

/**
 * Vérifie si l'utilisateur est connecté
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Vérifie si l'utilisateur est admin
 */
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

/**
 * Définir l'utilisateur connecté
 */
function setUser($userId, $username, $email, $isAdmin = 0) {
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    $_SESSION['is_admin'] = $isAdmin;
    $_SESSION['login_time'] = time();
}

/**
 * Obtenir l'ID de l'utilisateur connecté
 */
function getUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

/**
 * Obtenir les informations de l'utilisateur connecté
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }

    return array(
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'email' => $_SESSION['email'],
        'is_admin' => $_SESSION['is_admin']
    );
}

/**
 * Déconnexion
 */
function logout() {
    session_destroy();
    unset($_SESSION);
}

/**
 * Rediriger vers la page de connexion si non connecté
 */
function requireLogin($redirect = null) {
    if (!isLoggedIn()) {
        if ($redirect === null) {
            $redirect = SITE_URL . 'login.php';
        }
        header('Location: ' . $redirect);
        exit();
    }
}

/**
 * Rediriger vers page admin si non admin
 */
function requireAdmin($redirect = null) {
    requireLogin();
    if (!isAdmin()) {
        if ($redirect === null) {
            $redirect = SITE_URL . 'index.php';
        }
        header('Location: ' . $redirect);
        exit();
    }
}

?>
