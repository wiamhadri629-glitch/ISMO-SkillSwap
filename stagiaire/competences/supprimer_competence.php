<?php
session_start();
if(!isset($_SESSION['id_user'])){ header('Location: /phpprojet/login/index.php'); exit; }
extract($_SESSION);
$id = 0;
if (isset($_GET['id'])) { $id = (int)$_GET['id']; }
elseif (isset($_POST['id'])) { $id = (int)$_POST['id']; }

if ($id <= 0) {
    header('Location: competences.php');
    exit;
}

require_once __DIR__ . '/../../dbopen/db.php';

$stmt = $db->prepare("SELECT id FROM user_competences WHERE id = ? AND id_user = ?");
$stmt->execute([$id, $id_user]);
if (!$stmt->fetch()) {
    header('Location: competences.php');
    exit;
}

$stmt = $db->prepare("DELETE FROM validation_competence WHERE id_user_competence = ?");
$stmt->execute([$id]);

$stmt = $db->prepare("DELETE FROM user_competences WHERE id = ?");
$stmt->execute([$id]);

header('Location: competences.php');
exit;
