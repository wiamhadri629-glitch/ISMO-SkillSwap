<?php
session_start();
require_once __DIR__ . '/../../dbopen/db.php';
if(!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin'){
    header('Location: /phpprojet/login/index.php');
    exit;
}
extract($_SESSION);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_user'], $_POST['action'])) {
    extract($_POST);
    $id_user = (int)$id_user;
    $action = isset($action) ? $action : '';
    if ($action === 'valider') {
        $stmt = $db->prepare("UPDATE utilisateurs SET valide = 1 WHERE id_user = ?");
        $stmt->execute([$id_user]);
    } elseif ($action === 'suspendre') {
        $stmt = $db->prepare("UPDATE utilisateurs SET valide = 0 WHERE id_user = ?");
        $stmt->execute([$id_user]);
    }
}

header('Location: utilisateurs.php');
exit;
