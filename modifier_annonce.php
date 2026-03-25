<?php
require_once 'config.php';
require_once 'functions.php';
$title = "Modifier une Annonce";
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
    echo "<p class='error'>Vous n'avez pas la permission de modifier cette annonce.</p>";
    require_once 'includes/footer.php';
    exit();
}

$annonce = mysqli_fetch_assoc($result);

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = mysqli_real_escape_string($conn, $_POST['titre']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $prix = (float)$_POST['prix'];
    $id_categorie = (int)$_POST['id_categorie'];

    // Gestion de la photo (si une nouvelle photo est uploadée)
    $photo = $annonce['photo'];
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        if (!empty($annonce['photo']) && file_exists('assets/uploads/' . $annonce['photo'])) {
            unlink('assets/uploads/' . $annonce['photo']);
        }
        $dossier = 'assets/uploads/';
        $nom_fichier = preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', basename($_FILES['photo']['name']));
        $photo = uniqid() . '_' . $nom_fichier;
        move_uploaded_file($_FILES['photo']['tmp_name'], $dossier . $photo);
    }

    $sql_update = "UPDATE annonces SET
                   titre = '$titre',
                   description = '$description',
                   prix = $prix,
                   id_categorie = $id_categorie,
                   photo = '$photo'
                   WHERE id = $id_annonce";

    if (mysqli_query($conn, $sql_update)) {
        echo "<p class='success'>Annonce modifiée avec succès !</p>";
        rediriger('voir_annonce.php?id=' . $id_annonce);
    } else {
        echo "<p class='error'>Erreur : " . mysqli_error($conn) . "</p>";
    }
}
?>

<h1>Modifier une Annonce</h1>
<form method="POST" enctype="multipart/form-data" class="form">
    <div class="form-group">
        <label for="titre">Titre :</label>
        <input type="text" id="titre" name="titre" value="<?php echo htmlspecialchars($annonce['titre']); ?>" required>
    </div>
    <div class="form-group">
        <label for="description">Description :</label>
        <textarea id="description" name="description" required><?php echo htmlspecialchars($annonce['description']); ?></textarea>
    </div>
    <div class="form-group">
        <label for="prix">Prix (€) :</label>
        <input type="number" id="prix" name="prix" step="0.01" value="<?php echo $annonce['prix']; ?>" required>
    </div>
    <div class="form-group">
        <label for="id_categorie">Catégorie :</label>
        <select id="id_categorie" name="id_categorie" required>
            <?php
            $result_categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY nom ASC");
            while ($categorie = mysqli_fetch_assoc($result_categories)) {
                $selected = ($categorie['id'] == $annonce['id_categorie']) ? 'selected' : '';
                echo '<option value="' . $categorie['id'] . '" ' . $selected . '>' . htmlspecialchars($categorie['nom']) . '</option>';
            }
            ?>
        </select>
    </div>
    <div class="form-group">
        <label for="photo">Photo (laisser vide pour garder l'actuelle) :</label>
        <input type="file" id="photo" name="photo" accept="image/*">
        <?php if (!empty($annonce['photo'])): ?>
            <p>Photo actuelle :</p>
            <img src="<?php echo 'assets/uploads/' . $annonce['photo']; ?>" alt="Photo actuelle" width="200">
        <?php endif; ?>
    </div>
    <button type="submit" class="btn">Modifier</button>
</form>

<?php mysqli_close($conn); ?>
<?php require_once 'footer.php'; ?>
