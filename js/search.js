document.getElementById('globalSearch')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && this.value.trim()) {
        window.location.href = window.location.pathname + '?search=' + encodeURIComponent(this.value);
    }
});
