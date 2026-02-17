<?php require_once("header.php"); ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        
        <div class="text-center mb-4">
            <h3>Acheter des ressources</h3>
            <p class="text-muted">Pour la ville de <strong><?= htmlspecialchars($ville['nom_villes']) ?></strong></p>
        </div>

        <div class="card shadow-lg">
            <div class="card-body p-4">

                <div class="d-flex justify-content-between align-items-center mb-4 p-3 rounded bg-light">
                    <span class="text-muted small text-uppercase fw-bold">Budget dispo</span>
                    <strong class="text-success fs-5"><?= number_format($argent_dispo, 0, ',', ' ') ?> Ar</strong>
                </div>

                <?php if (!empty($erreur)): ?>
                    <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger"><?= htmlspecialchars($erreur) ?></div>
                <?php endif; ?>

                <?php if (!empty($succes)): ?>
                    <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success">Achat enregistré avec succès !</div>
                <?php endif; ?>

                <form method="post" action="/saisir-achat" id="formAchat">
                    <div class="mb-3">
                        <label class="form-label">Objet à acheter</label>
                        <select name="id_objets" class="form-select" id="selectObjet" required>
                            <option value="">Sélectionnez un objet...</option>
                            <?php foreach ($objets as $o): ?>
                                <option value="<?= $o['id_objets'] ?>" data-prix="<?= $o['prix_unitaire'] ?>">
                                    <?= htmlspecialchars($o['nom_objets']) ?> (<?= number_format($o['prix_unitaire'], 0, ',', ' ') ?> Ar/<?= $o['unite_objets'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Quantité</label>
                        <input type="number" name="quantite" class="form-control" id="inputQte" min="0.01" step="0.01" placeholder="0.00" required>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="text-muted">Total estimé</span>
                        <span id="montantTotal" class="fs-4 fw-bold text-primary">0 Ar</span>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3">Confirmer l'achat</button>
                    <a href="/" class="btn btn-secondary w-100 mt-2">Annuler</a>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
const selectObjet = document.getElementById('selectObjet');
const inputQte = document.getElementById('inputQte');
const montantTotal = document.getElementById('montantTotal');

function calculerMontant() {
    const option = selectObjet.options[selectObjet.selectedIndex];
    const prix = parseFloat(option.dataset.prix || 0);
    const qte = parseFloat(inputQte.value || 0);
    const total = prix * qte;
    montantTotal.textContent = total.toLocaleString('fr-FR') + ' Ar';
}

selectObjet.addEventListener('change', calculerMontant);
inputQte.addEventListener('input', calculerMontant);
</script>

<?php require_once("footer.php"); ?>