<?php
require_once 'config.php';
require_once 'functions.php';
$title = "Ajouter une Annonce";
require_once 'header.php';

if (!est_connecte()) {
    rediriger('connexion.php');
}

$conn = connect_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = mysqli_real_escape_string($conn, $_POST['titre']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $prix = (float)$_POST['prix'];
    $id_categorie = (int)$_POST['id_categorie'];
    $id_utilisateur = $_SESSION['user_id'];

    // Gestion de l'upload de photo
    $photo = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $dossier = 'assets/uploads/';
        if (!file_exists($dossier)) {
            mkdir($dossier, 0777, true);
        }
        $nom_fichier = preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', basename($_FILES['photo']['name']));
        $photo = uniqid() . '_' . $nom_fichier;
        move_uploaded_file($_FILES['photo']['tmp_name'], $dossier . $photo);
    }

    $sql = "INSERT INTO annonces (titre, description, prix, id_utilisateur, id_categorie, photo)
            VALUES ('$titre', '$description', $prix, $id_utilisateur, $id_categorie, '$photo')";

    if (mysqli_query($conn, $sql)) {
        echo "<p class='success'>Annonce ajoutée avec succès !</p>";
        rediriger('liste_annonces.php');
    } else {
        echo "<p class='error'>Erreur : " . mysqli_error($conn) . "</p>";
    }
}
?>

<h1>Ajouter une Annonce</h1>
<form method="POST" enctype="multipart/form-data" class="form">
    <div class="form-group">
        <label for="titre">Titre :</label>
        <input type="text" id="titre" name="titre" required>
    </div>
    <div class="form-group">
        <label for="description">Description :</label>
        <textarea id="description" name="description" required></textarea>
    </div>
    <div class="form-group">
        <label for="prix">Prix (€) :</label>
        <input type="number" id="prix" name="prix" step="0.01" required>
    </div>
    <div class="form-group">
        <label for="id_categorie">Catégorie :</label>
        <select id="id_categorie" name="id_categorie" required>
            <option value="">-- Sélectionnez une catégorie --</option>
            <?php
            $result_categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY nom ASC");
            while ($categorie = mysqli_fetch_assoc($result_categories)) {
                echo '<option value="' . $categorie['id'] . '">' . htmlspecialchars($categorie['nom']) . '</option>';
            }
            ?>
        </select>
    </div>
    <div class="form-group">
        <label for="photo">Photo :</label>
        <input type="file" id="photo" name="photo" accept="image/*">
    </div>
    <button type="submit" class="btn">Ajouter</button>
</form>

<?php mysqli_close($conn); ?>
<?php require_once 'footer.php'; ?>
