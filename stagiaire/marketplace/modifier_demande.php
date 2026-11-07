<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: /phpprojet/login/index.php');
    exit;
}
$page_title = 'Modifier la demande';
$page_css = 'marketplace.css';

$id_demande = 0;
if (isset($_GET['id_demande'])) { $id_demande = (int)$_GET['id_demande']; }
elseif (isset($_POST['id_demande'])) { $id_demande = (int)$_POST['id_demande']; }
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier'])) {
    extract($_POST);
    require_once __DIR__ . '/../../dbopen/db.php';
    $id_user = $_SESSION['id_user'];

    $titre = trim($titre ?? '');
    $description = trim($description ?? '');
    $status = trim($status ?? 'ouverte');

    if (empty($titre)) {
        $error = 'Le titre est obligatoire.';
    } elseif (empty($description)) {
        $error = 'La description est obligatoire.';
    } else {
        $stmt = $db->prepare("UPDATE demandes_aide SET titre = ?, description = ?, status = ? WHERE id_demande = ?");
        $stmt->execute([$titre, $description, $status, $id_demande]);
        header("Location: marketplace.php");
        exit;
    }
}

require_once __DIR__ . '/../../includes/header.php';

$id_user = $_SESSION['id_user'];

$stmt = $db->prepare("SELECT * FROM demandes_aide WHERE id_demande = ?");
$stmt->execute([$id_demande]);
$demande = $stmt->fetch();

if (!$demande) {
    header("Location: marketplace.php");
    exit;
}

$stmt_helper = $db->prepare("SELECT id FROM propositions_aide WHERE id_demande = ? AND id_mentor = ? AND status = 'acceptee'");
$stmt_helper->execute([$id_demande, $id_user]);
$is_helper = (bool)$stmt_helper->fetch();

if ($demande['id_user'] != $id_user && !$is_helper) {
    header("Location: marketplace.php");
    exit;
}
?>
<div class="market-card header-card">
    <p class="eyebrow">Marketplace</p>
    <h1>Modifier la demande</h1>
</div>

<?php if ($error): ?>
<div style="background:#fef2f2;color:#991b1b;padding:14px 20px;border-radius:12px;margin-bottom:18px;font-weight:600;"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="market-card form-card">
    <form method="POST">
        <input type="hidden" name="id_demande" value="<?= $id_demande ?>">
        <div class="form-group">
            <label>Titre</label>
            <input type="text" name="titre" value="<?= htmlspecialchars($demande['titre']) ?>" required>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="8" required><?= htmlspecialchars($demande['description']) ?></textarea>
        </div>
        <div class="form-group">
            <label>Statut</label>
            <select name="status">
                <option value="ouverte" <?= $demande['status'] === 'ouverte' ? 'selected' : '' ?>>Ouvert</option>
                <option value="en_cours" <?= $demande['status'] === 'en_cours' ? 'selected' : '' ?>>En cours</option>
                <option value="resolue" <?= $demande['status'] === 'resolue' ? 'selected' : '' ?>>Résolu</option>
            </select>
        </div>
        <div class="form-actions" style="display:flex;gap:12px;margin-top:24px;">
            <button type="submit" name="modifier" class="btn primary">
                <i class="fas fa-save"></i> Enregistrer
            </button>
            <a href="marketplace.php" class="btn secondary" style="padding:14px 22px;background:#f3f4f6;color:#333;border-radius:14px;font-weight:600;text-decoration:none;">Annuler</a>
        </div>
    </form>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
