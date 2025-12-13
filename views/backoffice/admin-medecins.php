<?php

session_start([
    'cookie_secure' => false,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict'
]);

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../frontoffice/auth/sign-in.php');
    exit;
}

require_once __DIR__ . '/../../controllers/AdminController.php';

$adminController = new AdminController();
$action = '';
$doctorId = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING) ?? '';
    $doctorId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING) ?? '';
    $doctorId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $_SESSION['error_message'] = "Token de sécurité invalide.";
        header('Location: admin-medecins.php');
        exit;
    }
    
    switch ($action) {
        case 'approve':
        case 'reject':
        case 'suspend':
            if ($doctorId) {
                $reason = filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_STRING) ?? '';
                $result = $adminController->handleApprovalRequest([
                    'action' => $action,
                    'doctor_id' => $doctorId,
                    'reason' => $reason
                ]);
                
                if ($result['success']) {
                    $_SESSION['success_message'] = $result['message'];
                } else {
                    $_SESSION['error_message'] = $result['message'];
                }
                header('Location: admin-medecins.php');
                exit;
            }
            break;
            
        case 'verify_diploma':
            if ($doctorId) {
                $diploma_status = filter_input(INPUT_POST, 'diploma_status', FILTER_SANITIZE_STRING);
                $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING) ?? '';
                
                if ($diploma_status) {
                    $result = $adminController->verifyDiploma(
                        $doctorId, 
                        $diploma_status,
                        $comment
                    );
                    if ($result['success']) {
                        $_SESSION['success_message'] = $result['message'];
                    } else {
                        $_SESSION['error_message'] = $result['message'];
                    }
                }
                header('Location: admin-medecins.php');
                exit;
            }
            break;
    }
}

function handleResultMessage($result) {
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
    } else {
        $_SESSION['error_message'] = $result['message'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    switch ($action) {
        case 'delete':
            if ($doctorId) {
                $result = $adminController->deactivateUser($doctorId);
                handleResultMessage($result);
                header('Location: admin-medecins.php');
                exit;
            }
            break;
            
        case 'activate':
            if ($doctorId) {
                $result = $adminController->activateUser($doctorId);
                handleResultMessage($result);
                header('Location: admin-medecins.php');
                exit;
            }
            break;
            
        case 'permanent_delete':
            if ($doctorId) {
                $result = $adminController->permanentlyDeleteUser($doctorId);
                handleResultMessage($result);
                header('Location: admin-medecins.php');
                exit;
            }
            break;
            
        case 'export_excel':
            $adminController->exportDoctorsToExcel();
            exit;
    }
}

$filters = [];
$search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING);
$statut = filter_input(INPUT_GET, 'statut', FILTER_SANITIZE_STRING);

if (!empty($search)) {
    $filters['search'] = $search;
}
if (!empty($statut) && in_array($statut, ['actif', 'inactif', 'en_attente', 'rejeté', 'suspendu'])) {
    $filters['statut'] = $statut;
}

$doctorsResult = $adminController->getAllDoctors($filters);
$doctors = $doctorsResult['doctors'] ?? [];
$totalDoctors = $doctorsResult['count'] ?? 0;

$statsResult = $adminController->getApprovalStats();
$statusStats = $statsResult['status_stats'] ?? [];
$weeklyApproved = $statsResult['weekly_approved'] ?? 0;

$pendingResult = $adminController->getPendingDoctors();
$pendingDoctors = $pendingResult['doctors'] ?? [];
$pendingCount = $pendingResult['count'] ?? 0;
$statutCounts = [
    'actif' => 0,
    'inactif' => 0,
    'en_attente' => 0,
    'rejeté' => 0,
    'suspendu' => 0
];

