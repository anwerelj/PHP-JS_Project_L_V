document.addEventListener('DOMContentLoaded', function () {

    // Alertes qui disparaissent après 4 secondes
    const alertes = document.querySelectorAll('.alert-success, .alert-error');
    for (const alerte of alertes) {
        setTimeout(function () {
            alerte.style.transition = 'opacity 0.6s';
            alerte.style.opacity = '0';
            setTimeout(function () { alerte.style.display = 'none'; }, 600);
        }, 4000);
    }

    // Lien actif dans la navbar
    const liens = document.querySelectorAll('.nav-links a');
    for (const lien of liens) {
        if (lien.href === window.location.href) {
            lien.style.color = '#14b8a6';
            lien.style.fontWeight = '700';
        }
    }
});
