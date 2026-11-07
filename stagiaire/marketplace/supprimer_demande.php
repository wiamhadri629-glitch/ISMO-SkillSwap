<?php
session_start();
if(!isset($_SESSION['id_user'])){ header('Location: /phpprojet/login/index.php'); exit; }
extract($_SESSION);

$id_demande = 0;
if (isset($_GET['id_demande'])) { $id_demande = (int)$_GET['id_demande']; }
elseif (isset($_POST['id_demande'])) { $id_demande = (int)$_POST['id_demande']; }

if ($id_demande <= 0) {
    header("Location: marketplace.php");
    exit;
}

require_once __DIR__ . '/../../dbopen/db.php';

$stmt = $db->prepare("SELECT id_user FROM demandes_aide WHERE id_demande = ?");
$stmt->execute([$id_demande]);
$demande = $stmt->fetch();

if (!$demande || $demande['id_user'] != $id_user) {
    header("Location: marketplace.php");
    exit;
}

$stmt = $db->prepare("DELETE FROM propositions_aide WHERE id_demande = ?");
$stmt->execute([$id_demande]);

$stmt = $db->prepare("DELETE FROM aide_effectuee WHERE id_demande = ?");
$stmt->execute([$id_demande]);

$stmt = $db->prepare("DELETE FROM demandes_aide WHERE id_demande = ?");
$stmt->execute([$id_demande]);

header("Location: marketplace.php");
exit;
