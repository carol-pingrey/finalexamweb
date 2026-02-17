<?php require_once("header.php"); ?>

<h3 class="mb-4">Liste des achats</h3>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" action="/achats" class="row g-2">
            <div class="col-md-4">
                <label class="form-label">Filtrer par ville</label>
                <select name="id_ville" class="form-select">
                    <option value="0">-- Toutes les villes --</option>
                    <?php foreach ($villes as $v): ?>
                        <option value="<?= $v['id_villes'] ?>" <?= $id_ville_filtre == $v['id_villes'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($v['nom_villes']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
            </div>
        </form>
    </div>
</div>

<?php if (empty($achats)): ?>
    <div class="alert alert-info">Aucun achat enregistré.</div>
<?php else: ?>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Date</th>
                <th>Ville</th>
                <th>Objet</th>
                <th>Prix unitaire</th>
                <th>Quantité</th>
                <th>Montant total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($achats as $a): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($a['date_achat'])) ?></td>
                    <td><?= htmlspecialchars($a['nom_villes']) ?></td>
                    <td><?= htmlspecialchars($a['nom_objets']) ?> (<?= $a['unite_objets'] ?>)</td>
                    <td><?= number_format($a['prix_unitaire'], 0, ',', ' ') ?> Ar</td>
                    <td><?= $a['quantite_achat'] ?></td>
                    <td><strong><?= number_format($a['montant_total'], 0, ',', ' ') ?> Ar</strong></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<a href="/" class="btn btn-secondary">Retour au tableau de bord</a>

<?php require_once("footer.php"); ?>