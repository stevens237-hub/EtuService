<?php
session_start();
require 'vendor/autoload.php';

if (!isset($_SESSION['utilisateur'])) {
    header('Location: login.php');
    exit;
}

// DB 1 → statistiques des services
$redis1 = new Predis\Client(['host' => '127.0.0.1', 'port' => 6379, 'database' => 1]);

$user = $_SESSION['utilisateur'];
$nb   = $_SESSION['nb_connexions'];
$message = '';

// Enregistre l'utilisation du service si cliqué
if (isset($_GET['service'])) {
    $service = in_array($_GET['service'], ['vente', 'achat']) ? $_GET['service'] : null;

    if ($service) {
        // Nombre total d'utilisations par service (SORTED SET)
        $redis1->zincrby("stats:services", 1, $service);

        // Utilisateurs qui ont utilisé ce service (SORTED SET par utilisateur)
        $redis1->zincrby("stats:services_par_user", 1, $user['email']);

        $message = "Vous avez accédé au service : " . strtoupper($service);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>EtuServices - Services</title>
</head>
<body>
    <h1>Services EtuServices</h1>
    <p>Bonjour <strong><?= $user['prenom'] . ' ' . $user['nom'] ?></strong></p>
    <p>Connexions utilisées : <strong><?= $nb ?> / 10</strong> sur les 10 dernières minutes</p>

    <?php if ($message): ?>
        <p style="color:green;"><strong><?= $message ?></strong></p>
    <?php endif; ?>

    <hr>

    <h2>🛒 Achat</h2>
    <p>Parcourez les articles disponibles à l'achat.</p>
    <a href="services.php?service=achat"><button>Accéder à l'Achat</button></a>

    <h2>💰 Vente</h2>
    <p>Mettez vos articles en vente.</p>
    <a href="services.php?service=vente"><button>Accéder à la Vente</button></a>

    <hr>
    <a href="stats.php">📊 Voir les statistiques</a> |
    <a href="logout.php">Se déconnecter</a>
</body>
</html>