<?php require_once("header.php"); ?>

<h3 class="mb-4">Vendre un don — <?= htmlspecialchars($ville['nom_villes']) ?></h3>

<?php if (!empty($erreur)): ?>
    <div class="alert alert-danger"><?= $erreur ?></div>
<?php endif; ?>

<?php if (!empty($succes)): ?>
    <div class="alert alert-success">Vente enregistrée ✅ Le montant a été ajouté aux dons en argent.</div>
<?php endif; ?>

<?php if (empty($dons)): ?>
    <div class="alert alert-info">Aucun don disponible à vendre pour cette ville.</div>
<?php else: ?>

<table class="table table-bordered">
    <thead class="table-dark">
        <tr>
            <th>Objet</th>
            <th>Prix unitaire</th>
            <th>Prix de vente (-<?= $reduction ?>%)</th>
            <th>Stock dispo</th>
            <th>Quantité à vendre</th>
            <th>Montant estimé</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($dons as $d): ?>
            <?php
                $stock = $d['quantite_dons'] - $d['deja_vendu'];
                $prix_vente = $d['prix_unitaire'] * (1 - $reduction / 100);
            ?>
            <tr>
                <td><?= htmlspecialchars($d['nom_objets']) ?> (<?= $d['unite_objets'] ?>)</td>
                <td><?= number_format($d['prix_unitaire'], 0, ',', ' ') ?> Ar</td>
                <td class="text-danger"><?= number_format($prix_vente, 0, ',', ' ') ?> Ar</td>
                <td><?= $stock ?></td>
                <td>
                    <form method="post" action="/vente" class="d-flex gap-2 align-items-center">
                        <input type="hidden" name="id_dons" value="<?= $d['id_dons'] ?>">
                        <input type="number" name="quantite" class="form-control form-control-sm qte-input"
                               min="0.01" max="<?= $stock ?>" step="0.01"
                               data-prix="<?= $prix_vente ?>"
                               placeholder="max <?= $stock ?>" style="width:120px">
                </td>
                <td><span class="montant-estime text-success fw-bold">0 Ar</span></td>
                <td>
                        <button type="submit" class="btn btn-danger btn-sm">Vendre</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php endif; ?>

<a href="/" class="btn btn-secondary mt-2">Retour au tableau de bord</a>

<script>
document.querySelectorAll('.qte-input').forEach(input => {
    input.addEventListener('input', function() {
        const prix = parseFloat(this.dataset.prix || 0);
        const qte  = parseFloat(this.value || 0);
        const span = this.closest('tr').querySelector('.montant-estime');
        span.textContent = (prix * qte).toLocaleString('fr-FR') + ' Ar';
    });
});
</script>

<?php require_once("footer.php"); ?>