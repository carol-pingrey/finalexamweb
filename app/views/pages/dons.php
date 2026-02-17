<?php require_once("header.php"); ?>

<h3 class="mb-4">Faire un don — <?= htmlspecialchars($ville['nom_villes']) ?></h3>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-warning"><h5 class="mb-0">Nouveau don</h5></div>
            <div class="card-body">

                <?php if (!empty($erreur)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
                <?php endif; ?>

                <?php if (!empty($succes)): ?>
                    <div class="alert alert-success">Don enregistré ✅</div>
                <?php endif; ?>

                <form method="post" action="/don">
                    <div class="mb-3">
                        <label class="form-label">Objet de don</label>
                        <select name="id_objets" class="form-select" required>
                            <option value="">-- Choisir un objet --</option>
                            <?php foreach ($objets as $o): ?>
                                <option value="<?= $o['id_objets'] ?>">
                                    <?= htmlspecialchars($o['nom_objets']) ?> (<?= $o['unite_objets'] ?>) — <?= $o['nom_types_objets'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Quantité de don</label>
                        <input type="number" name="quantite" class="form-control" min="0.01" step="0.01" required>
                    </div>

                    <button type="submit" class="btn btn-warning w-100">Faire un don</button>
                    <a href="/" class="btn btn-secondary w-100 mt-2">Retour au tableau de bord</a>
                </form>

            </div>
        </div>
    </div>
</div>

<?php require_once("footer.php"); ?>