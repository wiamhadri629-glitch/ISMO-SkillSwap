<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin - Paramètres</title>
  <link rel="stylesheet" href="../../css/styles.css">
  <link rel="stylesheet" href="parametres.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <div class="container">
                <aside class="sidebar sidebar-admin">
      <div class="sidebar-header">
        <div class="sidebar-logo">
          <i class="fas fa-graduation-cap"></i>
        </div>
        <div>
          <h1>ISMO-SkillSwap</h1>
          <p>ISMO Tétouan</p>
        </div>
      </div>
      <nav class="sidebar-nav">
        <a href="../accueil/accueil.php" class="nav-item">
          <i class="fas fa-home"></i>
          <span>Accueil</span>
        </a>
        <a href="../marketplace/marketplace.php" class="nav-item">
          <i class="fas fa-store"></i>
          <span>Marketplace</span>
        </a>
        <a href="../profile/profile.php" class="nav-item">
          <i class="fas fa-user"></i>
          <span>Profil</span>
        </a>
        <a href="../passport/passport.php" class="nav-item">
          <i class="fas fa-shield"></i>
          <span>Modération</span>
        </a>
        <a href="../classement/classement.php" class="nav-item">
          <i class="fas fa-ranking-star"></i>
          <span>Classement</span>
        </a>
        <a href="../validation/validations.php" class="nav-item">
          <i class="fas fa-check-circle"></i>
          <span>Validations</span>
        </a>
        <a href="../badge/badges.php" class="nav-item">
          <i class="fas fa-trophy"></i>
          <span>Badges</span>
        </a>
        <a href="../parametre/parametres.php" class="nav-item active">
          <i class="fas fa-cog"></i>
          <span>Paramètres</span>
        </a>
      </nav>
      <div class="sidebar-footer">
        <div class="user-info">
          <div class="avatar">MA</div>
          <div>
            <p class="user-name">Mohammed Admin</p>
            <p class="user-role">Admin</p>
          </div>
        </div>
      </div>
    </aside>

        <header class="top-header">
      <div class="search-bar">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Rechercher...">
      </div>
      <div class="header-actions">
        <button class="notification-btn">
          <i class="fas fa-bell"></i>
          <span class="notification-badge">3</span>
        </button>
        <div class="user-menu" id="profileToggle">
          <div class="avatar-large">MA</div>
          <div>
            <p class="user-name">Mohammed Admin</p>
            <p class="user-role">Admin</p>
          </div>
          <i class="fas fa-chevron-down" id="chevronIcon"></i>
        </div>
      </div>

      <!-- Profile Dropdown -->
      <div class="profile-dropdown" id="profileMenu">
        <div class="dropdown-header">
          <div class="dropdown-avatar">MA</div>
          <div class="dropdown-user-info">
            <h4>Mohammed Admin</h4>
            <p>Platform Admin</p>
            <span class="role-badge role-badge-admin">Admin</span>
          </div>
        </div>
        <div class="dropdown-meta">
          <div class="meta-item"><i class="fas fa-envelope"></i> admin@ismo.com</div>
          <div class="meta-item"><i class="fas fa-calendar"></i> Joined 1/1/2022</div>
          <div class="meta-item"><i class="fas fa-chart-line"></i> 8,500 Reputation Points</div>
          <div class="meta-item"><i class="fas fa-medal"></i> 60 Skills Validated</div>
        </div>
        <div class="dropdown-actions">
          <a href="../passport/passport.php" class="dropdown-action">
            <i class="fas fa-shield"></i> Modération
          </a>
          <a href="../profile/profile.php" class="dropdown-action">
            <i class="fas fa-user"></i> View Profile
          </a>
          <a href="../parametre/parametres.php" class="dropdown-action">
            <i class="fas fa-gear"></i> Settings
          </a>
        </div>
        <div class="dropdown-signout">
          <a href="../../login/index.php" class="dropdown-action signout">
            <i class="fas fa-right-from-bracket"></i> Sign Out
          </a>
        </div>
      </div>
    </header>

    <main class="main-content">

      <section class="profile-container">
        <h2>Paramètres de la Plateforme</h2>
        <p class="subtitle">Configurez les paramètres globaux de ISMO-SkillSwap</p>

        <div class="settings-grid">
          <div class="settings-card">
            <div class="settings-header"><i class="fas fa-sliders-h"></i><h3>Paramètres Généraux</h3></div>
            <div class="settings-form">
              <div class="form-group">
                <label>Nom de la plateforme</label>
                <input type="text" value="ISMO-SkillSwap" class="form-input">
              </div>
              <div class="form-group">
                <label>Description</label>
                <textarea class="form-textarea">Plateforme d'entraide pour les stagiaires de l'ISMO Tétouan</textarea>
              </div>
              <div class="form-group checkbox">
                <input type="checkbox" checked> Inscription publique
              </div>
              <div class="form-group checkbox">
                <input type="checkbox"> Validation manuelle des comptes
              </div>
            </div>
          </div>

          <div class="settings-card">
            <div class="settings-header"><i class="fas fa-bell"></i><h3>Notifications</h3></div>
            <div class="settings-form">
              <div class="form-group toggle">
                <label>Email de bienvenue</label>
                <input type="checkbox" class="toggle-input" checked>
              </div>
              <div class="form-group toggle">
                <label>Notifications de validation</label>
                <input type="checkbox" class="toggle-input" checked>
              </div>
              <div class="form-group toggle">
                <label>Alertes de modération</label>
                <input type="checkbox" class="toggle-input" checked>
              </div>
            </div>
          </div>

          <div class="settings-card full-width">
            <div class="settings-header"><i class="fas fa-lock"></i><h3>Sécurité et Permissions</h3></div>
            <div class="settings-form">
              <div class="form-group">
                <label>Durée de session (minutes)</label>
                <input type="number" value="60" class="form-input">
              </div>
              <div class="form-group toggle">
                <label>Authentification à deux facteurs</label>
                <input type="checkbox" class="toggle-input">
              </div>
              <div class="form-group toggle">
                <label>Logs d'activité</label>
                <input type="checkbox" class="toggle-input" checked>
              </div>
            </div>
          </div>

          <div class="settings-card full-width">
            <div class="settings-header"><i class="fas fa-database"></i><h3>Base de Données</h3></div>
            <div class="settings-info">
              <div class="info-row"><span>Dernière sauvegarde</span><strong>Il y a 2 heures</strong></div>
              <div class="info-row"><span>Espace utilisé</span><strong>42.8 GB / 100 GB</strong></div>
              <button class="btn primary-btn">Effectuer une sauvegarde</button>
            </div>
          </div>
        </div>

        <div class="settings-actions">
          <button class="btn secondary-btn">Annuler</button>
          <button class="btn primary-btn">Enregistrer les modifications</button>
        </div>

      </section>
    </main>
  </div>
  <script>
    const profileToggle = document.getElementById('profileToggle');
    const profileMenu   = document.getElementById('profileMenu');
    const chevronIcon   = document.getElementById('chevronIcon');
    profileToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      profileMenu.classList.toggle('open');
      chevronIcon.classList.toggle('rotated');
    });
    document.addEventListener('click', (e) => {
      if (!profileToggle.contains(e.target) && !profileMenu.contains(e.target)) {
        profileMenu.classList.remove('open');
        chevronIcon.classList.remove('rotated');
      }
    });
  </script>
</body>
</html>