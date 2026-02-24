<?php
require 'vendor/autoload.php';

$redis1 = new Predis\Client(['host' => '127.0.0.1', 'port' => 6379, 'database' => 1]);

// Récupère toutes les stats
$recents          = $redis1->lrange("stats:recents", 0, 9);
$top3             = $redis1->zrevrange("stats:total_connexions", 0, 2, ['WITHSCORES' => true]);
$tous_connexions  = $redis1->zrevrange("stats:total_connexions", 0, -1, ['WITHSCORES' => true]);
$moins_services   = $redis1->zrange("stats:services_par_user", 0, -1, ['WITHSCORES' => true]);
$services         = $redis1->zrevrange("stats:services", 0, -1, ['WITHSCORES' => true]);

$total_connexions_global = array_sum(array_values((array)$tous_connexions));
$total_services_global   = array_sum(array_values((array)$services));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques - EtuServices</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        .header {
            background: #1a1a2e;
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 { font-size: 1.4em; }
        .header a { color: #aaa; text-decoration: none; font-size: 0.9em; }
        .header a:hover { color: white; }
        .summary {
            display: flex;
            gap: 20px;
            padding: 30px 40px 10px;
        }
        .card {
            background: white;
            border-radius: 10px;
            padding: 20px 30px;
            flex: 1;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        }
        .card .number { font-size: 2.5em; font-weight: bold; color: #1a1a2e; }
        .card .label { color: #888; font-size: 0.9em; margin-top: 5px; }
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding: 20px 40px 40px;
        }
        .stat-box {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        }
        .stat-box h2 { font-size: 1em; color: #333; margin-bottom: 15px; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px; }
        .stat-box ol, .stat-box ul { padding-left: 20px; }
        .stat-box li { padding: 6px 0; font-size: 0.95em; color: #444; border-bottom: 1px solid #f9f9f9; }
        .badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 0.8em; font-weight: bold; margin-left: 8px; }
        .badge-green  { background: #e8f5e9; color: #2e7d32; }
        .badge-blue   { background: #e3f2fd; color: #1565c0; }
        .badge-orange { background: #fff3e0; color: #e65100; }
        .badge-red    { background: #fce4ec; color: #c62828; }
        .empty { color: #aaa; font-style: italic; font-size: 0.9em; }
        .progress-bar { background: #f0f2f5; border-radius: 10px; height: 10px; margin-top: 5px; }
        .progress-fill { background: #1a1a2e; border-radius: 10px; height: 10px; }
    </style>
</head>
<body>

<div class="header">
    <h1>📊 Statistiques — EtuServices</h1>
    <a href="login.php">← Retour à la connexion</a>
</div>

<!-- Cartes résumé -->
<div class="summary">
    <div class="card">
        <div class="number"><?= count((array)$tous_connexions) ?></div>
        <div class="label">Utilisateurs actifs</div>
    </div>
    <div class="card">
        <div class="number"><?= $total_connexions_global ?></div>
        <div class="label">Connexions totales</div>
    </div>
    <div class="card">
        <div class="number"><?= $total_services_global ?></div>
        <div class="label">Appels de services</div>
    </div>
    <div class="card">
        <div class="number"><?= count($recents) ?></div>
        <div class="label">Connexions récentes suivies</div>
    </div>
</div>

<!-- Grille de stats -->
<div class="stats-grid">

    <!-- 10 derniers connectés -->
    <div class="stat-box">
        <h2>🕐 10 derniers utilisateurs connectés</h2>
        <?php if (empty($recents)): ?>
            <p class="empty">Aucune donnée.</p>
        <?php else: ?>
            <ol>
                <?php foreach ($recents as $i => $email): ?>
                    <li>
                        <?= htmlspecialchars($email) ?>
                        <?php if ($i === 0): ?>
                            <span class="badge badge-green">Dernier connecté</span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </div>

    <!-- Top 3 -->
    <div class="stat-box">
        <h2>🏆 Top 3 des utilisateurs les plus connectés</h2>
        <?php if (empty($top3)): ?>
            <p class="empty">Aucune donnée.</p>
        <?php else: ?>
            <?php
            $medailles = ['🥇', '🥈', '🥉'];
            $i = 0;
            foreach ($top3 as $email => $score): ?>
                <div style="padding: 8px 0; border-bottom: 1px solid #f9f9f9;">
                    <?= $medailles[$i] ?? '' ?>
                    <?= htmlspecialchars($email) ?>
                    <span class="badge badge-blue"><?= $score ?> connexions</span>
                </div>
            <?php $i++; endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Tous les utilisateurs -->
    <div class="stat-box">
        <h2>👥 Connexions par utilisateur (tous)</h2>
        <?php if (empty($tous_connexions)): ?>
            <p class="empty">Aucune donnée.</p>
        <?php else: ?>
            <ol>
                <?php foreach ($tous_connexions as $email => $score): ?>
                    <li>
                        <?= htmlspecialchars($email) ?>
                        <span class="badge badge-blue"><?= $score ?> connexions</span>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </div>

    <!-- Moins utilisateurs services -->
    <div class="stat-box">
        <h2>📉 Utilisateurs qui ont le moins utilisé les services</h2>
        <?php if (empty($moins_services)): ?>
            <p class="empty">Aucun service utilisé pour l'instant.</p>
        <?php else: ?>
            <ol>
                <?php foreach ($moins_services as $email => $score): ?>
                    <li>
                        <?= htmlspecialchars($email) ?>
                        <span class="badge badge-red"><?= $score ?> utilisation(s)</span>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </div>

    <!-- Services -->
    <div class="stat-box" style="grid-column: span 2;">
        <h2>🔥 Utilisation des services</h2>
        <?php if (empty($services)): ?>
            <p class="empty">Aucun service utilisé pour l'instant.</p>
        <?php else: ?>
            <?php
            $max = max(array_values((array)$services));
            $first = true;
            foreach ($services as $service => $score):
                $pct = $max > 0 ? round(($score / $max) * 100) : 0;
            ?>
                <div style="padding: 10px 0; border-bottom: 1px solid #f9f9f9;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span>
                            <?= $first ? '🔥' : '📦' ?>
                            <strong><?= strtoupper(htmlspecialchars($service)) ?></strong>
                            <?php if ($first): ?>
                                <span class="badge badge-orange">Service le plus utilisé</span>
                            <?php endif; ?>
                        </span>
                        <span class="badge badge-green"><?= $score ?> utilisation(s)</span>
                    </div>
                    <div class="progress-bar" style="margin-top:8px;">
                        <div class="progress-fill" style="width: <?= $pct ?>%;"></div>
                    </div>
                </div>
            <?php $first = false; endforeach; ?>
        <?php endif; ?>
    </div>

</div>
</body>
</html>