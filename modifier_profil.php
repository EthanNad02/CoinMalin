<?php
require_once 'config.php';
require_once 'functions.php';
$title = "Modifier mon Profil";
require_once 'header.php';

if (!est_connecte()) {
    rediriger('connexion.php');
}

$user_id = $_SESSION['user_id'];
$conn    = connect_db();
$user    = get_utilisateur($user_id);

// ── Vérifier si changement forcé (mdp expiré) ────────────────
$force_mdp = isset($_SESSION['force_mdp_change']) && $_SESSION['force_mdp_change'] === true;
// Double-vérification côté serveur
if (!$force_mdp) {
    $force_mdp = mdp_expire($user);
}

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom              = mysqli_real_escape_string($conn, trim($_POST['nom'] ?? ''));
    $prenom           = mysqli_real_escape_string($conn, trim($_POST['prenom'] ?? ''));
    $email            = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $telephone        = mysqli_real_escape_string($conn, trim($_POST['telephone'] ?? ''));
    $current_password = $_POST['current_password'] ?? '';
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation champs de base
    if (empty($nom))    $errors[] = "Le nom est obligatoire.";
    if (empty($prenom)) $errors[] = "Le prénom est obligatoire.";
    if (empty($email))  $errors[] = "L'email est obligatoire.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide.";
    }

    // Email dupliqué
    if ($email != $user['email']) {
        $check_email = mysqli_query($conn, "SELECT id FROM utilisateurs WHERE email = '$email' AND id != $user_id");
        if (mysqli_num_rows($check_email) > 0) {
            $errors[] = "Cet email est déjà utilisé par un autre utilisateur.";
        }
    }

    // ── Gestion mot de passe ──────────────────────────────────
    $mdp_change_demande = !empty($new_password);

    // Si changement forcé, le nouveau mdp est OBLIGATOIRE
    if ($force_mdp && !$mdp_change_demande) {
        $errors[] = "Votre mot de passe a expiré. Vous devez en définir un nouveau.";
    }

    if ($mdp_change_demande || $force_mdp) {
        // Vérification mdp actuel
        if (!password_verify($current_password, $user['mot_de_passe'])) {
            $errors[] = "Le mot de passe actuel est incorrect.";
        }

        if ($mdp_change_demande) {
            // Règles de complexité
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
            if ($new_password !== $confirm_password) {
                $errors[] = "Les nouveaux mots de passe ne correspondent pas.";
            }

            // ── Vérification historique des 3 derniers mdp ───
            if (mdp_deja_utilise($new_password, $user['password_history'])) {
                $errors[] = "Ce mot de passe a déjà été utilisé récemment. Veuillez en choisir un différent de vos 3 derniers mots de passe.";
            }
            // Vérifier aussi que ce n'est pas le mdp actuel
            if (password_verify($new_password, $user['mot_de_passe'])) {
                $errors[] = "Le nouveau mot de passe doit être différent du mot de passe actuel.";
            }
        }
    }

    if (empty($errors)) {
        // Mise à jour infos de base
        $update_sql = "UPDATE utilisateurs SET
                           nom       = '$nom',
                           prenom    = '$prenom',
                           email     = '$email',
                           telephone = '$telephone'";

        // Mise à jour mdp si demandé
        if ($mdp_change_demande) {
            // Utiliser la fonction dédiée qui gère l'historique
            mettre_a_jour_mdp($user_id, $new_password, $user['mot_de_passe'], $user['password_history']);
            // On met à jour les autres champs séparément
            $update_sql .= " WHERE id = $user_id";
            mysqli_query($conn, $update_sql);
        } else {
            $update_sql .= " WHERE id = $user_id";
            mysqli_query($conn, $update_sql);
        }

        if ($email != $user['email']) {
            $_SESSION['email'] = $email;
        }

        // Lever le flag force si présent
        unset($_SESSION['force_mdp_change']);

        $success = true;
        // Rafraîchir les données user
        $user = get_utilisateur($user_id);

        // Redirection après succès
        header("Refresh: 2; url=profil.php");
    }
}
?>

<h1>Modifier mon Profil</h1>

