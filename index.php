<?php
require_once 'config.php';
require_once 'functions.php';
$title = "Accueil";
require_once 'header.php';

// Récupérer les catégories pour le filtre
$conn = connect_db();
$sql_categories = "SELECT * FROM categories";
$result_categories = mysqli_query($conn, $sql_categories);

// Traitement de la recherche et du filtre par catégorie
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$categorie_id = isset($_GET['categorie']) ? (int)$_GET['categorie'] : 0;

// Requête pour les annonces (avec filtre et recherche)
$sql_annonces = "SELECT a.*, u.nom, u.prenom, c.nom AS categorie FROM annonces a
                JOIN utilisateurs u ON a.id_utilisateur = u.id
                JOIN categories c ON a.id_categorie = c.id
                WHERE 1";

if ($categorie_id > 0) {
    $sql_annonces .= " AND a.id_categorie = $categorie_id";
}

if (!empty($search)) {
    $sql_annonces .= " AND (a.titre LIKE '%$search%' OR a.description LIKE '%$search%')";
}

$sql_annonces .= " ORDER BY a.date_publication DESC LIMIT 6";
$result_annonces = mysqli_query($conn, $sql_annonces);
?>

<h1>Bienvenue sur CoinMalin</h1>
<p>Découvrez nos dernières annonces...</p>

<!-- Barre de recherche et filtre par catégorie -->
<div class="search-filter">
    <form method="GET" action="index.php">
        <input type="text" name="search" placeholder="Rechercher une annonce..." value="<?php echo htmlspecialchars($search); ?>">
        <select name="categorie">
            <option value="0">Toutes les catégories</option>
            <?php while ($categorie = mysqli_fetch_assoc($result_categories)): ?>
                <option value="<?php echo $categorie['id']; ?>"
                    <?php echo ($categorie_id == $categorie['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($categorie['nom']); ?>
                </option>
            <?php endwhile; ?>
        </select>
        <button type="submit">Rechercher</button>
    </form>
</div>

<!-- Affichage des annonces -->
<div class="annonces-container">
    <?php if (@mysqli_num_rows($result_annonces) > 0): ?>
        <?php while ($annonce = mysqli_fetch_assoc($result_annonces)): ?>
            <div class="annonce-card">
                <?php if (!empty($annonce['photo'])): ?>
                    <div class="annonce-image">
                        <img src="<?php echo 'assets/uploads/' . $annonce['photo']; ?>" alt="<?php echo htmlspecialchars($annonce['titre']); ?>">
                    </div>
                <?php endif; ?>
                <div class="annonce-details">
                    <h3><?php echo htmlspecialchars($annonce['titre']); ?></h3>
                    <p class="prix"><?php echo $annonce['prix']; ?> €</p>
                    <p class="categorie">Catégorie: <?php echo htmlspecialchars($annonce['categorie']); ?></p>
                    <p class="vendeur">Vendeur: <?php echo htmlspecialchars($annonce['prenom'] . ' ' . $annonce['nom']); ?></p>
                    <a href="<?php echo 'voir_annonce.php?id=' . $annonce['id']; ?>" class="btn">Voir plus</a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="aucune-annonce">Aucune annonce disponible.</p>
    <?php endif; ?>
</div>

<!-- Lien vers toutes les annonces -->
<div class="voir-plus">
    <a href="liste_annonces.php" class="btn">Voir toutes les annonces</a>
</div>

<?php mysqli_close($conn); ?>
<?php require_once 'footer.php'; ?>
