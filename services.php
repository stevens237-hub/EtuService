<?php
session_start();

// Redirige si pas connecté
if (!isset($_SESSION['utilisateur'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['utilisateur'];
$nb   = $_SESSION['nb_connexions'];
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

    <hr>

    <h2>🛒 Achat</h2>
    <p>Parcourez les articles disponibles à l'achat.</p>
    <button onclick="alert('Service Achat en cours de développement')">Accéder à l'Achat</button>

    <h2>💰 Vente</h2>
    <p>Mettez vos articles en vente.</p>
    <button onclick="alert('Service Vente en cours de développement')">Accéder à la Vente</button>

    <hr>
    <a href="logout.php">Se déconnecter</a>
</body>
</html>