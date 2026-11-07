<?php
session_start();
if(!isset($_SESSION['id_user'])){ header('Location: /phpprojet/login/index.php'); exit; }
$id_user = $_SESSION['id_user'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: competences.php');
    exit;
}
extract($_POST);

require_once __DIR__ . '/../../dbopen/db.php';

$id_competence = (int)($id_competence ?? 0);
$niveau = $niveau ?? '';

if ($id_competence <= 0 || !in_array($niveau, ['debutant', 'intermediaire', 'avance'])) {
    header('Location: competences.php');
    exit;
}

$stmt = $db->prepare("SELECT id FROM user_competences WHERE id_user = ? AND id_competence = ?");
$stmt->execute([$id_user, $id_competence]);
if ($stmt->fetch()) {
    header('Location: competences.php');
    exit;
}

$stmt = $db->prepare("INSERT INTO user_competences (id_user, id_competence, niveau) VALUES (?, ?, ?)");
$stmt->execute([$id_user, $id_competence, $niveau]);

header('Location: competences.php');
exit;
