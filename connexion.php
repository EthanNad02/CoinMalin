<?php
require_once 'functions.php';

$title = "Connexion";

// 🔥 Si déjà connecté → redirection AVANT HTML
if (est_connecte()) {
    rediriger('profil.php');
}

$message = "";

$conn = connect_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];

    $sql = "SELECT id, mot_de_passe FROM utilisateurs WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($mot_de_passe, $user['mot_de_passe'])) {
            $_SESSION['user_id'] = $user['id'];
            rediriger('profil.php'); // ✅ OK ici
        } else {
            $message = "Mot de passe incorrect.";
        }
    } else {
        $message = "Utilisateur non trouvé.";
    }

    mysqli_close($conn);
}

require_once 'header.php';
?>

<h1>Connexion</h1>

<?php if (!empty($message)): ?>
    <p class="error"><?php echo $message; ?></p>
<?php endif; ?>

<form method="POST" class="form">
    <div class="form-group">
        <label for="email">Email :</label>
        <input type="email" id="email" name="email" required>
    </div>
    <div class="form-group">
        <label for="mot_de_passe">Mot de passe :</label>
        <input type="password" id="mot_de_passe" name="mot_de_passe" required>
    </div>
    <button type="submit" class="btn">Se connecter</button>
</form>

<?php require_once 'footer.php'; ?>