<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../frontoffice/auth/sign-in.php');
    exit;
}

require_once __DIR__ . '/../../controllers/AdminController.php';
$adminController = new AdminController();
$usersResult = $adminController->manageUsers('list');
$allUsers = $usersResult['success'] ? $usersResult['users'] : [];

$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';
$statut_filter = $_GET['statut'] ?? '';

$users = $allUsers;

if ($search || $role_filter || $statut_filter) {
    $users = array_filter($allUsers, function($user) use ($search, $role_filter, $statut_filter) {
        $match_search = true;
        $match_role = true;
        $match_statut = true;

        if ($search) {
            $search_term = strtolower(trim($search));
            $nom = strtolower($user['nom'] ?? '');
            $prenom = strtolower($user['prenom'] ?? '');
            $email = strtolower($user['email'] ?? '');
            
            $match_search = strpos($nom, $search_term) !== false ||
                           strpos($prenom, $search_term) !== false ||
                           strpos($email, $search_term) !== false;
        }
        if ($role_filter) {
            $match_role = ($user['role'] ?? '') === $role_filter;
        }
        if ($statut_filter) {
            $match_statut = ($user['statut'] ?? '') === $statut_filter;
        }
        
        return $match_search && $match_role && $match_statut;
    });
    $users = array_values($users);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - Medsense Medical</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../assets/vendors/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    
    <style>
        .dashboard-page {
            min-height: 100vh;
            background: #f8fafc;
        }

        .dashboard-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            grid-template-rows: 70px 1fr;
            grid-template-areas:
                "sidebar header"
                "sidebar main";
            min-height: 100vh;
        }
        .dashboard-header {
            grid-area: header;
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .dashboard-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #64748b;
            cursor: pointer;
        }

        .dashboard-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
        }

        .dashboard-subtitle {
            font-size: 0.875rem;
            color: #64748b;
        }

        .dashboard-user-info {
            display: flex;
            align-items: center;
        }

        .dashboard-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #3b82f6;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
        }

        .dashboard-user-details {
            line-height: 1.4;
        }

        .dashboard-user-name {
            font-weight: 600;
            color: #1e293b;
        }

        .dashboard-user-role {
            font-size: 0.75rem;
            color: #64748b;
        }

        .dashboard-sidebar {
            grid-area: sidebar;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            color: white;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .dashboard-logo {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .dashboard-logo-img {
            max-height: 40px;
            margin-right: 12px;
        }

        .dashboard-logo-text {
            font-size: 1.25rem;
            font-weight: 600;
            color: white;
        }

        .dashboard-nav {
            flex: 1;
            padding: 20px 0;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .dashboard-nav-section {
            margin-bottom: 20px;
        }

        .dashboard-nav-title {
            padding: 0 20px 8px;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #94a3b8;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .dashboard-nav-item {
            padding: 12px 20px;
            color: #cbd5e1;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s;
            border-left: 3px solid transparent;
            cursor: pointer;
        }

        .dashboard-nav-item:hover,
        .dashboard-nav-item.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: #3b82f6;
        }

        .dashboard-nav-item.logout {
            color: #f87171;
            margin-top: auto;
        }

        .dashboard-nav-item.logout:hover {
            background: rgba(248, 113, 113, 0.1);
        }

        .dashboard-nav-item i {
            width: 20px;
            text-align: center;
        }

        .dashboard-badge {
            background: #ef4444;
            color: white;
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 12px;
            margin-left: 8px;
        }
        .with-submenu {
            flex-direction: column;
            padding: 0;
        }

        .submenu-toggle {
            transition: transform 0.3s;
        }

        .submenu-toggle.open {
            transform: rotate(180deg);
        }

        .dashboard-submenu {
            display: none;
            background: rgba(0, 0, 0, 0.2);
            border-left: 3px solid #3b82f6;
        }

        .dashboard-submenu-item {
            padding: 10px 20px 10px 45px;
            color: #cbd5e1;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s;
            font-size: 0.875rem;
        }

        .dashboard-submenu-item:hover {
            background: rgba(255, 255, 255, 0.05);
            color: white;
        }

        .dashboard-submenu-item.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left: 3px solid #3b82f6;
        }
        .dashboard-main {
            grid-area: main;
            padding: 24px;
            overflow-y: auto;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
            background: linear-gradient(135deg, #f8fafc 0%, #f0f9ff 100%);
        }
        .dashboard-alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-warning {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .dashboard-alert i {
            margin-top: 2px;
        }

        .dashboard-alert-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: inherit;
            cursor: pointer;
            margin-left: auto;
            opacity: 0.7;
        }

        .dashboard-alert-close:hover {
            opacity: 1;
        }
        .dashboard-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            margin-bottom: 24px;
            overflow: hidden;
        }

        .dashboard-card-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dashboard-card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .dashboard-card-badge {
            background: #3b82f6;
            color: white;
            font-size: 0.75rem;
            padding: 4px 12px;
            border-radius: 20px;
        }

        .dashboard-card-badge.danger {
            background: #ef4444;
        }

        .dashboard-card-body {
            padding: 24px;
        }

        .dashboard-card-footer {
            padding: 16px 24px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
        }

        .dashboard-table-responsive {
            overflow-x: auto;
        }

        .dashboard-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .dashboard-table th {
            background: #f8fafc;
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        .dashboard-table td {
            padding: 16px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .dashboard-table tbody tr:hover {
            background: #f8fafc;
        }
        .dashboard-user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .dashboard-user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #3b82f6;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            flex-shrink: 0;
        }

        .dashboard-user-name {
            font-weight: 600;
            color: #1e293b;
        }

        .dashboard-user-email {
            font-size: 0.875rem;
            color: #64748b;
        }
        .dashboard-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .role-admin {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
        }

        .role-user {
            background: rgba(6, 182, 212, 0.1);
            color: #0891b2;
        }

        .role-moderator {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
        }

        .role-success {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .role-warning {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .role-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .role-secondary {
            background: rgba(148, 163, 184, 0.1);
            color: #94a3b8;
        }

        .role-info {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .dashboard-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-actif {
            background: rgba(34, 197, 94, 0.1);
            color: #16a34a;
        }

        .status-inactif {
            background: rgba(148, 163, 184, 0.1);
            color: #64748b;
        }
        .dashboard-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 24px;
        }

        .dashboard-btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            font-size: 0.875rem;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .btn-success {
            background: #22c55e;
            color: white;
        }

        .btn-success:hover {
            background: #16a34a;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-outline {
            background: white;
            color: #64748b;
            border: 1px solid #d1d5db;
        }

        .btn-outline:hover {
            background: #f8fafc;
            border-color: #9ca3af;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.75rem;
        }
        .dashboard-empty {
            text-align: center;
            padding: 48px 24px;
            color: #64748b;
        }

        .dashboard-empty i {
            opacity: 0.5;
            margin-bottom: 16px;
        }

        .dashboard-empty h5 {
            color: #64748b;
            margin-bottom: 8px;
        }

        .dashboard-empty p {
            font-size: 0.875rem;
            color: #94a3b8;
        }
        @media (max-width: 1024px) {
            .dashboard-container {
                grid-template-columns: 200px 1fr;
            }
            
            .dashboard-sidebar {
                width: 200px;
            }
        }

        @media (max-width: 768px) {
            .dashboard-container {
                grid-template-columns: 1fr;
                grid-template-areas:
                    "header"
                    "main";
            }
            
            .dashboard-sidebar {
                position: fixed;
                left: -250px;
                top: 0;
                bottom: 0;
                z-index: 1000;
                width: 250px;
                transition: left 0.3s;
            }
            
            .dashboard-sidebar.active {
                left: 0;
            }
            
            .dashboard-menu-toggle {
                display: block;
            }
            
            .dashboard-main {
                padding: 16px;
            }
        }

        @media (max-width: 640px) {
            .dashboard-header {
                padding: 0 16px;
            }
            
            .dashboard-card-body {
                padding: 16px;
            }
        }
        .users-container {
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }
        .users-alert {
            margin: 20px 0;
            border-radius: 12px;
            border-left: 4px solid;
            padding: 16px 20px;
            font-size: 15px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            animation: slideIn 0.3s ease;
        }

        .users-alert.alert-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
            color: #166534;
            border-left-color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .users-alert.alert-danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%);
            color: #991b1b;
            border-left-color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .users-alert.alert-warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0.05) 100%);
            color: #92400e;
            border-left-color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }


        .users-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 24px 0;
        }

        .users-stat-card {
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-top: 4px solid;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .users-stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .users-stat-card.primary {
            border-top-color: #3b82f6;
        }

        .users-stat-card.success {
            border-top-color: #10b981;
        }

        .users-stat-card.warning {
            border-top-color: #f59e0b;
        }

        .users-stat-card.danger {
            border-top-color: #ef4444;
        }

        .users-stat-card.info {
            border-top-color: #06b6d4;
        }

        .users-stat-card.secondary {
            border-top-color: #94a3b8;
        }

        .users-stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .users-stat-icon.primary {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(59, 130, 246, 0.2) 100%);
            color: #3b82f6;
        }

        .users-stat-icon.success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.2) 100%);
            color: #10b981;
        }

        .users-stat-icon.warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0.2) 100%);
            color: #f59e0b;
        }

        .users-stat-icon.danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.2) 100%);
            color: #ef4444;
        }

        .users-stat-icon.info {
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.1) 0%, rgba(6, 182, 212, 0.2) 100%);
            color: #06b6d4;
        }

        .users-stat-icon.secondary {
            background: linear-gradient(135deg, rgba(148, 163, 184, 0.1) 0%, rgba(148, 163, 184, 0.2) 100%);
            color: #94a3b8;
        }

        .users-stat-value {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 4px;
            color: #1e293b;
            line-height: 1;
        }

        .users-stat-label {
            color: #64748b;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 600;
        }

        .users-filter-form {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin: 24px 0;
        }

        .users-table-container {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin: 24px 0;
        }

        .users-table-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .users-table-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .users-table-badge {
            background: #3b82f6;
            color: white;
            font-size: 0.75rem;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
        }

        .users-table-badge.danger {
            background: #ef4444;
        }

        .users-table-content {
            padding: 0;
        }


        .users-status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            gap: 6px;
        }

        .users-status-badge.actif {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.2) 100%);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .users-status-badge.inactif {
            background: linear-gradient(135deg, rgba(148, 163, 184, 0.1) 0%, rgba(148, 163, 184, 0.2) 100%);
            color: #64748b;
            border: 1px solid rgba(148, 163, 184, 0.3);
        }

    
        .users-user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .users-user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            flex-shrink: 0;
        }

        .users-user-name {
            font-weight: 600;
            color: #1e293b;
        }

        .users-user-email {
            font-size: 0.875rem;
            color: #64748b;
        }

        .users-table-footer {
            padding: 16px 24px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #64748b;
            font-size: 0.875rem;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        @media (max-width: 1200px) {
            .users-stats {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .users-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .users-table-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .users-table-footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }

        @media (max-width: 576px) {
            .users-stats {
                grid-template-columns: 1fr;
            }
            
            .users-stat-card {
                padding: 20px;
            }
        }
        .dataTables_wrapper {
            margin-top: 20px;
        }

        .dataTables_length select,
        .dataTables_filter input {
            padding: 8px;
            border-radius: 8px;
            border: 2px solid #e2e8f0;
            font-size: 14px;
        }

        .dataTables_info {
            color: #64748b;
            font-size: 0.875rem;
        }

        .dataTables_paginate .paginate_button {
            padding: 8px 16px;
            margin: 0 2px;
            border-radius: 8px;
            border: 2px solid #e2e8f0;
            background: white;
            color: #3b82f6;
            cursor: pointer;
            transition: all 0.3s;
        }

        .dataTables_paginate .paginate_button:hover {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        .dataTables_paginate .paginate_button.current {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
    </style>
</head>
<body class="dashboard-page">

   
    <div class="dashboard-container">
        
        <header class="dashboard-header">
            <button class="dashboard-menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="d-flex align-items-center gap-3">
                <h1 class="dashboard-title mb-0">Gestion des Utilisateurs</h1>
                <div class="dashboard-subtitle">Tableau de bord d'administration</div>
            </div>
            <div class="dashboard-user-info">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                        <div class="dashboard-avatar">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div class="dashboard-user-details ms-2">
                            <div class="dashboard-user-name">Admin</div>
                            <div class="dashboard-user-role">Administrateur</div>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="../frontoffice/auth/profile.php"><i class="fas fa-user me-2"></i> Mon profil</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Paramètres</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="../../../controllers/logout.php" 
                               onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">
                                <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <aside class="dashboard-sidebar" id="sidebar">
            <div class="dashboard-logo">
                <a href="../home/index.php" class="text-white text-decoration-none">
                    <img src="../assets/img/logo.png" alt="logo" style="height: 40px;">
                    <span class="dashboard-logo-text">Medsense Medical</span>
                </a>
            </div>
            
            <nav class="dashboard-nav">
                <div class="dashboard-nav-section">
                    <div class="dashboard-nav-title">Tableau de Bord</div>
                    <a class="dashboard-nav-item" href="admin-dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                
                <div class="dashboard-nav-section">
                    <div class="dashboard-nav-title">Gestion Médicale</div>
                    <div class="dashboard-nav-item with-submenu">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div>
                                <i class="fas fa-calendar-check"></i>
                                <span>Rendez-vous</span>
                            </div>
                            <i class="fas fa-chevron-down submenu-toggle"></i>
                        </div>
                        <div class="dashboard-submenu">
                            <a class="dashboard-submenu-item" href="admin-appointments.php">
                                <i class="fas fa-list"></i>
                                <span>Tous les rendez-vous</span>
                            </a>
                            <a class="dashboard-submenu-item" href="admin-patient-appointments.php">
                                <i class="fas fa-user-injured"></i>
                                <span>Rendez-vous patients</span>
                            </a>
                            <a class="dashboard-submenu-item" href="admin-new-appointment.php">
                                <i class="fas fa-plus-circle"></i>
                                <span>Nouveau rendez-vous</span>
                            </a>
                            <a class="dashboard-submenu-item" href="admin-calendar.php">
                                <i class="fas fa-calendar"></i>
                                <span>Calendrier</span>
                            </a>
                        </div>
                    </div>
  
                    <a class="dashboard-nav-item" href="admin-patients.php">
                        <i class="fas fa-user-injured"></i>
                        <span>Patients</span>
                    </a>
      
                    <div class="dashboard-nav-item with-submenu">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div>
                                <i class="fas fa-user-md"></i>
                                <span>Médecins</span>
                            </div>
                            <i class="fas fa-chevron-down submenu-toggle"></i>
                        </div>
                        <div class="dashboard-submenu">
                            <a class="dashboard-submenu-item" href="admin-doctors.php">
                                <i class="fas fa-list"></i>
                                <span>Tous les médecins</span>
                            </a>
                            <a class="dashboard-submenu-item" href="admin-doctor-availability.php">
                                <i class="fas fa-clock"></i>
                                <span>Disponibilité</span>
                            </a>
                            <a class="dashboard-submenu-item" href="admin-doctor-complaints.php">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Réclamations</span>
                            </a>
                        </div>
                    </div>

                    <div class="dashboard-nav-item with-submenu active">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div>
                                <i class="fas fa-users"></i>
                                <span>Utilisateurs</span>
                            </div>
                            <i class="fas fa-chevron-down submenu-toggle open"></i>
                        </div>
                        <div class="dashboard-submenu" style="display: block;">
                            <a class="dashboard-submenu-item active" href="admin-users.php">
                                <i class="fas fa-list"></i>
                                <span>Tous les utilisateurs</span>
                            </a>
                            <a class="dashboard-submenu-item" href="admin-create-user.php">
                                <i class="fas fa-user-plus"></i>
                                <span>Nouvel utilisateur</span>
                            </a>
                        </div>
                    </div>
           
                    <a class="dashboard-nav-item" href="admin-complaints.php">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Réclamations</span>
                    </a>
            
                
                </div>
                
                <div class="dashboard-nav-section">
                    <div class="dashboard-nav-title">Gestion du Blog</div>
                 
                    <div class="dashboard-nav-item with-submenu">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div>
                                <i class="fas fa-blog"></i>
                                <span>Blog</span>
                            </div>
                            <i class="fas fa-chevron-down submenu-toggle"></i>
                        </div>
                        <div class="dashboard-submenu">
                            <a class="dashboard-submenu-item" href="admin-blog-categories.php">
                                <i class="fas fa-tags"></i>
                                <span>Catégories</span>
                            </a>
                            <a class="dashboard-submenu-item" href="admin-blog-articles.php">
                                <i class="fas fa-file-alt"></i>
                                <span>Articles</span>
                            </a>
                            <a class="dashboard-submenu-item" href="admin-blog-comments.php">
                                <i class="fas fa-comments"></i>
                                <span>Commentaires</span>
                            </a>
                            <a class="dashboard-submenu-item" href="admin-blog-activity.php">
                                <i class="fas fa-chart-line"></i>
                                <span>Activité du blog</span>
                            </a>
                        </div>
                    </div>
                </div>
                
               
                  
                 
                
                
                <div class="dashboard-nav-section">
                    <div class="dashboard-nav-title">Rapports</div>
                    
                    <div class="dashboard-nav-item with-submenu">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div>
                                <i class="fas fa-chart-bar"></i>
                                <span>Rapports</span>
                            </div>
                            <i class="fas fa-chevron-down submenu-toggle"></i>
                        </div>
                        <div class="dashboard-submenu">
                            <a class="dashboard-submenu-item" href="admin-reports-statistics.php">
                                <i class="fas fa-chart-pie"></i>
                                <span>Statistiques</span>
                            </a>
                     
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-nav-section mt-auto">
                    <a class="dashboard-nav-item logout" href="../../../controllers/logout.php" 
                       onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Déconnexion</span>
                    </a>
                </div>
            </nav>
        </aside>

        <main class="dashboard-main">
            <div class="users-container">
                <?php if ($success_message): ?>
                    <div class="users-alert alert-success animate-fade-in-up">
                        <i class="fas fa-check-circle"></i>
                        <div><?= htmlspecialchars($success_message) ?></div>
                        <button type="button" class="dashboard-alert-close">&times;</button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="users-alert alert-danger animate-fade-in-up">
                        <i class="fas fa-exclamation-circle"></i>
                        <div><?= htmlspecialchars($error_message) ?></div>
                        <button type="button" class="dashboard-alert-close">&times;</button>
                    </div>
                <?php endif; ?>
                <div class="users-stats animate-fade-in-up">
                    <div class="users-stat-card primary">
                        <div class="users-stat-icon primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <div class="users-stat-value"><?= count($allUsers) ?></div>
                            <div class="users-stat-label">Total Utilisateurs</div>
                        </div>
                    </div>
                    <div class="users-stat-card success">
                        <div class="users-stat-icon success">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div>
                            <div class="users-stat-value">
                                <?= count(array_filter($allUsers, function($user) { return $user['statut'] === 'actif'; })) ?>
                            </div>
                            <div class="users-stat-label">Utilisateurs Actifs</div>
                        </div>
                    </div>
                    <div class="users-stat-card warning">
                        <div class="users-stat-icon warning">
                            <i class="fas fa-user-slash"></i>
                        </div>
                        <div>
                            <div class="users-stat-value">
                                <?= count(array_filter($allUsers, function($user) { return $user['statut'] === 'inactif'; })) ?>
                            </div>
                            <div class="users-stat-label">Utilisateurs Inactifs</div>
                        </div>
                    </div>
                    <div class="users-stat-card danger">
                        <div class="users-stat-icon danger">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div>
                            <div class="users-stat-value">
                                <?= count(array_filter($allUsers, function($user) { return $user['role'] === 'admin'; })) ?>
                            </div>
                            <div class="users-stat-label">Administrateurs</div>
                        </div>
                    </div>
                </div>

                <div class="users-filter-form animate-fade-in-up">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="mb-0">
                            <i class="fas fa-users me-2"></i>Gestion des Utilisateurs
                        </h3>
                        <div class="d-flex gap-2">
                            <a href="admin-export-excel.php?<?= http_build_query([
                                'search' => $search,
                                'role' => $role_filter,
                                'statut' => $statut_filter
                            ]) ?>" class="dashboard-btn btn-success">
                                <i class="fas fa-file-excel me-1"></i> Exporter Excel
                            </a>
                            <a href="admin-create-user.php" class="dashboard-btn btn-primary">
                                <i class="fas fa-user-plus me-1"></i> Nouvel Utilisateur
                            </a>
                        </div>
                    </div>
                    
                    <form method="GET" action="" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Recherche</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?= htmlspecialchars($search) ?>" placeholder="Nom, prénom ou email...">
                            </div>
                            <div class="col-md-3">
                                <label for="role" class="form-label">Rôle</label>
                                <select class="form-select" id="role" name="role">
                                    <option value="">Tous les rôles</option>
                                    <option value="user" <?= $role_filter === 'user' ? 'selected' : '' ?>>Utilisateur</option>
                                    <option value="admin" <?= $role_filter === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                                    <option value="moderator" <?= $role_filter === 'moderator' ? 'selected' : '' ?>>Modérateur</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="statut" class="form-label">Statut</label>
                                <select class="form-select" id="statut" name="statut">
                                    <option value="">Tous les statuts</option>
                                    <option value="actif" <?= $statut_filter === 'actif' ? 'selected' : '' ?>>Actif</option>
                                    <option value="inactif" <?= $statut_filter === 'inactif' ? 'selected' : '' ?>>Inactif</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="d-flex gap-2 w-100">
                                    <button type="submit" class="dashboard-btn btn-primary flex-grow-1">
                                        <i class="fas fa-search me-1"></i> Appliquer
                                    </button>
                                    <?php if ($search || $role_filter || $statut_filter): ?>
                                        <a href="admin-users.php" class="dashboard-btn btn-outline" title="Réinitialiser">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php if ($search || $role_filter || $statut_filter): ?>
                            <div class="mt-3 text-muted">
                                <small>
                                    <i class="fas fa-filter"></i> Filtres actifs : 
                                    <?php 
                                    $filters = [];
                                    if ($search) $filters[] = "Recherche: \"$search\"";
                                    if ($role_filter) $filters[] = "Rôle: " . ucfirst($role_filter);
                                    if ($statut_filter) $filters[] = "Statut: " . ucfirst($statut_filter);
                                    echo implode(' • ', $filters);
                                    ?>
                                </small>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="users-table-container animate-fade-in-up">
                    <div class="users-table-header">
                        <h3 class="users-table-title">
                            <i class="fas fa-list me-2"></i>Liste des Utilisateurs
                            <span class="users-table-badge"><?= count($users) ?></span>
                        </h3>
                        <div class="d-flex align-items-center gap-2">
                            <small class="text-muted">Total: <?= count($allUsers) ?> utilisateur(s)</small>
                            <?php if ($search || $role_filter || $statut_filter): ?>
                                <a href="admin-users.php" class="dashboard-btn btn-outline btn-sm">
                                    <i class="fas fa-redo me-1"></i> Tout afficher
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="users-table-content">
                        <?php if (empty($users)): ?>
                            <div class="dashboard-empty">
                                <i class="fas fa-users fa-3x mb-3"></i>
                                <h5>Aucun utilisateur trouvé</h5>
                                <?php if ($search || $role_filter || $statut_filter): ?>
                                    <p>Essayez de modifier vos critères de recherche</p>
                                    <a href="admin-users.php" class="dashboard-btn btn-primary mt-2">
                                        <i class="fas fa-redo me-1"></i> Réinitialiser les filtres
                                    </a>
                                <?php else: ?>
                                    <p>Commencez par ajouter un nouvel utilisateur</p>
                                    <a href="admin-create-user.php" class="dashboard-btn btn-success mt-2">
                                        <i class="fas fa-user-plus me-1"></i> Ajouter un utilisateur
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="dashboard-table-responsive">
                                <table class="dashboard-table" id="usersTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Utilisateur</th>
                                            <th>Email</th>
                                            <th>Rôle</th>
                                            <th>Statut</th>
                                            <th>Date d'inscription</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): 
                                            $user_id = $user['id_utilisateur'] ?? $user['id'];
                                            $is_current_user = $user_id == $_SESSION['user_id'];
                                            $initials = strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1));
                                        ?>
                                        <tr>
                                            <td><strong>#<?= $user_id ?></strong></td>
                                            <td>
                                                <div class="users-user-info">
                                                    <div class="users-user-avatar">
                                                        <?= $initials ?>
                                                    </div>
                                                    <div>
                                                        <div class="users-user-name"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="mailto:<?= htmlspecialchars($user['email']) ?>" class="users-user-email">
                                                    <?= htmlspecialchars($user['email']) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="dashboard-badge role-<?= $user['role'] ?>">
                                                    <?= ucfirst($user['role']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="users-status-badge <?= $user['statut'] ?>">
                                                    <?= ucfirst($user['statut']) ?>
                                                </span>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($user['date_inscription'])) ?></td>
                                            <td>
                                                <div class="dashboard-actions">
                                                    <a href="admin-edit.php?id=<?= $user_id ?>" 
                                                       class="dashboard-btn btn-primary btn-sm" 
                                                       title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <?php if (!$is_current_user): ?>
                                                        <?php if ($user['statut'] === 'actif'): ?>
                                                            <form method="POST" action="admin-deactivate.php" 
                                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir désactiver cet utilisateur ?');"
                                                                  style="display: inline;">
                                                                <input type="hidden" name="user_id" value="<?= $user_id ?>">
                                                                <button type="submit" class="dashboard-btn btn-warning btn-sm" title="Désactiver">
                                                                    <i class="fas fa-user-slash"></i>
                                                                </button>
                                                            </form>
                                                        <?php else: ?>
                                                            <form method="POST" action="admin-activate.php" 
                                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir activer cet utilisateur ?');"
                                                                  style="display: inline;">
                                                                <input type="hidden" name="user_id" value="<?= $user_id ?>">
                                                                <button type="submit" class="dashboard-btn btn-success btn-sm" title="Activer">
                                                                    <i class="fas fa-user-check"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <button class="dashboard-btn btn-outline btn-sm" disabled title="Vous ne pouvez pas modifier votre propre statut">
                                                            <i class="fas fa-user-lock"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($users)): ?>
                        <div class="users-table-footer">
                            <span>Affichage de <?= count($users) ?> utilisateur(s) sur <?= count($allUsers) ?></span>
                            <small>Dernière mise à jour : <?= date('H:i:s') ?></small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/jquery-2.2.4.min.js"></script>
    <script src="../assets/js/popper.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>

    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
       
        document.getElementById('menuToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        });

        $(document).ready(function() {
            $('#usersTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json'
                },
                pageLength: 25,
                responsive: true,
                order: [[5, 'desc']], 
                columnDefs: [
                    {
                        targets: [6], 
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        });

        setTimeout(() => {
            const alerts = document.querySelectorAll('.users-alert');
            alerts.forEach(alert => {
                alert.style.display = 'none';
            });
        }, 5000);

        document.querySelectorAll('.with-submenu').forEach(item => {
            const toggle = item.querySelector('.submenu-toggle');
            const submenu = item.querySelector('.dashboard-submenu');
            
            if (toggle && submenu) {
                toggle.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                  
                    document.querySelectorAll('.with-submenu').forEach(otherItem => {
                        if (otherItem !== item) {
                            otherItem.querySelector('.dashboard-submenu').style.display = 'none';
                            otherItem.querySelector('.submenu-toggle').classList.remove('open');
                        }
                    });
             
                    if (submenu.style.display === 'block') {
                        submenu.style.display = 'none';
                        toggle.classList.remove('open');
                    } else {
                        submenu.style.display = 'block';
                        toggle.classList.add('open');
                    }
                });
            }
        });

        document.querySelectorAll('.dashboard-alert-close').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.users-alert').style.display = 'none';
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.users-stat-card, .users-filter-form, .users-table-container');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
                
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 300 + (index * 100));
            });
        });
     
        document.querySelectorAll('a[href*="logout"]').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>