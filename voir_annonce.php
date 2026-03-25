<?php
require_once 'config.php';
require_once 'functions.php';
$title = "Détails de l'Annonce";
require_once 'header.php';

if (!isset($_GET['id'])) {
    rediriger('index.php');
}

$id_annonce = (int)$_GET['id'];
$conn = connect_db();

$sql = "SELECT a.*, u.id AS id_vendeur, u.nom, u.prenom, u.email, u.telephone, c.nom AS categorie FROM annonces a
        JOIN utilisateurs u ON a.id_utilisateur = u.id
        JOIN categories c ON a.id_categorie = c.id
        WHERE a.id = $id_annonce";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Erreur SQL: " . mysqli_error($conn));
}

if (mysqli_num_rows($result) == 0) {
    echo "<p class='error'>Annonce non trouvée.</p>";
    require_once 'footer.php';
    exit();
}

$annonce = mysqli_fetch_assoc($result);

if (est_connecte()) {
    $id_utilisateur = $_SESSION['user_id'];
    if (isset($_GET['action']) && $_GET['action'] == 'ajouter_favoris') {
        ajouter_favoris($id_utilisateur, $id_annonce);
        rediriger('voir_annonce.php?id=' . $id_annonce);
    } elseif (isset($_GET['action']) && $_GET['action'] == 'retirer_favoris') {
        retirer_favoris($id_utilisateur, $id_annonce);
        rediriger('voir_annonce.php?id=' . $id_annonce);
    }
}
?>

<a href="javascript:history.back()" class="btn btn-secondary" style="display:inline-block; margin-bottom: 15px;">← Retour</a>

<div class="annonce-detail">
    <h1><?php echo htmlspecialchars($annonce['titre']); ?></h1>
    <p class="prix"><?php echo $annonce['prix']; ?> €</p>
    <p class="categorie">Catégorie: <?php echo htmlspecialchars($annonce['categorie']); ?></p>

    <?php if (!empty($annonce['photo'])): ?>
        <div class="annonce-image">
            <img src="<?php echo 'assets/uploads/' . $annonce['photo']; ?>" alt="<?php echo htmlspecialchars($annonce['titre']); ?>">
        </div>
    <?php endif; ?>

    <div class="annonce-description">
        <h2>Description</h2>
        <p><?php echo nl2br(htmlspecialchars($annonce['description'])); ?></p>
    </div>

    <div class="vendeur-info">
        <h2>Informations du vendeur</h2>
        <p><strong>Nom:</strong> <?php echo htmlspecialchars($annonce['prenom'] . ' ' . $annonce['nom']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($annonce['email']); ?></p>
        <?php if (!empty($annonce['telephone'])): ?>
            <p><strong>Téléphone:</strong> <?php echo htmlspecialchars($annonce['telephone']); ?></p>
        <?php endif; ?>
    </div>

    <div class="annonce-actions">
        <?php if (est_connecte()): ?>
            <?php if ($annonce['id_vendeur'] != $_SESSION['user_id']): ?>
                <a href="<?php echo 'envoyer_message.php?annonce_id=' . $annonce['id'] . '&destinataire_id=' . $annonce['id_vendeur']; ?>" class="btn">Contacter le vendeur</a>

                <?php if (est_favoris($_SESSION['user_id'], $annonce['id'])): ?>
                    <a href="<?php echo 'voir_annonce.php?id=' . $annonce['id'] . '&action=retirer_favoris'; ?>" class="btn btn-secondary">Retirer des favoris</a>
                <?php else: ?>
                    <a href="<?php echo 'voir_annonce.php?id=' . $annonce['id'] . '&action=ajouter_favoris'; ?>" class="btn btn-secondary">Ajouter aux favoris</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="<?php echo 'modifier_annonce.php?id=' . $annonce['id']; ?>" class="btn">Modifier</a>
                <a href="<?php echo 'supprimer_annonce.php?id=' . $annonce['id']; ?>" class="btn btn-error">Supprimer</a>
            <?php endif; ?>
        <?php else: ?>
            <p>Connectez-vous pour contacter le vendeur ou ajouter cette annonce à vos favoris.</p>
        <?php endif; ?>
    </div>
</div>

<?php mysqli_close($conn); ?>
<?php require_once 'footer.php'; ?>