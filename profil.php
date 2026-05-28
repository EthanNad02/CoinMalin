<?php
require_once 'config.php';
require_once 'functions.php';
$title = "Mon Profil";
require_once 'header.php';

if (!est_connecte()) {
    rediriger('connexion.php');
}

$user_id = $_SESSION['user_id'];
$conn    = connect_db();
$user    = get_utilisateur($user_id);

// ── Vérification expiration mot de passe ──────────────────────
$expire      = mdp_expire($user);
$temps       = temps_restant_mdp($user);
$pourcentage = 0;
if (!$expire && $temps['total_secondes'] > 0) {
    $pourcentage = round(($temps['total_secondes'] / (30 * 24 * 3600)) * 100);
}
// Couleur de la barre selon urgence
$bar_color = '#39D98A'; // vert
if ($pourcentage < 40) $bar_color = '#FFB547'; // orange
if ($pourcentage < 15) $bar_color = '#FF5C35'; // rouge

// Si le mot de passe a expiré → forcer la redirection
if ($expire) {
    $_SESSION['force_mdp_change'] = true;
    rediriger('modifier_profil.php');
}
?>

<h1>Mon Profil</h1>

<div class="profil-header">
    <div class="profil-info">
        <h2>Bonjour, <?php echo htmlspecialchars($user['prenom']); ?> !</h2>
        <p>Email : <?php echo htmlspecialchars($user['email']); ?></p>
        <?php if (!empty($user['telephone'])): ?>
            <p>Téléphone : <?php echo htmlspecialchars($user['telephone']); ?></p>
        <?php endif; ?>
    </div>
    <div class="profil-actions">
        <a href="modifier_profil.php" class="btn">Modifier mon profil</a>
    </div>
</div>

<!-- ── BLOC SÉCURITÉ MOT DE PASSE ─────────────────────────── -->
<div class="password-expiry-card <?php echo $pourcentage < 15 ? 'urgent' : ($pourcentage < 40 ? 'warning' : ''); ?>">
    <div class="expiry-header">
        <span class="expiry-icon">
            <?php if ($pourcentage < 15): ?>🔴<?php elseif ($pourcentage < 40): ?>🟡<?php else: ?>🟢<?php endif; ?>
        </span>
        <h3>Sécurité du mot de passe</h3>
    </div>

    <div class="expiry-countdown">
        <span class="countdown-block">
            <strong id="days"><?php echo $temps['jours']; ?></strong>
            <small>jour<?php echo $temps['jours'] > 1 ? 's' : ''; ?></small>
        </span>
        <span class="countdown-sep">:</span>
        <span class="countdown-block">
            <strong id="hours"><?php echo $temps['heures']; ?></strong>
            <small>heure<?php echo $temps['heures'] > 1 ? 's' : ''; ?></small>
        </span>
        <span class="countdown-sep">:</span>
        <span class="countdown-block">
            <strong id="minutes"><?php echo $temps['minutes']; ?></strong>
            <small>min</small>
        </span>
        <span class="countdown-sep">:</span>
        <span class="countdown-block">
            <strong id="seconds">00</strong>
            <small>sec</small>
        </span>
    </div>

    <p class="expiry-label">avant le renouvellement obligatoire du mot de passe</p>

    <div class="expiry-bar-container">
        <div class="expiry-bar" id="expiryBar"
             style="width: <?php echo $pourcentage; ?>%; background: <?php echo $bar_color; ?>;"></div>
    </div>
    <p class="expiry-bar-legend">
        <?php echo $pourcentage; ?>% du délai restant
        <?php if ($pourcentage < 15): ?>
            — <strong style="color:#FF5C35">Changez votre mot de passe maintenant !</strong>
        <?php elseif ($pourcentage < 40): ?>
            — Pensez à changer votre mot de passe bientôt.
        <?php endif; ?>
    </p>
</div>
<!-- ── FIN BLOC SÉCURITÉ ───────────────────────────────────── -->

