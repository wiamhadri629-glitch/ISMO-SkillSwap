<!doctype html>
<html lang="fr">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ISMO-SkillSwap - Passport</title>
  <link rel="stylesheet" href="../../css/styles.css">
  <link rel="stylesheet" href="passport.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
  <div class="container">
                <aside class="sidebar sidebar-formateur">
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
        <a href="../passport/passport.php" class="nav-item active">
          <i class="fas fa-passport"></i>
          <span>Passport</span>
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
      </nav>
      <div class="sidebar-footer">
        <div class="user-info">
          <div class="avatar">MF</div>
          <div>
            <p class="user-name">Marc Formateur</p>
            <p class="user-role">Formateur</p>
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
          <div class="avatar-large">MF</div>
          <div>
            <p class="user-name">Marc Formateur</p>
            <p class="user-role">Formateur</p>
          </div>
          <i class="fas fa-chevron-down" id="chevronIcon"></i>
        </div>
      </div>

      <!-- Profile Dropdown -->
      <div class="profile-dropdown" id="profileMenu">
        <div class="dropdown-header">
          <div class="dropdown-avatar">MF</div>
          <div class="dropdown-user-info">
            <h4>Marc Formateur</h4>
            <p>Platform Formateur</p>
            <span class="role-badge role-badge-formateur">Formateur</span>
          </div>
        </div>
        <div class="dropdown-meta">
          <div class="meta-item"><i class="fas fa-envelope"></i> formateur@ismo.com</div>
          <div class="meta-item"><i class="fas fa-calendar"></i> Joined 3/9/2022</div>
          <div class="meta-item"><i class="fas fa-chart-line"></i> 4,200 Reputation Points</div>
          <div class="meta-item"><i class="fas fa-medal"></i> 35 Skills Validated</div>
        </div>
        <div class="dropdown-actions">
          <a href="../passport/passport.php" class="dropdown-action">
            <i class="fas fa-download"></i> Download Skills Passport (PDF)
          </a>
          <a href="../profile/profile.php" class="dropdown-action">
            <i class="fas fa-user"></i> View Profile
          </a>
          <a href="../profile/profile.php" class="dropdown-action">
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

    <main class="main-content passport-page">
      <section class="page-top">
        <div>
          <p class="eyebrow">Skills Passport</p>
          <h1>Votre portfolio de compétences validées</h1>
          <p class="page-copy">Visualisez vos compétences techniques certifiées et découvrez les actions pour progresser encore plus.</p>
        </div>
        <button class="btn primary download-btn">
          <i class="fas fa-download"></i>
          Download PDF
        </button>
      </section>

      <section class="passport-summary-card">
        <div class="passport-row">
          <div class="passport-profile">
            <div class="avatar-hero">JS</div>
            <div>
              <h2>John Student</h2>
              <p>Computer Science Student</p>
              <p class="validated-skills">12 Validated Skills</p>
            </div>
          </div>
        </div>
      </section>

      <section class="passport-promo-card">
        <div>
          <h2>Level Up Your Skills</h2>
          <p>Complete more help requests to validate new skills and increase your levels.</p>
        </div>
        <button class="btn primary">Browse Opportunities</button>
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
