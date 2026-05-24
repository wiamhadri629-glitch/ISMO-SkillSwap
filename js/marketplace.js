/* ===== Marketplace - Edit Own Posts ===== */

const user = window.currentUser || { initials: 'JS', name: 'John Student' };

document.querySelectorAll('.market-card[data-author]').forEach(card => {
  if (card.dataset.author !== user.initials) return;
  const footer = card.querySelector('.card-footer');
  if (!footer.querySelector('.btn-edit')) {
    const btn = document.createElement('button');
    btn.className = 'btn-edit';
    btn.innerHTML = '<i class="fas fa-pen"></i>';
    btn.onclick = () => editPost(btn);
    footer.appendChild(btn);
  }
});

function editPost(btn) {
  const card = btn.closest('.market-card');
  const titleEl = card.querySelector('.card-title');
  const descEl = card.querySelector('.card-copy');

  if (card.classList.contains('editing')) {
    const titleInput = card.querySelector('.edit-title');
    const descInput = card.querySelector('.edit-desc');
    if (titleInput && descInput) {
      titleEl.textContent = titleInput.value;
      descEl.textContent = descInput.value;
    }
    card.classList.remove('editing');
    btn.innerHTML = '<i class="fas fa-pen"></i>';
    return;
  }

  const titleVal = titleEl.textContent;
  const descVal = descEl.textContent;

  titleEl.innerHTML = `<input class="edit-title" value="${titleVal.replace(/"/g,'&quot;')}" style="width:100%;padding:8px;border:1px solid #d0d5dd;border-radius:6px;font-size:14px;font-weight:600;color:#222">`;
  descEl.innerHTML = `<textarea class="edit-desc" rows="2" style="width:100%;padding:8px;border:1px solid #d0d5dd;border-radius:6px;font-size:14px;margin-top:6px;resize:vertical">${descVal}</textarea>`;

  card.classList.add('editing');
  btn.innerHTML = '<i class="fas fa-check"></i>';
}
