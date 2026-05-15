document.addEventListener('DOMContentLoaded', function () {

    const inputDebut  = document.getElementById('date_debut');
    const inputFin    = document.getElementById('date_fin');
    const prixBox     = document.getElementById('prixBox');
    const nbJoursEl   = document.getElementById('nbJours');
    const prixTotalEl = document.getElementById('prixTotal');

    if (!inputDebut || !inputFin || typeof prixJour === 'undefined') return;

    // Calcul du nombre de jours entre deux dates
    const calculerJours = (debut, fin) => {
        let jours = Math.round((new Date(fin) - new Date(debut)) / (1000 * 60 * 60 * 24));
        return jours <= 0 ? 1 : jours;
    };

    function mettreAJourPrix() {
        const dd = inputDebut.value;
        const df = inputFin.value;

        if (!dd || !df) {
            prixBox.style.display = 'none';
            return;
        }

        if (df < dd) {
            inputFin.setCustomValidity('La date de fin doit être après la date de début.');
            prixBox.style.display = 'none';
            return;
        }

        inputFin.setCustomValidity('');

        const jours = calculerJours(dd, df);
        const total = jours * prixJour;

        nbJoursEl.innerHTML   = jours + ' jour(s)';
        prixTotalEl.innerHTML = total.toFixed(2) + ' DT';
        prixBox.style.display = 'block';
    }

    inputDebut.addEventListener('change', mettreAJourPrix);
    inputFin.addEventListener('change', mettreAJourPrix);
    mettreAJourPrix();
});
