<?php
require_once '../../../config/config.php';
require_once '../../../models/Reclamation.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get statistics
$reclamationModel = new Reclamation();
$stats = $reclamationModel->getStatistics();

$pageTitle = "Statistiques - Admin";
?>

<?php include '../../../admin_sidebar.php'; ?>

<div class="admin-content">
    <!-- Header Section -->
    <div class="content-header" style="margin-bottom: 2rem;">
        <h1 style="color: #1e293b; font-size: 2rem; margin-bottom: 0.5rem;">
            <i class="fas fa-chart-bar"></i> Statistiques des Réclamations
        </h1>
        <p style="color: #64748b; font-size: 1.1rem;">Analyse complète des données utilisateurs et réclamations</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Total Réclamations -->
        <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="margin: 0; opacity: 0.9; font-size: 0.9rem;">Total Réclamations</p>
                    <h2 style="margin: 0.5rem 0 0 0; font-size: 2.5rem; font-weight: 700;"><?= number_format($stats['total_reclamations'] ?? 0) ?></h2>
                </div>
                <div style="font-size: 3rem; opacity: 0.3;">
                    <i class="fas fa-file-alt"></i>
                </div>
            </div>
        </div>

        <!-- Réclamations 24h -->
        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="margin: 0; opacity: 0.9; font-size: 0.9rem;">Dernières 24h</p>
                    <h2 style="margin: 0.5rem 0 0 0; font-size: 2.5rem; font-weight: 700;"><?= number_format($stats['recent_24h'] ?? 0) ?></h2>
                </div>
                <div style="font-size: 3rem; opacity: 0.3;">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>

        <!-- Cette Semaine -->
        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="margin: 0; opacity: 0.9; font-size: 0.9rem;">Cette Semaine</p>
                    <h2 style="margin: 0.5rem 0 0 0; font-size: 2.5rem; font-weight: 700;"><?= number_format($stats['this_week'] ?? 0) ?></h2>
                </div>
                <div style="font-size: 3rem; opacity: 0.3;">
                    <i class="fas fa-calendar-week"></i>
                </div>
            </div>
        </div>

        <!-- Ce Mois -->
        <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="margin: 0; opacity: 0.9; font-size: 0.9rem;">Ce Mois</p>
                    <h2 style="margin: 0.5rem 0 0 0; font-size: 2.5rem; font-weight: 700;"><?= number_format($stats['this_month'] ?? 0) ?></h2>
                </div>
                <div style="font-size: 3rem; opacity: 0.3;">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
        <!-- Réclamations par Type -->
        <div class="chart-card" style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 1.5rem 0; color: #1e293b; font-size: 1.3rem;">
                <i class="fas fa-chart-pie"></i> Réclamations par Type
            </h3>
            <div style="margin-bottom: 1rem;">
                <?php 
                $totalByType = array_sum($stats['by_type'] ?? []);
                foreach ($stats['by_type'] ?? [] as $type => $count): 
                    $percentage = $totalByType > 0 ? round(($count / $totalByType) * 100, 1) : 0;
                    $color = $type === 'urgence' ? '#ef4444' : '#3b82f6';
                ?>
                <div style="margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="font-weight: 600; color: #374151; text-transform: capitalize;">
                            <?= $type === 'urgence' ? '🚨 Urgence' : '📄 Normal' ?>
                        </span>
                        <span style="font-weight: 700; color: #1e293b;">
                            <?= number_format($count) ?> (<?= $percentage ?>%)
                        </span>
                    </div>
                    <div style="background: #e5e7eb; border-radius: 8px; height: 12px; overflow: hidden;">
                        <div style="background: <?= $color ?>; height: 100%; width: <?= $percentage ?>%; transition: width 0.5s ease;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Réclamations par Statut -->
        <div class="chart-card" style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 1.5rem 0; color: #1e293b; font-size: 1.3rem;">
                <i class="fas fa-tasks"></i> Réclamations par Statut
            </h3>
            <div style="margin-bottom: 1rem;">
                <?php 
                $totalByStatus = array_sum($stats['by_status'] ?? []);
                $statusColors = [
                    'ouvert' => '#f59e0b',
                    'en cours' => '#3b82f6',
                    'fermé' => '#10b981'
                ];
                $statusIcons = [
                    'ouvert' => 'fa-clock',
                    'en cours' => 'fa-spinner',
                    'fermé' => 'fa-check-circle'
                ];
                foreach ($stats['by_status'] ?? [] as $statut => $count): 
                    $percentage = $totalByStatus > 0 ? round(($count / $totalByStatus) * 100, 1) : 0;
                    $color = $statusColors[$statut] ?? '#6b7280';
                    $icon = $statusIcons[$statut] ?? 'fa-circle';
                ?>
                <div style="margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="font-weight: 600; color: #374151;">
                            <i class="fas <?= $icon ?>"></i> <?= ucfirst($statut) ?>
                        </span>
                        <span style="font-weight: 700; color: #1e293b;">
                            <?= number_format($count) ?> (<?= $percentage ?>%)
                        </span>
                    </div>
                    <div style="background: #e5e7eb; border-radius: 8px; height: 12px; overflow: hidden;">
                        <div style="background: <?= $color ?>; height: 100%; width: <?= $percentage ?>%; transition: width 0.5s ease;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Réclamations par Mois -->
    <div class="chart-card" style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <h3 style="margin: 0 0 1.5rem 0; color: #1e293b; font-size: 1.3rem;">
            <i class="fas fa-chart-line"></i> Évolution sur 6 Mois
        </h3>
        <div style="display: flex; align-items: flex-end; gap: 1rem; height: 200px; padding: 1rem 0;">
            <?php 
            $maxCount = max($stats['by_month'] ?? [1]);
            foreach ($stats['by_month'] ?? [] as $month => $count): 
                $height = $maxCount > 0 ? round(($count / $maxCount) * 100) : 0;
                $monthLabel = date('M Y', strtotime($month . '-01'));
            ?>
            <div style="flex: 1; display: flex; flex-direction: column; align-items: center;">
                <div style="background: linear-gradient(180deg, #667eea 0%, #764ba2 100%); width: 100%; border-radius: 4px 4px 0 0; min-height: 20px; height: <?= $height ?>%; margin-bottom: 0.5rem; transition: height 0.5s ease;"></div>
                <div style="font-size: 0.8rem; color: #64748b; text-align: center; transform: rotate(-45deg); transform-origin: center; white-space: nowrap;">
                    <?= $monthLabel ?>
                </div>
                <div style="font-weight: 600; color: #1e293b; margin-top: 0.5rem;"><?= $count ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Statistiques par Utilisateur -->
    <div class="chart-card" style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <h3 style="margin: 0 0 1.5rem 0; color: #1e293b; font-size: 1.3rem;">
            <i class="fas fa-users"></i> Statistiques par Utilisateur
        </h3>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e5e7eb;">
                        <th style="padding: 1rem; text-align: left; color: #374151; font-weight: 600;">Utilisateur</th>
                        <th style="padding: 1rem; text-align: center; color: #374151; font-weight: 600;">Total</th>
                        <th style="padding: 1rem; text-align: center; color: #374151; font-weight: 600;">🚨 Urgences</th>
                        <th style="padding: 1rem; text-align: center; color: #374151; font-weight: 600;">⏳ Ouvertes</th>
                        <th style="padding: 1rem; text-align: center; color: #374151; font-weight: 600;">✅ Fermées</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($stats['by_user'])): ?>
                    <tr>
                        <td colspan="5" style="padding: 2rem; text-align: center; color: #6b7280;">
                            Aucune donnée utilisateur disponible
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($stats['by_user'] as $user): ?>
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 1rem;">
                            <div>
                                <div style="font-weight: 600; color: #1e293b;"><?= htmlspecialchars($user['username']) ?></div>
                                <div style="font-size: 0.875rem; color: #6b7280;"><?= htmlspecialchars($user['email']) ?></div>
                            </div>
                        </td>
                        <td style="padding: 1rem; text-align: center; font-weight: 700; color: #1e293b;">
                            <?= number_format($user['total_reclamations']) ?>
                        </td>
                        <td style="padding: 1rem; text-align: center;">
                            <span style="background: #fecaca; color: #dc2626; padding: 4px 8px; border-radius: 12px; font-weight: 600; font-size: 0.875rem;">
                                <?= number_format($user['urgences']) ?>
                            </span>
                        </td>
                        <td style="padding: 1rem; text-align: center;">
                            <span style="background: #fef3c7; color: #92400e; padding: 4px 8px; border-radius: 12px; font-weight: 600; font-size: 0.875rem;">
                                <?= number_format($user['ouvertes']) ?>
                            </span>
                        </td>
                        <td style="padding: 1rem; text-align: center;">
                            <span style="background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 12px; font-weight: 600; font-size: 0.875rem;">
                                <?= number_format($user['fermees']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Temps moyen de résolution -->
    <?php if (isset($stats['avg_resolution_days']) && $stats['avg_resolution_days'] > 0): ?>
    <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-top: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="margin: 0; opacity: 0.9; font-size: 0.9rem;">Temps Moyen de Résolution</p>
                <h2 style="margin: 0.5rem 0 0 0; font-size: 2.5rem; font-weight: 700;">
                    <?= number_format($stats['avg_resolution_days'], 1) ?> jours
                </h2>
            </div>
            <div style="font-size: 3rem; opacity: 0.3;">
                <i class="fas fa-hourglass-half"></i>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr !important;
        }
        
        .chart-card {
            padding: 1rem !important;
        }
        
        table {
            font-size: 0.875rem;
        }
        
        th, td {
            padding: 0.5rem !important;
        }
    }
</style>

<?php include '../../../admin_footer.php'; ?>

