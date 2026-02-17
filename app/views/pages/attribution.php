<?php require_once("header.php"); ?>

<h3 class="mb-4">Attribuer un don — <?= htmlspecialchars($ville['nom_villes']) ?></h3>

<?php if (!empty($erreur)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
<?php endif; ?>

<?php if (!empty($succes)): ?>
    <div class="alert alert-success">Attribution enregistrée ✅</div>
<?php endif; ?>

<?php if (empty($besoins)): ?>
    <div class="alert alert-info">Aucun besoin enregistré pour cette ville.</div>
<?php else: ?>

<table class="table table-bordered">
    <thead class="table-dark">
        <tr>
            <th>Objet</th>
            <th>Besoin total</th>
            <th>Déjà attribué</th>
            <th>Reste à couvrir</th>
            <th>Stock dons dispo</th>
            <th>Attribuer</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($besoins as $b): ?>
            <?php
                $stock = max(0, (float)$b['stock_dons']);
                $reste = max(0, (float)$b['reste_besoin']);
                $complet = $reste <= 0;
            ?>
            <tr class="<?= $complet ? 'table-success' : '' ?>">
                <td><?= htmlspecialchars($b['nom_objets']) ?> (<?= $b['unite_objets'] ?>)</td>
                <td><?= $b['quantite_besoins'] ?></td>
                <td><?= $b['total_attribue'] ?></td>
                <td><?= $reste ?></td>
                <td>
                    <?php if ($stock <= 0): ?>
                        <span class="text-danger">0 (pas de don)</span>
                    <?php else: ?>
                        <?= $stock ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($complet): ?>
                        <span class="badge bg-success">Besoin couvert ✅</span>
                    <?php elseif ($stock <= 0): ?>
                        <span class="badge bg-secondary">Pas de stock</span>
                    <?php else: ?>
                        <form method="post" action="/attribution" class="d-flex gap-2">
                            <input type="hidden" name="id_besoins" value="<?= $b['id_besoins'] ?>">
                            <input type="number" name="quantite_attribuee" class="form-control form-control-sm" 
                                   min="0.01" max="<?= $stock ?>" step="0.01" 
                                   placeholder="max <?= $stock ?>" required style="width:130px;">
                            <button type="submit" class="btn btn-success btn-sm">Attribuer</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php endif; ?>

<a href="/" class="btn btn-secondary mt-2">Retour au tableau de bord</a>

<?php require_once("footer.php"); ?>