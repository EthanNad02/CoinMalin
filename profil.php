<?php
require_once 'functions.php';

// 🔒 protection (SEULEMENT ici)
if (!est_connecte()) {
    rediriger("connexion.php");
}

require_once 'header.php';
?>

<?php
require_once 'config.php';
require_once 'functions.php';
$title = "Mon Profil";
require_once 'header.php';

if (!est_connecte()) {
    rediriger('connexion.php');
}

$user_id = $_SESSION['user_id'];
$conn = connect_db();
$user = get_utilisateur($user_id);
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
        <a href="<?php echo 'modifier_profil.php'; ?>" class="btn">Modifier mon profil</a>
    </div>
</div>
<div class="profil-container">
    <div class="profil-section">
        <h2>Mes Annonces</h2>
        <?php
        $sql = "SELECT * FROM annonces WHERE id_utilisateur = $user_id ORDER BY date_publication DESC";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            echo '<div class="annonces-container">';
            while ($annonce = mysqli_fetch_assoc($result)) {
                echo '<div class="annonce-card">';
                echo '<h3>' . htmlspecialchars($annonce['titre']) . '</h3>';
                echo '<p>' . htmlspecialchars(substr($annonce['description'], 0, 100)) . '...</p>';
                echo '<p class="prix">' . $annonce['prix'] . ' €</p>';
                if (!empty($annonce['photo'])) {
                    echo '<img src="' . 'assets/uploads/' . $annonce['photo'] . '" alt="' . htmlspecialchars($annonce['titre']) . '" width="100">';
                }
                echo '<div class="annonce-actions">';
                echo '<a href="' . 'modifier_annonce.php?id=' . $annonce['id'] . '" class="btn">Modifier</a>';
                echo '<a href="' . 'supprimer_annonce.php?id=' . $annonce['id'] . '" class="btn btn-error">Supprimer</a>';
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<p class="aucune-annonce">Vous n\'avez pas encore publié d\'annonces.</p>';
        }
        ?>
        <a href="<?php echo 'ajouter_annonce.php'; ?>" class="btn">Ajouter une annonce</a>
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
                    echo '<img src="' . 'assets/uploads/' . $annonce['photo'] . '" alt="' . htmlspecialchars($annonce['titre']) . '" width="100">';
                }
                echo '<div class="annonce-actions">';
                echo '<a href="' . 'voir_annonce.php?id=' . $annonce['id'] . '" class="btn">Voir</a>';
                echo '<a href="' . 'voir_annonce.php?id=' . $annonce['id'] . '&action=retirer_favoris' . '" class="btn btn-error">Retirer</a>';
                echo '</div>';
                echo '</div>';
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
    <a href="<?php echo 'messages.php'; ?>" class="btn">Voir mes messages</a>
</div>

<?php mysqli_close($conn); ?>
<?php require_once 'footer.php'; ?>
