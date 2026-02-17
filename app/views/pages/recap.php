<?php require_once("header.php"); ?>

<div class="d-flex justify-content-between align-items-center mb-5">
    <h3>Récapitulation globale</h3>
    <button onclick="actualiser()" class="btn btn-secondary btn-sm">
        🔄 Actualiser les données
    </button>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card h-100 border-0">
            <div class="card-body text-center p-5">
                <div class="mb-3 text-uppercase text-muted fw-bold small tracking-wide">Besoins Totaux</div>
                <h2 class="display-5 fw-bold text-secondary mb-1" id="besoins_total"><?= number_format($besoins_total, 0, ',', ' ') ?> Ar</h2>
                <div class="text-muted mb-4">Valeur estimée</div>
                
                <hr class="my-4 opacity-25">
                
                <div class="d-flex justify-content-between px-4">
                    <span class="text-muted">Besoins satisfaits</span>
                    <strong class="text-success" id="besoins_satisfaits"><?= number_format($besoins_satisfaits, 0, ',', ' ') ?> Ar</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card h-100 border-0" style="background: #fff8f0;"> <div class="card-body text-center p-5">
                <div class="mb-3 text-uppercase text-muted fw-bold small tracking-wide text-warning">Dons Reçus</div>
                <h2 class="display-5 fw-bold text-primary mb-1" id="dons_recus"><?= number_format($dons_recus, 0, ',', ' ') ?> Ar</h2>
                <div class="text-muted mb-4">Total collecté</div>

                <hr class="my-4 opacity-25">

                <div class="d-flex justify-content-between px-4">
                    <span class="text-muted">Dons dispatchés</span>
                    <strong class="text-danger" id="dons_dispatches"><?= number_format($dons_dispatches, 0, ',', ' ') ?> Ar</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="text-center mt-5">
    <a href="/" class="btn btn-secondary px-5">Retour au tableau de bord</a>
</div>

<script>
function actualiser() {
    fetch('/api/recap')
        .then(response => response.json())
        .then(data => {
            document.getElementById('besoins_total').textContent = data.besoins_total + ' Ar';
            document.getElementById('besoins_satisfaits').textContent = data.besoins_satisfaits + ' Ar';
            document.getElementById('dons_recus').textContent = data.dons_recus + ' Ar';
            document.getElementById('dons_dispatches').textContent = data.dons_dispatches + ' Ar';
        });
}
</script>

<?php require_once("footer.php"); ?>