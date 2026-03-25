<?php
require_once 'config.php';
require_once 'functions.php';
$title = "Inscription";
require_once 'header.php';

if (est_connecte()) {
    rediriger('profil.php');
}
$conn = connect_db();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = mysqli_real_escape_string($conn, $_POST['nom']);
    $prenom = mysqli_real_escape_string($conn, $_POST['prenom']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];
    $telephone = mysqli_real_escape_string($conn, $_POST['telephone']);

    // Validation du mot de passe
    if (strlen($mot_de_passe) < 10) {
        $errors[] = "Le mot de passe doit contenir au moins 10 caractères.";
    }
    if (!preg_match('/[A-Z]/', $mot_de_passe)) {
        $errors[] = "Le mot de passe doit contenir au moins une majuscule.";
    }
    if (!preg_match('/[a-z]/', $mot_de_passe)) {
        $errors[] = "Le mot de passe doit contenir au moins une minuscule.";
    }
    if (!preg_match('/[^a-zA-Z0-9]/', $mot_de_passe)) {
        $errors[] = "Le mot de passe doit contenir au moins un caractère spécial.";
    }

    if (empty($errors)) {
        $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_BCRYPT);

        
        $sql = "INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, telephone)
                VALUES ('$nom', '$prenom', '$email', '$mot_de_passe_hash', '$telephone')";

if (mysqli_query($conn, $sql)) {
    $_SESSION['user_id'] = mysqli_insert_id($conn);
    
    rediriger('profil.php');
} else {
            if (mysqli_errno($conn) == 1062) { // Erreur de duplication (email déjà utilisé)
                $errors[] = "Cet email est déjà utilisé.";
            } else {
                $errors[] = "Erreur : " . mysqli_error($conn);
            }
        }
        mysqli_close($conn);
    }
}
?>

<h1>Inscription</h1>

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
        <input type="text" id="nom" name="nom" value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>" required>
    </div>
    <div class="form-group">
        <label for="prenom">Prénom :</label>
        <input type="text" id="prenom" name="prenom" value="<?php echo isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : ''; ?>" required>
    </div>
    <div class="form-group">
        <label for="email">Email :</label>
        <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
    </div>
    <div class="form-group">
        <label for="mot_de_passe">Mot de passe :</label>
        <input type="password" id="mot_de_passe" name="mot_de_passe" required>
        <small>Le mot de passe doit contenir au moins 10 caractères, une majuscule, une minuscule et un caractère spécial.</small>
    </div>
    <div class="form-group">
        <label for="telephone">Téléphone :</label>
        <input type="tel" id="telephone" name="telephone" value="<?php echo isset($_POST['telephone']) ? htmlspecialchars($_POST['telephone']) : ''; ?>">
    </div>
    <button type="submit" class="btn">S'inscrire</button>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('mot_de_passe');
    const passwordError = document.createElement('div');
    passwordError.className = 'password-error';
    passwordInput.parentNode.appendChild(passwordError);

    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let errorMessage = '';

        if (password.length > 0 && password.length < 10) {
            errorMessage += 'Le mot de passe doit contenir au moins 10 caractères. ';
        }
        if (password.length > 0 && !/[A-Z]/.test(password)) {
            errorMessage += 'Le mot de passe doit contenir au moins une majuscule. ';
        }
        if (password.length > 0 && !/[a-z]/.test(password)) {
            errorMessage += 'Le mot de passe doit contenir au moins une minuscule. ';
        }
        if (password.length > 0 && !/[^a-zA-Z0-9]/.test(password)) {
            errorMessage += 'Le mot de passe doit contenir au moins un caractère spécial. ';
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


<?php require_once 'footer.php'; ?>