<div class="profil-container">
    <div class="profil-section">
        <h2>Mes Annonces</h2>
        <?php
        $sql    = "SELECT * FROM annonces WHERE id_utilisateur = $user_id ORDER BY date_publication DESC";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            echo '<div class="annonces-container">';
            while ($annonce = mysqli_fetch_assoc($result)) {
                echo '<div class="annonce-card">';
                echo '<h3>' . htmlspecialchars($annonce['titre']) . '</h3>';
                echo '<p>' . htmlspecialchars(substr($annonce['description'], 0, 100)) . '...</p>';
                echo '<p class="prix">' . $annonce['prix'] . ' €</p>';
                if (!empty($annonce['photo'])) {
                    echo '<img src="assets/uploads/' . $annonce['photo'] . '" alt="' . htmlspecialchars($annonce['titre']) . '" width="100">';
                }
                echo '<div class="annonce-actions">';
                echo '<a href="modifier_annonce.php?id=' . $annonce['id'] . '" class="btn">Modifier</a>';
                echo '<a href="supprimer_annonce.php?id=' . $annonce['id'] . '" class="btn btn-error">Supprimer</a>';
                echo '</div></div>';
            }
            echo '</div>';
        } else {
            echo '<p class="aucune-annonce">Vous n\'avez pas encore publié d\'annonces.</p>';
        }
        ?>
        <a href="ajouter_annonce.php" class="btn">Ajouter une annonce</a>
    </div>

    <div class="profil-section">
        <h2>Mes Favoris</h2>
        <?php
        $favoris = get_favoris($user_id);
        if (!empty($favoris)) {
            echo '<div class="annonces-container">';
            foreach ($favoris as $annonce) {
                echo '<div class="annonce-card">';
                echo '<h3>' . htmlspecialchars($annonce['titre']) . '</h3>';
                echo '<p>' . htmlspecialchars(substr($annonce['description'], 0, 100)) . '...</p>';
                echo '<p class="prix">' . $annonce['prix'] . ' €</p>';
                if (!empty($annonce['photo'])) {
                    echo '<img src="assets/uploads/' . $annonce['photo'] . '" alt="' . htmlspecialchars($annonce['titre']) . '" width="100">';
                }
                echo '<div class="annonce-actions">';
                echo '<a href="voir_annonce.php?id=' . $annonce['id'] . '" class="btn">Voir</a>';
                echo '<a href="voir_annonce.php?id=' . $annonce['id'] . '&action=retirer_favoris" class="btn btn-error">Retirer</a>';
                echo '</div></div>';
            }
            echo '</div>';
        } else {
            echo '<p class="aucune-annonce">Vous n\'avez pas encore de favoris.</p>';
        }
        ?>
    </div>
</div>

<div class="profil-section">
    <h2>Mes Messages</h2>
    <a href="messages.php" class="btn">Voir mes messages</a>
</div>

<?php mysqli_close($conn); ?>

<!-- Countdown JS dynamique -->
<script>
    const expiryDate = new Date("<?php echo $temps['expiry_iso']; ?>");

    function updateCountdown() {
        const now  = new Date();
        const diff = expiryDate - now;

        if (diff <= 0) {
            document.getElementById('days').textContent    = '0';
            document.getElementById('hours').textContent   = '0';
            document.getElementById('minutes').textContent = '0';
            document.getElementById('seconds').textContent = '00';
            // Rediriger automatiquement
            window.location.href = 'modifier_profil.php';
            return;
        }

        const totalSec = Math.floor(diff / 1000);
        const totalMin = Math.floor(totalSec / 60);
        const jours    = Math.floor(totalMin / 60 / 24);
        const heures   = Math.floor((totalMin / 60) % 24);
        const minutes  = totalMin % 60;
        const secondes = totalSec % 60;

        document.getElementById('days').textContent    = jours;
        document.getElementById('hours').textContent   = heures;
        document.getElementById('minutes').textContent = minutes;
        document.getElementById('seconds').textContent = String(secondes).padStart(2, '0');

        // Mise à jour barre
        const totalDuree = 30 * 24 * 3600;
        const pct = Math.round((diff / 1000) / totalDuree * 100);
        const bar = document.getElementById('expiryBar');
        if (bar) {
            bar.style.width = pct + '%';
            if (pct < 15)      bar.style.background = '#FF5C35';
            else if (pct < 40) bar.style.background = '#FFB547';
            else               bar.style.background = '#39D98A';
        }
    }

    updateCountdown();
    setInterval(updateCountdown, 1000);
</script>

<?php require_once 'footer.php'; ?>