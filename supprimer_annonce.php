<?php
require_once 'config.php';
require_once 'functions.php';
$title = "Supprimer une Annonce";
require_once 'header.php';

if (!est_connecte()) {
    rediriger('connexion.php');
}

if (!isset($_GET['id'])) {
    rediriger('index.php');
}

$id_annonce = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
$conn = connect_db();

// Vérifie que l'annonce appartient bien à l'utilisateur connecté
$sql = "SELECT * FROM annonces WHERE id = $id_annonce AND id_utilisateur = $user_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    echo "<p class='error'>Vous n'avez pas la permission de supprimer cette annonce.</p>";
    require_once 'includes/footer.php';
    exit();
}

$annonce = mysqli_fetch_assoc($result);

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($annonce['photo']) && file_exists('assets/uploads/' . $annonce['photo'])) {
        unlink('assets/uploads/' . $annonce['photo']);
    }

    $sql_delete = "DELETE FROM annonces WHERE id = $id_annonce";
    if (mysqli_query($conn, $sql_delete)) {
        echo "<p class='success'>Annonce supprimée avec succès !</p>";
        rediriger('profil.php');
    } else {
        echo "<p class='error'>Erreur : " . mysqli_error($conn) . "</p>";
    }
}
?>

<h1>Supprimer une Annonce</h1>
<p>Êtes-vous sûr de vouloir supprimer l'annonce "<strong><?php echo htmlspecialchars($annonce['titre']); ?></strong>" ?</p>
<?php if (!empty($annonce['photo'])): ?>
    <img src="<?php echo 'assets/uploads/' . $annonce['photo']; ?>" alt="Photo de l'annonce" width="200">
<?php endif; ?>

<form method="POST" class="form">
    <div class="annonce-actions">
        <button type="submit" class="btn btn-error">Supprimer définitivement</button>
        <a href="<?php echo 'voir_annonce.php?id=' . $id_annonce; ?>" class="btn">Annuler</a>
    </div>
</form>

<?php mysqli_close($conn); ?>
<?php require_once 'footer.php'; ?>