<?php if ($force_mdp): ?>
    <!-- Bannière d'alerte expiration -->
    <div class="force-mdp-banner">
        <span>🔒</span>
        <div>
            <strong>Votre mot de passe a expiré !</strong>
            <p>Le renouvellement mensuel est obligatoire. Veuillez définir un nouveau mot de passe pour continuer à utiliser CoinMalin.</p>
        </div>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="success">✅ Votre profil a été mis à jour avec succès ! Redirection en cours...</div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="error-messages">
        <?php foreach ($errors as $error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="POST" class="form">

    <?php if ($force_mdp): ?>
        <!-- En mode forcé, on masque les champs non-essentiels pour centrer sur le mdp -->
        <input type="hidden" name="nom"       value="<?php echo htmlspecialchars($user['nom']); ?>">
        <input type="hidden" name="prenom"    value="<?php echo htmlspecialchars($user['prenom']); ?>">
        <input type="hidden" name="email"     value="<?php echo htmlspecialchars($user['email']); ?>">
        <input type="hidden" name="telephone" value="<?php echo htmlspecialchars($user['telephone'] ?? ''); ?>">
    <?php else: ?>
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
            <input type="tel" id="telephone" name="telephone" value="<?php echo htmlspecialchars($user['telephone'] ?? ''); ?>">
        </div>
    <?php endif; ?>

    <h2><?php echo $force_mdp ? 'Nouveau mot de passe obligatoire' : 'Changer de mot de passe'; ?></h2>

    <?php if (!$force_mdp): ?>
        <p class="password-instructions">Laisser ces champs vides si vous ne souhaitez pas changer votre mot de passe.</p>
    <?php else: ?>
        <p class="password-instructions" style="color: var(--error);">
            ⚠️ Vous ne pouvez pas réutiliser vos 3 derniers mots de passe.
        </p>
    <?php endif; ?>

    <div class="form-group">
        <label for="current_password">Mot de passe actuel :</label>
        <input type="password" id="current_password" name="current_password"
               <?php echo $force_mdp ? 'required' : ''; ?>>
    </div>
    <div class="form-group">
        <label for="new_password">Nouveau mot de passe :</label>
        <input type="password" id="new_password" name="new_password"
               <?php echo $force_mdp ? 'required' : ''; ?>>
        <small>Minimum 10 caractères, une majuscule, une minuscule, un caractère spécial.</small>
        <div id="pwd-strength" class="pwd-strength-bar" style="display:none">
            <div id="pwd-fill"></div>
        </div>
        <small id="pwd-feedback" style="color: var(--text-muted); font-size:0.8rem;"></small>
    </div>
    <div class="form-group">
        <label for="confirm_password">Confirmer le nouveau mot de passe :</label>
        <input type="password" id="confirm_password" name="confirm_password"
               <?php echo $force_mdp ? 'required' : ''; ?>>
        <small id="confirm-feedback" style="color: var(--error); font-size:0.8rem;"></small>
    </div>

    <button type="submit" class="btn"
        <?php echo $force_mdp ? '' : ''; ?>>
        <?php echo $force_mdp ? '🔒 Définir mon nouveau mot de passe' : 'Mettre à jour mon profil'; ?>
    </button>

    <?php if (!$force_mdp): ?>
        <a href="profil.php" class="btn btn-secondary" style="margin-left:10px;">Annuler</a>
    <?php endif; ?>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const newPwd     = document.getElementById('new_password');
    const confirmPwd = document.getElementById('confirm_password');
    const feedback   = document.getElementById('pwd-feedback');
    const confFb     = document.getElementById('confirm-feedback');
    const bar        = document.getElementById('pwd-strength');
    const fill       = document.getElementById('pwd-fill');

    newPwd.addEventListener('input', function () {
        const p = this.value;
        if (!p) { bar.style.display = 'none'; feedback.textContent = ''; return; }
        bar.style.display = 'block';

        let score = 0;
        let msgs  = [];

        if (p.length >= 10)            score++; else msgs.push('10 caractères min.');
        if (/[A-Z]/.test(p))           score++; else msgs.push('une majuscule');
        if (/[a-z]/.test(p))           score++; else msgs.push('une minuscule');
        if (/[^a-zA-Z0-9]/.test(p))    score++; else msgs.push('un caractère spécial');
        if (p.length >= 14)            score++;

        const pct   = (score / 5) * 100;
        fill.style.width = pct + '%';
        fill.style.background = pct < 40 ? '#FF5C35' : pct < 70 ? '#FFB547' : '#39D98A';

        feedback.textContent = msgs.length ? 'Manque : ' + msgs.join(', ') : '✅ Mot de passe fort';
        feedback.style.color = msgs.length ? '#FFB547' : '#39D98A';
    });

    confirmPwd.addEventListener('input', function () {
        if (this.value && this.value !== newPwd.value) {
            confFb.textContent = '⚠ Les mots de passe ne correspondent pas.';
        } else {
            confFb.textContent = '';
        }
    });
});
</script>

<?php mysqli_close($conn); ?>
<?php require_once 'footer.php'; ?>