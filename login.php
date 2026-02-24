<?php
session_start();
require 'db.php';
require 'vendor/autoload.php'; // Charge Predis

// Connexion à Redis
$redis = new Predis\Client([
    'host' => '127.0.0.1',
    'port' => 6379,
]);

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $mdp   = md5(trim($_POST['mot_de_passe']));

    // 1. Vérifie dans MySQL si l'utilisateur existe
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ? AND mot_de_passe = ?");
    $stmt->execute([$email, $mdp]);
    $user = $stmt->fetch();

    if ($user) {
        $cle = "connexions:" . $email;
        $compteur = $redis->get($cle);

        if ($compteur === null) {
            // Première connexion dans la fenêtre
            $redis->set($cle, 1);
            $redis->expire($cle, 600); // 10 minutes
            $compteur = 1;
            $autorise = true;

        } elseif ((int)$compteur < 10) {
            // Sous la limite → on incrémente
            $compteur = $redis->incr($cle);
            $autorise = true;

        } else {
            // Limite atteinte
            $autorise = false;
        }

        if ($autorise) {
            // Crée la session utilisateur
            $_SESSION['utilisateur'] = [
                'id'     => $user['id'],
                'nom'    => $user['nom'],
                'prenom' => $user['prenom'],
                'email'  => $user['email'],
            ];
            $_SESSION['nb_connexions'] = $compteur;
            header('Location: services.php');
            exit;

        } else {
            $ttl = $redis->ttl($cle);
            $erreur = "Trop de connexions. Réessayez dans $ttl secondes.";
        }

    } else {
        $erreur = "Email ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>EtuServices - Connexion</title>
</head>
<body>
    <h1>Connexion</h1>

    <?php if ($erreur): ?>
        <p style="color:red;"><?= $erreur ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Email :</label><br>
        <input type="email" name="email" required><br><br>

        <label>Mot de passe :</label><br>
        <input type="password" name="mot_de_passe" required><br><br>

        <button type="submit">Se connecter</button>
    </form>

    <br><a href="index.php">← Retour à l'accueil</a>
</body>
</html>