<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>EtuServices - Accueil</title>
</head>
<body>
    <h1>Bienvenue sur EtuServices</h1>
    <p>Achetez et vendez vos articles facilement.</p>

    <?php if (isset($_SESSION['utilisateur'])): ?>
        <p>Bonjour <strong><?= $_SESSION['utilisateur']['prenom'] ?></strong> !</p>
        <a href="services.php">Accéder aux services</a> |
        <a href="logout.php">Se déconnecter</a>
    <?php else: ?>
        <a href="login.php">Se connecter</a>
    <?php endif; ?>

    <br><br>
    <a href="stats.php">📊 Voir les statistiques</a>
</body>
</html>