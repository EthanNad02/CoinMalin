<?php
require_once 'config.php';

// Démarre la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Connexion à la base de données
function connect_db() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$conn) {
        die("Erreur de connexion à MySQL : " . mysqli_connect_error());
    }
        mysqli_set_charset($conn, 'utf8mb4');  // ← Ajoute cette ligne
    return $conn;
  
}

// Vérifie si l'utilisateur est connecté
function est_connecte() {
    return isset($_SESSION['user_id']);
}

// Redirige vers une page
function rediriger($path) {
    header("Location: " . $path);
    exit();
}

// Récupère les informations d'un utilisateur
function get_utilisateur($id) {
    $conn = connect_db();
    $sql = "SELECT * FROM utilisateurs WHERE id = $id";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($result);
}

// Vérifie si une annonce est dans les favoris
function est_favoris($id_utilisateur, $id_annonce) {
    $conn = connect_db();
    $sql = "SELECT COUNT(*) FROM favoris WHERE id_utilisateur = $id_utilisateur AND id_annonce = $id_annonce";
    $result = mysqli_query($conn, $sql);
    $count = mysqli_fetch_array($result)[0];
    return $count > 0;
}

// Ajoute une annonce aux favoris
function ajouter_favoris($id_utilisateur, $id_annonce) {
    $conn = connect_db();
    $sql = "INSERT INTO favoris (id_utilisateur, id_annonce) VALUES ($id_utilisateur, $id_annonce)";
    return mysqli_query($conn, $sql);
}

// Retire une annonce des favoris
function retirer_favoris($id_utilisateur, $id_annonce) {
    $conn = connect_db();
    $sql = "DELETE FROM favoris WHERE id_utilisateur = $id_utilisateur AND id_annonce = $id_annonce";
    return mysqli_query($conn, $sql);
}

// Récupère les favoris d'un utilisateur
function get_favoris($id_utilisateur) {
    $conn = connect_db();
    $sql = "SELECT a.* FROM annonces a
            JOIN favoris f ON a.id = f.id_annonce
            WHERE f.id_utilisateur = $id_utilisateur
            ORDER BY f.date_ajout DESC";
    $result = mysqli_query($conn, $sql);
    $favoris = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $favoris[] = $row;
    }
    return $favoris;
}

// Récupère les messages d'un utilisateur
function get_messages($id_utilisateur) {
    $conn = connect_db();

    // Messages reçus
    $sql_recus = "SELECT m.*, u.nom, u.prenom, a.titre FROM messages m
                  JOIN utilisateurs u ON m.id_expediteur = u.id
                  LEFT JOIN annonces a ON m.id_annonce = a.id
                  WHERE m.id_destinataire = $id_utilisateur
                  ORDER BY m.date_envoi DESC";
    $result_recus = mysqli_query($conn, $sql_recus);
    $messages_recus = [];
    while ($row = mysqli_fetch_assoc($result_recus)) {
        $messages_recus[] = $row;
    }

    // Messages envoyés
    $sql_envoyes = "SELECT m.*, u.nom, u.prenom, a.titre FROM messages m
                    JOIN utilisateurs u ON m.id_destinataire = u.id
                    LEFT JOIN annonces a ON m.id_annonce = a.id
                    WHERE m.id_expediteur = $id_utilisateur
                    ORDER BY m.date_envoi DESC";
    $result_envoyes = mysqli_query($conn, $sql_envoyes);
    $messages_envoyes = [];
    while ($row = mysqli_fetch_assoc($result_envoyes)) {
        $messages_envoyes[] = $row;
    }

    return [
        'recus' => $messages_recus,
        'envoyes' => $messages_envoyes
    ];
}