<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: /phpprojet/login/index.php');
    exit;
}
$page_title = 'Publier une demande';
$page_css = 'marketplace.css';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['publier'])) {
    extract($_POST);
    require_once __DIR__ . '/../../dbopen/db.php';
    $id_user = $_SESSION['id_user'];

    $titre = trim($titre ?? '');
    $description = trim($description ?? '');

    if (empty($titre)) {
        $error = 'Le titre est obligatoire.';
    } elseif (empty($description)) {
        $error = 'La description est obligatoire.';
    } else {
        $stmt = $db->prepare("INSERT INTO demandes_aide (id_user, titre, description, status, date_pub) VALUES (?, ?, ?, 'ouverte', NOW())");
        $stmt->execute([$id_user, $titre, $description]);
        header("Location: marketplace.php");
        exit;
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>
<div class="market-card header-card">
    <p class="eyebrow">Marketplace</p>
    <h1>Publier une demande d'aide</h1>
    <p class="card-copy">Décrivez votre problème afin que les autres stagiaires ou formateurs puissent vous aider.</p>
</div>

<?php if ($error): ?>
<div style="background:#fef2f2;color:#991b1b;padding:14px 20px;border-radius:12px;margin-bottom:18px;font-weight:600;"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="market-card form-card">
    <form method="POST">
        <div class="form-group">
            <label>Titre du problème</label>
            <input type="text" name="titre" placeholder="Ex : Problème avec les jointures SQL" required>
        </div>
        <div class="form-group">
            <label>Description détaillée</label>
            <textarea name="description" rows="8" placeholder="Expliquez votre problème..." required></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" name="publier" class="btn primary full">
                <i class="fas fa-paper-plane"></i> Publier la demande
            </button>
        </div>
    </form>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
