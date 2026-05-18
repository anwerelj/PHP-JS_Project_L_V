document.addEventListener('DOMContentLoaded', function () {

    const form       = document.getElementById('rechercheForm');
    const inputDebut = document.querySelector('input[name="date_debut"]');
    const inputFin   = document.querySelector('input[name="date_fin"]');

    if (!form || !inputDebut || !inputFin) return;

    const today = new Date().toISOString().split('T')[0];

    // Mettre à jour le min de date_fin quand date_debut change
    inputDebut.addEventListener('change', function () {
        if (this.value) {
            inputFin.min = this.value;
            if (inputFin.value && inputFin.value < this.value) {
                inputFin.value = '';
            }
        }
    });

    // Validation avant soumission
    form.addEventListener('submit', function (e) {
        const dd = inputDebut.value;
        const df = inputFin.value;
        let erreur = '';

        if (!dd || !df) {
            erreur = 'Veuillez sélectionner les deux dates.';
        } else if (dd < today) {
            erreur = 'La date de début ne peut pas être dans le passé.';
        } else if (df < dd) {
            erreur = 'La date de fin doit être après la date de début.';
        }

        if (erreur !== '') {
            e.preventDefault();
            let zoneErreur = document.getElementById('erreurRecherche');
            if (!zoneErreur) {
                zoneErreur = document.createElement('div');
                zoneErreur.id = 'erreurRecherche';
                zoneErreur.className = 'alert alert-error';
                form.parentNode.insertBefore(zoneErreur, form.nextSibling);
            }
            zoneErreur.innerHTML = '⚠️ ' + erreur;
            zoneErreur.style.display = 'block';
        }
    });

    // Filtrage live des résultats
    const inputFiltre = document.getElementById('filtreVoiture');
    if (inputFiltre) {
        inputFiltre.addEventListener('input', function () {
            const recherche = this.value.toLowerCase().trim();
            const cards = document.querySelectorAll('.car-card');
            let nbVisible = 0;

            for (const card of cards) {
                const texte = card.textContent.toLowerCase();
                if (texte.includes(recherche)) {
                    card.style.display = 'block';
                    nbVisible++;
                } else {
                    card.style.display = 'none';
                }
            }

            const compteur = document.getElementById('compteurVoitures');
            if (compteur) {
                compteur.innerHTML = nbVisible + ' voiture(s) trouvée(s)';
            }
        });
    }
});
