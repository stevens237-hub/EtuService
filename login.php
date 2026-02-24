<?php
session_start();
require 'db.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $mdp   = md5(trim($_POST['mot_de_passe']));

    // 1. Vérifie si l'utilisateur existe dans MySQL
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ? AND mot_de_passe = ?");
    $stmt->execute([$email, $mdp]);
    $user = $stmt->fetch();

    if ($user) {
        // 2. Appelle le script Python pour vérifier Redis
        $email_safe = escapeshellarg($email);
        $cmd = "cd /mnt/c/xampp/htdocs/EtuServices && source venv/bin/activate && python3 redis_check.py $email_safe";
        $output = shell_exec("bash -c " . escapeshellarg($cmd));
        $output = trim($output);

        // 3. Analyse la réponse Python
        $parts = explode('|', $output);

        if ($parts[0] === 'AUTORISE') {
            // Connexion OK → on crée la session
            $_SESSION['utilisateur'] = [
                'id'     => $user['id'],
                'nom'    => $user['nom'],
                'prenom' => $user['prenom'],
                'email'  => $user['email'],
            ];
            $_SESSION['nb_connexions'] = $parts[1];
            header('Location: services.php');
            exit;
        } else {
            // Limite atteinte
            $reset = isset($parts[3]) ? $parts[3] : '';
            $erreur = "Trop de connexions. Réessayez dans quelques minutes. ($reset)";
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