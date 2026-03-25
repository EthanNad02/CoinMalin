<?php
require_once 'config.php';
require_once 'functions.php';
$title = "Mes Favoris";
require_once 'header.php';

if (!est_connecte()) {
    rediriger('connexion.php');
}

$user_id = $_SESSION['user_id'];
$favoris = get_favoris($user_id);
?>

<h1>Mes Favoris</h1>

<?php if (!empty($favoris)): ?>
    <div class="annonces-container">
        <?php foreach ($favoris as $annonce): ?>
            <div class="annonce-card">
                <?php if (!empty($annonce['photo'])): ?>
                    <div class="annonce-image">
                        <img src="assets/uploads/<?php echo $annonce['photo']; ?>" alt="<?php echo htmlspecialchars($annonce['titre']); ?>">
                    </div>
                <?php endif; ?>
                <div class="annonce-details">
                    <h3><?php echo htmlspecialchars($annonce['titre']); ?></h3>
                    <p class="prix"><?php echo $annonce['prix']; ?> €</p>
                    <p class="categorie">
                        <?php
                        $conn = connect_db();
                        $categorie = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nom FROM categories WHERE id = " . $annonce['id_categorie']));
                        echo htmlspecialchars($categorie['nom']);
                        mysqli_close($conn);
                        ?>
                    </p>
                    <div class="annonce-actions">
                        <a href="voir_annonce.php?id=<?php echo $annonce['id']; ?>" class="btn">Voir plus</a>
                        <a href="voir_annonce.php?id=<?php echo $annonce['id']; ?>&action=retirer_favoris" class="btn btn-error">Retirer</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p class="aucune-annonce">Vous n'avez pas encore de favoris.</p>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