foreach ($statusStats as $stat) {
    if (isset($statutCounts[$stat['statut']])) {
        $statutCounts[$stat['statut']] = (int)$stat['count'];
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Médecins - Medsense Medical</title>
    
    <link rel="stylesheet" href="../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../../assets/css/themify-icons.css">
    <link rel="stylesheet" href="../../assets/css/flaticon.css">
    <link rel="stylesheet" href="../assets/vendors/fontawesome/css/all.min.css">
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
   
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

        .role-medecin {
            background: rgba(6, 182, 212, 0.1);
            color: #0891b2;
        }

        .role-patient {
            background: rgba(34, 197, 94, 0.1);
            color: #16a34a;
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

        .status-en_attente {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
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

        .doctors-container {
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

       
        .doctors-alert {
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

        .doctors-alert.alert-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
            color: #166534;
            border-left-color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .doctors-alert.alert-danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%);
            color: #991b1b;
            border-left-color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .doctors-alert.alert-warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0.05) 100%);
            color: #92400e;
            border-left-color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .doctors-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 24px 0;
        }

        .doctors-stat-card {
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

        .doctors-stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .doctors-stat-card.primary {
            border-top-color: #3b82f6;
        }

        .doctors-stat-card.success {
            border-top-color: #10b981;
        }

        .doctors-stat-card.warning {
            border-top-color: #f59e0b;
        }

        .doctors-stat-card.danger {
            border-top-color: #ef4444;
        }

        .doctors-stat-card.info {
            border-top-color: #06b6d4;
        }

        .doctors-stat-card.secondary {
            border-top-color: #94a3b8;
        }

        .doctors-stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .doctors-stat-icon.primary {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(59, 130, 246, 0.2) 100%);
            color: #3b82f6;
        }

        .doctors-stat-icon.success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.2) 100%);
            color: #10b981;
        }

        .doctors-stat-icon.warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0.2) 100%);
            color: #f59e0b;
        }

        .doctors-stat-icon.danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.2) 100%);
            color: #ef4444;
        }

        .doctors-stat-icon.info {
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.1) 0%, rgba(6, 182, 212, 0.2) 100%);
            color: #06b6d4;
        }

        .doctors-stat-icon.secondary {
            background: linear-gradient(135deg, rgba(148, 163, 184, 0.1) 0%, rgba(148, 163, 184, 0.2) 100%);
            color: #94a3b8;
        }

        .doctors-stat-value {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 4px;
            color: #1e293b;
            line-height: 1;
        }

        .doctors-stat-label {
            color: #64748b;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 600;
        }

        .doctors-filter-form {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin: 24px 0;
        }

        .doctors-table-container {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin: 24px 0;
        }

        .doctors-table-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .doctors-table-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .doctors-table-badge {
            background: #3b82f6;
            color: white;
            font-size: 0.75rem;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
        }

        .doctors-table-badge.danger {
            background: #ef4444;
        }

        .doctors-table-content {
            padding: 0;
        }

        /* Badges de statut */
        .doctors-status-badge {
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

        .doctors-status-badge.actif {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.2) 100%);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .doctors-status-badge.inactif {
            background: linear-gradient(135deg, rgba(148, 163, 184, 0.1) 0%, rgba(148, 163, 184, 0.2) 100%);
            color: #64748b;
            border: 1px solid rgba(148, 163, 184, 0.3);
        }

        .doctors-status-badge.en_attente {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0.2) 100%);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .doctors-status-badge.rejeté {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.2) 100%);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .doctors-status-badge.suspendu {
            background: linear-gradient(135deg, rgba(148, 163, 184, 0.1) 0%, rgba(148, 163, 184, 0.2) 100%);
            color: #64748b;
            border: 1px solid rgba(148, 163, 184, 0.3);
        }

        /* Actions spécifiques */
        .doctors-actions-column {
            min-width: 200px;
        }

        .doctors-action-btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 1px;
            border-radius: 8px;
            border: none;
            transition: all 0.3s;
        }

        .doctors-action-btn:hover {
            transform: translateY(-2px);
        }

        .doctors-action-btn.success {
            background: #10b981;
            color: white;
        }

        .doctors-action-btn.danger {
            background: #ef4444;
            color: white;
        }

        .doctors-action-btn.warning {
            background: #f59e0b;
            color: white;
        }

        .doctors-action-btn.secondary {
            background: #94a3b8;
            color: white;
        }

        .doctors-action-btn.info {
            background: #3b82f6;
            color: white;
        }

        .doctors-action-btn.outline {
            background: transparent;
            border: 1px solid #d1d5db;
            color: #64748b;
        }

        /* Diplôme actions */
        .diplome-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }

        /* User info dans tableau */
        .doctors-user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .doctors-user-avatar {
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

        .doctors-user-name {
            font-weight: 600;
            color: #1e293b;
        }

        .doctors-user-email {
            font-size: 0.875rem;
            color: #64748b;
        }

        /* Footer du tableau */
        .doctors-table-footer {
            padding: 16px 24px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #64748b;
            font-size: 0.875rem;
        }

        /* Animation fade-in */
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

        /* Responsive pour les stats */
        @media (max-width: 1200px) {
            .doctors-stats {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .doctors-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .doctors-table-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .doctors-table-footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .doctors-actions-column {
                min-width: 150px;
            }
        }

        @media (max-width: 576px) {
            .doctors-stats {
                grid-template-columns: 1fr;
            }
            
            .doctors-stat-card {
                padding: 20px;
            }
        }

        /* Styles DataTables */
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

    <!-- Dashboard Container -->
    <div class="dashboard-container">
        <!-- Header -->
        <header class="dashboard-header">
            <button class="dashboard-menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="d-flex align-items-center gap-3">
                <h1 class="dashboard-title mb-0">Gestion des Médecins</h1>
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

        <!-- Sidebar -->
        <aside class="dashboard-sidebar" id="sidebar">
            <div class="dashboard-logo">
                <a href="../home/index.php" class="text-white text-decoration-none">
                    <img src="../assets/img/logo.png" alt="logo" class="dashboard-logo-img">
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
                    
                    <!-- Rendez-vous -->
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
                    
                    <!-- Patients -->
                    <a class="dashboard-nav-item" href="admin-patients.php">
                        <i class="fas fa-user-injured"></i>
                        <span>Patients</span>
                    </a>
                    
                    <!-- Médecins -->
                    <div class="dashboard-nav-item with-submenu active">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div>
                                <i class="fas fa-user-md"></i>
                                <span>Médecins</span>
                                <?php if ($pendingCount > 0): ?>
                                    <span class="dashboard-badge"><?= $pendingCount ?></span>
                                <?php endif; ?>
                            </div>
                            <i class="fas fa-chevron-down submenu-toggle open"></i>
                        </div>
                        <div class="dashboard-submenu" style="display: block;">
                            <a class="dashboard-submenu-item active" href="admin-medecins.php">
                                <i class="fas fa-list"></i>
                                <span>Tous les médecins</span>
                            </a>
                            <a class="dashboard-submenu-item" href="admin-doctor-availability.php">
                                <i class="fas fa-clock"></i>
                                <span>Disponibilité</span>
                            </a>
                            <a class="dashboard-submenu-item" href="admin-doctor-ratings.php">
                                <i class="fas fa-star"></i>
                                <span>Évaluations</span>
                            </a>
                            <a class="dashboard-submenu-item" href="admin-doctor-complaints.php">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Réclamations</span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Utilisateurs -->
                    <a class="dashboard-nav-item" href="admin-users.php">
                        <i class="fas fa-users"></i>
                        <span>Utilisateurs</span>
                    </a>
                    
                    <!-- Réclamations -->
                    <a class="dashboard-nav-item" href="admin-complaints.php">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Réclamations</span>
                    </a>
                    
                    <!-- Ordonnances -->
                    <a class="dashboard-nav-item" href="admin-prescriptions.php">
                        <i class="fas fa-prescription"></i>
                        <span>Ordonnances</span>
                    </a>
                    
                    <!-- Facturation -->
                    <a class="dashboard-nav-item" href="admin-billing.php">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>Facturation</span>
                    </a>
                </div>
                
                <div class="dashboard-nav-section">
                    <div class="dashboard-nav-title">Gestion du Blog</div>
                    
                    <!-- Blog -->
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
                    <div class="dashboard-nav-title">Avis & Évaluations</div>
                    
                    <!-- Reviews -->
                    <div class="dashboard-nav-item with-submenu">
                        <div class="d-flex align-items-center justify-content-between w-100">
                            <div>
                                <i class="fas fa-star"></i>
                                <span>Reviews & Avis</span>
                            </div>
                            <i class="fas fa-chevron-down submenu-toggle"></i>
                        </div>
                        <div class="dashboard-submenu">
                            <a class="dashboard-submenu-item" href="admin-reviews.php">
                                <i class="fas fa-star-half-alt"></i>
                                <span>Tous les avis</span>
                            </a>
                            <a class="dashboard-submenu-item" href="admin-doctor-reviews.php">
                                <i class="fas fa-user-md"></i>
                                <span>Avis médecins</span>
                            </a>
                            <a class="dashboard-submenu-item" href="admin-patient-reviews.php">
                                <i class="fas fa-user-injured"></i>
                                <span>Avis patients</span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Feedback -->
                    <a class="dashboard-nav-item" href="admin-feedback.php">
                        <i class="fas fa-comment-medical"></i>
                        <span>Feedback</span>
                    </a>
                </div>
                
                <div class="dashboard-nav-section">
                    <div class="dashboard-nav-title">Configuration</div>
                    
                    <!-- Paramètres -->
                    <a class="dashboard-nav-item" href="admin-settings.php">
                        <i class="fas fa-cog"></i>
                        <span>Paramètres</span>
                    </a>
                </div>
                
                <div class="dashboard-nav-section">
                    <div class="dashboard-nav-title">Rapports</div>
                    
                    <!-- Rapports -->
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
                            <a class="dashboard-submenu-item" href="admin-reports-financial.php">
                                <i class="fas fa-money-bill-wave"></i>
                                <span>Financiers</span>
                            </a>
                            <a class="dashboard-submenu-item" href="admin-reports-medical.php">
                                <i class="fas fa-stethoscope"></i>
                                <span>Médicaux</span>
                            </a>
                            <a class="dashboard-submenu-item" href="admin-audit.php">
                                <i class="fas fa-clipboard-list"></i>
                                <span>Audit médical</span>
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

        <!-- Main Content -->
        <main class="dashboard-main">
            <div class="doctors-container">
                <!-- Messages d'alerte -->
                <?php if ($success_message): ?>
                    <div class="doctors-alert alert-success animate-fade-in-up">
                        <i class="fas fa-check-circle"></i>
                        <div><?= htmlspecialchars($success_message) ?></div>
                        <button type="button" class="dashboard-alert-close">&times;</button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="doctors-alert alert-danger animate-fade-in-up">
                        <i class="fas fa-exclamation-circle"></i>
                        <div><?= htmlspecialchars($error_message) ?></div>
                        <button type="button" class="dashboard-alert-close">&times;</button>
                    </div>
                <?php endif; ?>

                <!-- Alertes importantes -->
                <?php if ($pendingCount > 0): ?>
                    <div class="doctors-alert alert-warning animate-fade-in-up">
                        <i class="fas fa-clock"></i>
                        <div>
                            <strong><?= $pendingCount ?> médecin(s)</strong> en attente d'approbation.
                        </div>
                        <button type="button" class="dashboard-alert-close">&times;</button>
                    </div>
                <?php endif; ?>

                <!-- Statistiques principales -->
                <div class="doctors-stats animate-fade-in-up">
                    <div class="doctors-stat-card primary">
                        <div class="doctors-stat-icon primary">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div>
                            <div class="doctors-stat-value"><?= $totalDoctors ?></div>
                            <div class="doctors-stat-label">Total Médecins</div>
                        </div>
                    </div>
                    <div class="doctors-stat-card success">
                        <div class="doctors-stat-icon success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <div class="doctors-stat-value"><?= $statutCounts['actif'] ?></div>
                            <div class="doctors-stat-label">Actifs</div>
                        </div>
                    </div>
                    <div class="doctors-stat-card warning">
                        <div class="doctors-stat-icon warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <div class="doctors-stat-value"><?= $pendingCount ?></div>
                            <div class="doctors-stat-label">En Attente</div>
                        </div>
                    </div>
                    <div class="doctors-stat-card danger">
                        <div class="doctors-stat-icon danger">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div>
                            <div class="doctors-stat-value"><?= $statutCounts['rejeté'] ?></div>
                            <div class="doctors-stat-label">Rejetés</div>
                        </div>
                    </div>
                    <div class="doctors-stat-card secondary">
                        <div class="doctors-stat-icon secondary">
                            <i class="fas fa-pause-circle"></i>
                        </div>
                        <div>
                            <div class="doctors-stat-value"><?= $statutCounts['suspendu'] ?></div>
                            <div class="doctors-stat-label">Suspendus</div>
                        </div>
                    </div>
                    <div class="doctors-stat-card info">
                        <div class="doctors-stat-icon info">
                            <i class="fas fa-calendar-week"></i>
                        </div>
                        <div>
                            <div class="doctors-stat-value"><?= $weeklyApproved ?></div>
                            <div class="doctors-stat-label">Cette semaine</div>
                        </div>
                    </div>
                </div>

                <!-- Filtres -->
                <div class="doctors-filter-form animate-fade-in-up">
                    <form method="GET" class="row g-3" id="filterForm">
                        <div class="col-md-6">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Rechercher (nom, prénom, email, spécialité)" 
                                   value="<?= htmlspecialchars($search ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <select name="statut" class="form-select">
                                <option value="">Tous les statuts</option>
                                <option value="actif" <?= ($statut ?? '') == 'actif' ? 'selected' : '' ?>>Actif</option>
                                <option value="inactif" <?= ($statut ?? '') == 'inactif' ? 'selected' : '' ?>>Inactif</option>
                                <option value="en_attente" <?= ($statut ?? '') == 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                <option value="rejeté" <?= ($statut ?? '') == 'rejeté' ? 'selected' : '' ?>>Rejeté</option>
                                <option value="suspendu" <?= ($statut ?? '') == 'suspendu' ? 'selected' : '' ?>>Suspendu</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="dashboard-btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i> Filtrer
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Liste des médecins -->
                <div class="doctors-table-container animate-fade-in-up">
                    <div class="doctors-table-header">
                        <h3 class="doctors-table-title">
                            <i class="fas fa-list me-2"></i>Liste des médecins
                            <span class="doctors-table-badge"><?= $totalDoctors ?></span>
                        </h3>
                        <div class="d-flex align-items-center gap-2">
                            <?php if (!empty($search) || !empty($statut)): ?>
                                <a href="admin-medecins.php" class="dashboard-btn btn-outline btn-sm">
                                    <i class="fas fa-times-circle me-1"></i> Réinitialiser
                                </a>
                            <?php endif; ?>
                            <button type="button" class="dashboard-btn btn-success btn-sm" onclick="exportToExcel()" id="exportBtn">
                                <i class="fas fa-file-excel me-1"></i> Exporter Excel
                                <span class="spinner-border spinner-border-sm d-none" role="status" id="exportSpinner"></span>
                            </button>
                        </div>
                    </div>
                    <div class="doctors-table-content">
                        <?php if (empty($doctors)): ?>
                            <div class="dashboard-empty">
                                <i class="fas fa-user-md fa-3x mb-3"></i>
                                <h5>Aucun médecin trouvé</h5>
                                <?php if (!empty($search) || !empty($statut)): ?>
                                    <p>Essayez de modifier vos critères de recherche</p>
                                <?php else: ?>
                                    <p>Aucun médecin n'est inscrit pour le moment</p>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="dashboard-table-responsive">
                                <table class="dashboard-table" id="doctorsTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nom</th>
                                            <th>Prénom</th>
                                            <th>Email</th>
                                            <th>Spécialité</th>
                                            <th>Date Inscription</th>
                                            <th>Diplôme</th>
                                            <th>Statut</th>
                                            <th class="doctors-actions-column">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($doctors as $doctor): ?>
                                            <?php
                                            $initials = strtoupper(
                                                substr($doctor['prenom'] ?? '', 0, 1) . 
                                                substr($doctor['nom'] ?? '', 0, 1)
                                            );
                                            $statusClass = match($doctor['statut'] ?? '') {
                                                'actif' => 'actif',
                                                'inactif' => 'inactif',
                                                'en_attente' => 'en_attente',
                                                'rejeté' => 'rejeté',
                                                'suspendu' => 'suspendu',
                                                default => 'inactif'
                                            };
                                            $diplomeStatutClass = match($doctor['diplome_statut'] ?? '') {
                                                'validé' => 'success',
                                                'en attente' => 'warning',
                                                'rejeté' => 'danger',
                                                default => 'secondary'
                                            };
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($doctor['id_utilisateur']) ?></td>
                                                <td>
                                                    <div class="doctors-user-info">
                                                        <div class="doctors-user-avatar">
                                                            <?= htmlspecialchars($initials) ?>
                                                        </div>
                                                        <?= htmlspecialchars($doctor['nom']) ?>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars($doctor['prenom']) ?></td>
                                                <td>
                                                    <a href="mailto:<?= htmlspecialchars($doctor['email']) ?>" class="dashboard-user-email">
                                                        <?= htmlspecialchars($doctor['email']) ?>
                                                    </a>
                                                </td>
                                                <td><?= htmlspecialchars($doctor['specialite'] ?? 'Non spécifiée') ?></td>
                                                <td>
                                                    <?= date('d/m/Y', strtotime($doctor['date_inscription'])) ?>
                                                </td>
                                                <td>
                                                    <div class="diplome-actions">
                                                        <?php if (!empty($doctor['diplome_path'])): ?>
                                                            <?php
                                                            // Construction du chemin correct pour le diplôme
                                                            $diplome_path = $doctor['diplome_path'];
                                                            $full_diplome_path = __DIR__ . '/../../' . $diplome_path;
                                                            ?>
                                                            <a href="../<?= htmlspecialchars($diplome_path) ?>" 
                                                               target="_blank" 
                                                               class="dashboard-badge role-info text-decoration-none" 
                                                               title="Voir le diplôme"
                                                               onclick="return checkDiplomaExists(<?= $doctor['id_utilisateur'] ?>, '<?= htmlspecialchars($diplome_path) ?>')">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <span class="dashboard-badge role-<?= $diplomeStatutClass ?>">
                                                                <?= $doctor['diplome_statut'] ?? 'Non vérifié' ?>
                                                            </span>
                                                            <button type="button" 
                                                                    class="doctors-action-btn info"
                                                                    onclick="showVerifyDiplomaModal(<?= $doctor['id_utilisateur'] ?>)"
                                                                    title="Vérifier le diplôme">
                                                                <i class="fas fa-check-circle"></i>
                                                            </button>
                                                        <?php else: ?>
                                                            <span class="dashboard-badge role-secondary">Non fourni</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="doctors-status-badge <?= $statusClass ?>">
                                                        <?= ucfirst($doctor['statut']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="dashboard-actions">
                                                        <!-- Actions selon le statut -->
                                                        <?php if ($doctor['statut'] === 'en_attente'): ?>
                                                            <button type="button" 
                                                                    class="doctors-action-btn success" 
                                                                    onclick="approveDoctor(<?= $doctor['id_utilisateur'] ?>)" 
                                                                    title="Approuver">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                            <button type="button" 
                                                                    class="doctors-action-btn danger" 
                                                                    onclick="rejectDoctor(<?= $doctor['id_utilisateur'] ?>)" 
                                                                    title="Rejeter">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($doctor['statut'] === 'actif'): ?>
                                                            <button type="button" 
                                                                    class="doctors-action-btn warning" 
                                                                    onclick="suspendDoctor(<?= $doctor['id_utilisateur'] ?>)" 
                                                                    title="Suspendre">
                                                                <i class="fas fa-pause"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (in_array($doctor['statut'], ['inactif', 'suspendu'])): ?>
                                                            <button type="button" 
                                                                    class="doctors-action-btn success" 
                                                                    onclick="activateUser(<?= $doctor['id_utilisateur'] ?>)" 
                                                                    title="Activer/Réactiver">
                                                                <i class="fas fa-power-off"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($doctor['statut'] !== 'inactif'): ?>
                                                            <button type="button" 
                                                                    class="doctors-action-btn secondary" 
                                                                    onclick="deactivateUser(<?= $doctor['id_utilisateur'] ?>)" 
                                                                    title="Désactiver">
                                                                <i class="fas fa-ban"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        
                                                        <button type="button" 
                                                                class="doctors-action-btn outline" 
                                                                onclick="deleteUser(<?= $doctor['id_utilisateur'] ?>)" 
                                                                title="Supprimer définitivement">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($doctors)): ?>
                        <div class="doctors-table-footer">
                            <span>Affichage de <?= count($doctors) ?> médecin(s) sur <?= $totalDoctors ?></span>
                            <small>Dernière mise à jour : <?= date('H:i:s') ?></small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal pour la raison du rejet/suspension -->
    <div class="modal fade" id="reasonModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="actionForm" method="POST" action="admin-medecins.php">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" id="actionType" name="action">
                    <input type="hidden" id="doctorId" name="id">
                    
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Raison</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="reason" class="form-label">Raison (optionnel)</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" 
                                      placeholder="Expliquez la raison de cette action..."></textarea>
                            <div class="form-text">Cette raison sera envoyée au médecin par email.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Confirmer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal pour vérification de diplôme -->
    <div class="modal fade" id="verifyDiplomaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="verifyDiplomaForm" method="POST" action="admin-medecins.php">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="action" value="verify_diploma">
                    <input type="hidden" id="diplomaDoctorId" name="id">
                    
                    <div class="modal-header">
                        <h5 class="modal-title">Vérification du diplôme</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="diploma_status" class="form-label">Statut du diplôme</label>
                            <select class="form-select" id="diploma_status" name="diploma_status" required>
                                <option value="">Sélectionner un statut</option>
                                <option value="validé">Validé</option>
                                <option value="rejeté">Rejeté</option>
                                <option value="en attente">En attente</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="comment" class="form-label">Commentaire (optionnel)</label>
                            <textarea class="form-control" id="comment" name="comment" rows="3" 
                                      placeholder="Commentaire sur la vérification..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../assets/js/jquery-2.2.4.min.js"></script>
    <script src="../assets/js/popper.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        // Mobile menu toggle
        document.getElementById('menuToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        });

        // Initialisation de DataTables
        $(document).ready(function() {
            $('#doctorsTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json'
                },
                pageLength: 25,
                responsive: true,
                order: [[5, 'desc']], // Tri par date d'inscription décroissante
                columnDefs: [
                    {
                        targets: [8], // Colonne actions
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        });

        // Fonctions pour les actions
        function approveDoctor(id) {
            console.log("Tentative d'approbation pour l'ID:", id);
            console.log("Token CSRF:", '<?= $_SESSION['csrf_token'] ?>');
            
            if (confirm("Êtes-vous sûr de vouloir approuver ce médecin ?")) {
                // Créer un formulaire temporaire
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'admin-medecins.php';
                form.style.display = 'none';
                
                // Ajouter les champs
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = 'csrf_token';
                csrf.value = '<?= $_SESSION['csrf_token'] ?>';
                form.appendChild(csrf);
                
                const action = document.createElement('input');
                action.type = 'hidden';
                action.name = 'action';
                action.value = 'approve';
                form.appendChild(action);
                
                const doctorId = document.createElement('input');
                doctorId.type = 'hidden';
                doctorId.name = 'id';
                doctorId.value = id;
                form.appendChild(doctorId);
                
                // Ajouter au body et soumettre
                document.body.appendChild(form);
                console.log("Soumission du formulaire pour approuver le médecin ID:", id);
                form.submit();
            }
        }

        function rejectDoctor(id) {
            document.getElementById('actionForm').action = 'admin-medecins.php';
            document.getElementById('actionType').value = 'reject';
            document.getElementById('doctorId').value = id;
            document.getElementById('modalTitle').textContent = 'Raison du rejet';
            new bootstrap.Modal(document.getElementById('reasonModal')).show();
        }

        function suspendDoctor(id) {
            document.getElementById('actionForm').action = 'admin-medecins.php';
            document.getElementById('actionType').value = 'suspend';
            document.getElementById('doctorId').value = id;
            document.getElementById('modalTitle').textContent = 'Raison de la suspension';
            new bootstrap.Modal(document.getElementById('reasonModal')).show();
        }

        function showVerifyDiplomaModal(id) {
            document.getElementById('diplomaDoctorId').value = id;
            new bootstrap.Modal(document.getElementById('verifyDiplomaModal')).show();
        }

        function deactivateUser(id) {
            if (confirm("Êtes-vous sûr de vouloir désactiver ce compte ?")) {
                window.location.href = `?action=delete&id=${id}`;
            }
        }

        function activateUser(id) {
            if (confirm("Êtes-vous sûr de vouloir activer/réactiver ce compte ?")) {
                window.location.href = `?action=activate&id=${id}`;
            }
        }

        function deleteUser(id) {
            if (confirm("Êtes-vous sûr de vouloir supprimer définitivement ce médecin ?\n\nCette action est irréversible !")) {
                window.location.href = `?action=permanent_delete&id=${id}`;
            }
        }

        function exportToExcel() {
            const btn = document.getElementById('exportBtn');
            const spinner = document.getElementById('exportSpinner');
            
            btn.disabled = true;
            spinner.classList.remove('d-none');
            
            setTimeout(() => {
                window.location.href = '?action=export_excel';
            }, 500);
        }

        // Auto-dismiss des alertes après 5 secondes
        setTimeout(() => {
            const alerts = document.querySelectorAll('.doctors-alert');
            alerts.forEach(alert => {
                alert.style.display = 'none';
            });
        }, 5000);

        // Gestion de la soumission du formulaire de diplôme
        document.getElementById('verifyDiplomaForm').addEventListener('submit', function(e) {
            const status = document.getElementById('diploma_status').value;
            if (!status) {
                e.preventDefault();
                alert('Veuillez sélectionner un statut pour le diplôme.');
            }
        });

        // Fonction pour vérifier si le diplôme existe
        function checkDiplomaExists(doctorId, diplomaPath) {
            console.log("Vérification du diplôme pour le médecin ID:", doctorId);
            console.log("Chemin du diplôme:", diplomaPath);
            return true; // Autoriser l'ouverture
        }

        // Gérer les sous-menus
        document.querySelectorAll('.with-submenu').forEach(item => {
            const toggle = item.querySelector('.submenu-toggle');
            const submenu = item.querySelector('.dashboard-submenu');
            
            if (toggle && submenu) {
                toggle.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Fermer les autres sous-menus
                    document.querySelectorAll('.with-submenu').forEach(otherItem => {
                        if (otherItem !== item) {
                            otherItem.querySelector('.dashboard-submenu').style.display = 'none';
                            otherItem.querySelector('.submenu-toggle').classList.remove('open');
                        }
                    });
                    
                    // Toggle le sous-menu actuel
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

        // Fermer les alertes
        document.querySelectorAll('.dashboard-alert-close').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.doctors-alert').style.display = 'none';
            });
        });

        // Animation au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.doctors-stat-card, .doctors-filter-form, .doctors-table-container');
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
        
        // Confirmation de déconnexion
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