<?php
session_start();
require 'db.php';
require 'vendor/autoload.php';

// DB 0 → rate limiting
$redis0 = new Predis\Client(['host' => '127.0.0.1', 'port' => 6379, 'database' => 0]);

// DB 1 → statistiques
$redis1 = new Predis\Client(['host' => '127.0.0.1', 'port' => 6379, 'database' => 1]);

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $mdp   = md5(trim($_POST['mot_de_passe']));

    // 1. Vérifie dans MySQL
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ? AND mot_de_passe = ?");
    $stmt->execute([$email, $mdp]);
    $user = $stmt->fetch();

    if ($user) {
        $cle = "connexions:" . $email;
        $compteur = $redis0->get($cle);

        if ($compteur === null) {
            $redis0->set($cle, 1);
            $redis0->expire($cle, 600);
            $compteur = 1;
            $autorise = true;
        } elseif ((int)$compteur < 10) {
            $compteur = $redis0->incr($cle);
            $autorise = true;
        } else {
            $autorise = false;
        }

        if ($autorise) {
            // Stats dans DB 1
            // 1. Les 10 derniers connectés (LIST)
            $redis1->lpush("stats:recents", $email);
            $redis1->ltrim("stats:recents", 0, 9);

            // 2. Total connexions par utilisateur (SORTED SET)
            $redis1->zincrby("stats:total_connexions", 1, $email);

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
            $ttl = $redis0->ttl($cle);
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