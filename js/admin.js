document.addEventListener('DOMContentLoaded', function () {

    // Recherche live dans la liste des voitures
    const searchInput = document.getElementById('searchVoiture');
    const tbody       = document.getElementById('voituresTableBody');

    if (searchInput && tbody) {
        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            const rows  = tbody.getElementsByTagName('tr');
            for (let i = 0; i < rows.length; i++) {
                rows[i].style.display = rows[i].textContent.toLowerCase().includes(query) ? '' : 'none';
            }
        });
    }

    // Confirmation avant suppression
    const boutonsSupprimer = document.querySelectorAll('[data-confirm]');
    for (const btn of boutonsSupprimer) {
        btn.addEventListener('click', function (e) {
            if (!confirm(this.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    }

    // Badge couleur selon nombre de réservations en attente
    const badge = document.querySelector('.badge-count');
    if (badge) {
        const nb = parseInt(badge.textContent);
        if (nb === 0)      badge.style.background = '#10b981';
        else if (nb <= 3)  badge.style.background = '#f59e0b';
        else               badge.style.background = '#ef4444';
    }

    // Horloge en temps réel
    const horloge = document.getElementById('horloge');
    if (horloge) {
        const mettreAJourHeure = () => {
            const d = new Date();
            const h = String(d.getHours()).padStart(2, '0');
            const m = String(d.getMinutes()).padStart(2, '0');
            const s = String(d.getSeconds()).padStart(2, '0');
            horloge.innerHTML = h + ':' + m + ':' + s;
        };
        mettreAJourHeure();
        setInterval(mettreAJourHeure, 1000);
    }
});
