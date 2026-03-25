<?php
require_once 'config.php';
require_once 'functions.php';
$title = "Modifier mon Profil";
require_once 'header.php';

if (!est_connecte()) {
    rediriger('connexion.php');
}

$user_id = $_SESSION['user_id'];
$conn = connect_db();

// Récupérer les informations de l'utilisateur
$sql = "SELECT * FROM utilisateurs WHERE id = $user_id";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = mysqli_real_escape_string($conn, $_POST['nom']);
    $prenom = mysqli_real_escape_string($conn, $_POST['prenom']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $telephone = mysqli_real_escape_string($conn, $_POST['telephone']);
    $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Validation des champs obligatoires
    if (empty($nom)) $errors[] = "Le nom est obligatoire.";
    if (empty($prenom)) $errors[] = "Le prénom est obligatoire.";
    if (empty($email)) $errors[] = "L'email est obligatoire.";

    // Validation de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide.";
    }

    // Vérification si l'email est déjà utilisé par un autre utilisateur
    if ($email != $user['email']) {
        $check_email = mysqli_query($conn, "SELECT id FROM utilisateurs WHERE email = '$email' AND id != $user_id");
        if (mysqli_num_rows($check_email) > 0) {
            $errors[] = "Cet email est déjà utilisé par un autre utilisateur.";
        }
    }

    // Validation du mot de passe si l'utilisateur veut le changer
    if (!empty($new_password)) {
        if (strlen($new_password) < 10) {
            $errors[] = "Le nouveau mot de passe doit contenir au moins 10 caractères.";
        }
        if (!preg_match('/[A-Z]/', $new_password)) {
            $errors[] = "Le nouveau mot de passe doit contenir au moins une majuscule.";
        }
        if (!preg_match('/[a-z]/', $new_password)) {
            $errors[] = "Le nouveau mot de passe doit contenir au moins une minuscule.";
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $new_password)) {
            $errors[] = "Le nouveau mot de passe doit contenir au moins un caractère spécial.";
        }
        if ($new_password != $confirm_password) {
            $errors[] = "Les nouveaux mots de passe ne correspondent pas.";
        }

        // Vérification du mot de passe actuel
        if (!empty($new_password) && !password_verify($current_password, $user['mot_de_passe'])) {
            $errors[] = "Le mot de passe actuel est incorrect.";
        }
    }

    if (empty($errors)) {
        // Mise à jour des informations de base
        $update_sql = "UPDATE utilisateurs SET
                       nom = '$nom',
                       prenom = '$prenom',
                       email = '$email',
                       telephone = '$telephone'";

        // Ajout de la mise à jour du mot de passe si nécessaire
        if (!empty($new_password)) {
            $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);
            $update_sql .= ", mot_de_passe = '$new_password_hash'";
        }

        $update_sql .= " WHERE id = $user_id";

        if (mysqli_query($conn, $update_sql)) {
            echo "<p class='success'>Votre profil a été mis à jour avec succès !</p>";
            // Mise à jour de la session si l'email a changé
            if ($email != $user['email']) {
                $_SESSION['email'] = $email;
            }
            rediriger('profil.php');
        } else {
            $errors[] = "Erreur lors de la mise à jour du profil : " . mysqli_error($conn);
        }
    }
}
?>

<h1>Modifier mon Profil</h1>

<?php if (!empty($errors)): ?>
    <div class="error-messages">
        <?php foreach ($errors as $error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="POST" class="form">
    <div class="form-group">
        <label for="nom">Nom :</label>
        <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
    </div>
    <div class="form-group">
        <label for="prenom">Prénom :</label>
        <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>" required>
    </div>
    <div class="form-group">
        <label for="email">Email :</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
    </div>
    <div class="form-group">
        <label for="telephone">Téléphone :</label>
        <input type="tel" id="telephone" name="telephone" value="<?php echo htmlspecialchars($user['telephone']); ?>">
    </div>

    <h2>Changer de mot de passe</h2>
    <p class="password-instructions">Laisser ces champs vides si vous ne souhaitez pas changer votre mot de passe.</p>

    <div class="form-group">
        <label for="current_password">Mot de passe actuel :</label>
        <input type="password" id="current_password" name="current_password">
    </div>
    <div class="form-group">
        <label for="new_password">Nouveau mot de passe :</label>
        <input type="password" id="new_password" name="new_password">
        <small>Le mot de passe doit contenir au moins 10 caractères, une majuscule, une minuscule et un caractère spécial.</small>
    </div>
    <div class="form-group">
        <label for="confirm_password">Confirmer le nouveau mot de passe :</label>
        <input type="password" id="confirm_password" name="confirm_password">
    </div>

    <button type="submit" class="btn">Mettre à jour mon profil</button>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const newPasswordInput = document.getElementById('new_password');
    const passwordError = document.createElement('div');
    passwordError.className = 'password-error';
    newPasswordInput.parentNode.appendChild(passwordError);

    newPasswordInput.addEventListener('input', function() {
        const password = this.value;
        let errorMessage = '';

        if (password.length > 0) {
            if (password.length < 10) {
                errorMessage += 'Le mot de passe doit contenir au moins 10 caractères. ';
            }
            if (!/[A-Z]/.test(password)) {
                errorMessage += 'Le mot de passe doit contenir au moins une majuscule. ';
            }
            if (!/[a-z]/.test(password)) {
                errorMessage += 'Le mot de passe doit contenir au moins une minuscule. ';
            }
            if (!/[^a-zA-Z0-9]/.test(password)) {
                errorMessage += 'Le mot de passe doit contenir au moins un caractère spécial. ';
            }
        }

        if (errorMessage) {
            passwordError.textContent = errorMessage;
            passwordError.style.color = '#f44336';
            passwordError.style.fontSize = '0.9rem';
            passwordError.style.marginTop = '5px';
        } else {
            passwordError.textContent = '';
        }
    });
});
</script>

<?php mysqli_close($conn); ?>
<?php require_once 'footer.php'; ?>
