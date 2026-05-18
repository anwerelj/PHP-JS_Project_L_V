<?php
session_start();
require_once('../config/config.php');

$error = '';

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = htmlentities(trim($_POST['username']));
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        define('ADMIN_LOGIN', 'admin');
        define('ADMIN_PASS',  'admin123');

        if ($username === ADMIN_LOGIN && $password === ADMIN_PASS) {
            $_SESSION['admin'] = true;
            header("Location: gestion.php");
            exit();
        } else {
            $error = "Nom d'utilisateur ou mot de passe incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="container">
        <h1 class="logo"><a href="../index.php" style="text-decoration:none;color:inherit;">AutoLoc Tunisie</a></h1>
        <ul class="nav-links">
            <li><a href="../index.php">Accueil</a></li>
        </ul>
    </div>
</nav>

<div class="form-container">
    <h2>Connexion Administrateur</h2>

    <?php if ($error) { echo '<div class="alert alert-error">'.$error.'</div>'; } ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Nom d'utilisateur</label>
            <input type="text" name="username" required
                   value="<?= isset($_POST['username']) ? htmlentities($_POST['username']) : '' ?>">
        </div>
        <div class="form-group">
            <label>Mot de passe</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;">Se connecter</button>
    </form>
</div>

<footer>
    <p>&copy; <?= date('Y') ?> AutoLoc Tunisie</p>
</footer>
</body>
</html>


