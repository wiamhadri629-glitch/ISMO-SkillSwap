<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: /phpprojet/login/index.php');
    exit;
}
$page_title = 'Historique des Aides';
$page_css = 'marketplace.css';

require_once __DIR__ . '/../../includes/header.php';

$id_user = $_SESSION['id_user'];

$demandes = $db->prepare("
    SELECT d.*, ae.note_mentor, ae.commentaire, ae.date_intervention,
           m.nom AS mentor_nom, m.prenom AS mentor_prenom
    FROM demandes_aide d
    LEFT JOIN aide_effectuee ae ON d.id_demande = ae.id_demande
    LEFT JOIN utilisateurs m ON ae.id_mentor = m.id_user
    WHERE d.id_user = ? AND d.status = 'resolue'
    ORDER BY COALESCE(ae.date_intervention, d.date_pub) DESC
");
$demandes->execute([$id_user]);
$demandes = $demandes->fetchAll();

$aides = $db->prepare("SELECT a.*, u.nom, u.prenom FROM aides a JOIN utilisateurs u ON a.id_formateur = u.id_user WHERE a.id_stagiaire_validateur = ? AND a.statut = 'validee' ORDER BY a.date_validation DESC");
$aides->execute([$id_user]);
$aides = $aides->fetchAll();

$has_entries = count($demandes) > 0 || count($aides) > 0;
?>
<div class="topbar-actions">
    <div class="topbar-left">
        <h1 class="section-title">Historique des Aides</h1>
        <a href="marketplace.php" class="btn secondary" style="text-decoration:none;"><i class="fas fa-arrow-left"></i> Retour au Marketplace</a>
    </div>
</div>

<?php if (!$has_entries): ?>
<div style="text-align:center;padding:60px 20px;color:#64748b;">
    <i class="fas fa-clock-rotate-left" style="font-size:48px;margin-bottom:16px;opacity:0.5;"></i>
    <p style="font-size:18px;">Aucun historique pour le moment.</p>
    <p style="font-size:14px;">Vos demandes résolues et aides validées apparaîtront ici.</p>
</div>
<?php endif; ?>

<?php if (count($demandes) > 0): ?>
<div class="aides-section">
    <h2 class="section-subtitle"><i class="fas fa-circle-check"></i> Demandes résolues <span class="badge-count"><?= count($demandes) ?></span></h2>
    <div class="market-grid">
        <?php foreach ($demandes as $d):
            $date = $d['date_intervention'] ?: $d['date_pub'];
            $author_init = strtoupper(substr($d['mentor_prenom'] ?? '', 0, 1) . substr($d['mentor_nom'] ?? '', 0, 1));
        ?>
        <article class="market-card">
            <div class="card-head">
                <div>
                    <p class="status-pill resolved">Résolue</p>
                    <h2 class="card-title"><?= htmlspecialchars($d['titre']) ?></h2>
                </div>
            </div>
            <p class="card-copy"><?= htmlspecialchars(mb_substr($d['description'], 0, 200)) ?></p>
            <div style="display:flex;flex-wrap:wrap;gap:16px;font-size:13px;color:#64748b;">
                <span><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($date)) ?></span>
                <?php if ($d['note_mentor']): ?>
                <span><i class="fas fa-star" style="color:#f59e0b;"></i> Note: <?= $d['note_mentor'] ?>/5</span>
                <?php endif; ?>
            </div>
            <?php if ($d['commentaire']): ?>
            <div style="background:#f8fafc;padding:12px 16px;border-radius:12px;font-size:13px;color:#475569;border:1px solid #e5e7eb;">
                <i class="fas fa-quote-left" style="color:#94a3b8;margin-right:6px;"></i><?= htmlspecialchars($d['commentaire']) ?>
            </div>
            <?php endif; ?>
            <?php if ($d['mentor_nom']): ?>
            <div class="card-footer" style="border-top:1px solid #f1f5f9;padding-top:14px;">
                <div class="author">
                    <span class="avatar-sm" style="background:#8b5cf6;"><?= htmlspecialchars($author_init !== '' ? $author_init : 'Inconnu') ?></span>
                    <div>
                        <strong><?= htmlspecialchars($d['mentor_prenom'] . ' ' . $d['mentor_nom']) ?></strong>
                        <small>Mentor</small>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </article>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if (count($aides) > 0): ?>
<div class="aides-section">
    <h2 class="section-subtitle"><i class="fas fa-check-circle"></i> Aides que j'ai validées <span class="badge-count"><?= count($aides) ?></span></h2>
    <div class="market-grid">
        <?php foreach ($aides as $a):
            $creator_init = strtoupper(substr($a['prenom'], 0, 1) . substr($a['nom'], 0, 1));
        ?>
        <article class="market-card">
            <div class="card-head">
                <div>
                    <p class="status-pill validee">Validée</p>
                    <h2 class="card-title"><?= htmlspecialchars($a['titre'] ?? 'Sans titre') ?></h2>
                </div>
            </div>
            <p class="card-copy"><?= htmlspecialchars($a['description'] ?? '') ?></p>
            <?php if ($a['explication']): ?>
            <div style="background:#f0fdf4;color:#166534;padding:12px 16px;border-radius:10px;font-size:13px;">
                <i class="fas fa-quote-left"></i> <?= htmlspecialchars($a['explication']) ?>
            </div>
            <?php endif; ?>
            <div class="card-footer" style="border-top:1px solid #f1f5f9;padding-top:14px;">
                <div class="author">
                    <span class="avatar-sm" style="background:var(--purple);"><?= htmlspecialchars($creator_init) ?></span>
                    <div>
                        <strong><?= htmlspecialchars($a['prenom'] . ' ' . $a['nom']) ?></strong>
                        <small>Validée le <?= date('d/m/Y', strtotime($a['date_validation'])) ?></small>
                    </div>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
