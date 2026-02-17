<?php require_once("header.php"); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Tableau de bord</h3>
    <div class="d-flex gap-2">
        <form method="post" action="/recuperer" onsubmit="return confirm('Remettre les données d\'origine ?')">
            <button type="submit" class="btn btn-warning">↩ Récupérer</button>
        </form>
        <form method="post" action="/reinitialiser" onsubmit="return confirm('Effacer toutes les villes et objets ajoutés ? Cette action est irréversible.')">
            <button type="submit" class="btn btn-danger">🗑 Réinitialiser</button>
        </form>
    </div>
</div>

<div class="row mb-4">
    <!-- Formulaire ajout ville -->
    <div class="col-md-6">
        <div class="card border-secondary h-100">
            <div class="card-header bg-secondary text-white">Ajouter une ville</div>
            <div class="card-body">
                <form method="post" action="/ville" class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Nom de la ville</label>
                        <input type="text" name="nom_villes" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sinistrés</label>
                        <input type="number" name="nb_sinstres" class="form-control" min="1" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Région</label>
                        <input type="text" name="nom_regions" class="form-control" placeholder="Ex: Analamanga" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-secondary w-100">Ajouter la ville</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Formulaire ajout objet -->
    <div class="col-md-6">
        <div class="card border-dark h-100">
            <div class="card-header bg-dark text-white">Ajouter un objet</div>
            <div class="card-body">
                <?php if (!empty($erreur_objet)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erreur_objet) ?></div>
                <?php endif; ?>
                <form method="post" action="/objets" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Nom</label>
                        <input type="text" name="nom_objets" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Unité</label>
                        <input type="text" name="unite_objets" class="form-control" placeholder="kg…" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Prix (Ar)</label>
                        <input type="number" name="prix_unitaire" class="form-control" min="0.01" step="0.01" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Type</label>
                        <select name="id_types_objets" class="form-select" required>
                            <option value="">--</option>
                            <?php foreach ($types as $t): ?>
                                <option value="<?= $t['id_types_objets'] ?>"><?= htmlspecialchars($t['nom_types_objets']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-dark w-100">Ajouter l'objet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Configuration pourcentage de réduction -->
<div class="card mb-4 border-danger">
    <div class="card-header bg-danger text-white">Configuration vente — Réduction</div>
    <div class="card-body">
        <form method="post" action="/config" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Pourcentage de réduction lors d'une vente de don (%)</label>
                <input type="number" name="reduction_vente" class="form-control" min="0" max="100" step="1" value="<?= $reduction ?>" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-danger w-100">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<?php foreach ($dashboard as $data): ?>
    <?php $ville = $data['ville']; ?>
    <div class="card mb-4">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <span>
                <strong><?= htmlspecialchars($ville['nom_villes']) ?></strong>
                — <?= $ville['nb_sinstres'] ?> sinistrés
                <span class="badge bg-info ms-2">Achats: <?= number_format($data['total_achats'], 0, ',', ' ') ?> Ar</span>
            </span>
            <div class="d-flex gap-2">
                <a href="/besoin?id_ville=<?= $ville['id_villes'] ?>" class="btn btn-primary btn-sm">+ Besoin</a>
                <a href="/don?id_ville=<?= $ville['id_villes'] ?>" class="btn btn-warning btn-sm">+ Don</a>
                <a href="/attribution?id_ville=<?= $ville['id_villes'] ?>" class="btn btn-success btn-sm">Attribuer</a>
                <a href="/saisir-achat?id_ville=<?= $ville['id_villes'] ?>" class="btn btn-info btn-sm">Acheter</a>
                <a href="/vente?id_ville=<?= $ville['id_villes'] ?>" class="btn btn-danger btn-sm">Vendre</a>
            </div>
        </div>
        <div class="card-body">

            <h6 class="text-primary">Besoins</h6>
            <?php if (empty($data['besoins'])): ?>
                <p class="text-muted">Aucun besoin enregistré.</p>
            <?php else: ?>
                <table class="table table-sm table-bordered mb-3">
                    <thead class="table-light">
                        <tr>
                            <th>Objet</th>
                            <th>Besoin total</th>
                            <th>Attribué</th>
                            <th>Reste</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['besoins'] as $b): ?>
                            <?php
                                $reste = $b['quantite_besoins'] - $b['total_attribue'];
                                $couleur = $reste <= 0 ? 'table-success' : ($b['total_attribue'] > 0 ? 'table-warning' : '');
                            ?>
                            <tr class="<?= $couleur ?>">
                                <td><?= htmlspecialchars($b['nom_objets']) ?> (<?= $b['unite_objets'] ?>)</td>
                                <td><?= $b['quantite_besoins'] ?></td>
                                <td><?= $b['total_attribue'] ?></td>
                                <td><?= max(0, $reste) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <h6 class="text-warning">Dons reçus</h6>
            <?php if (empty($data['dons'])): ?>
                <p class="text-muted">Aucun don enregistré.</p>
            <?php else: ?>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Objet</th>
                            <th>Total reçu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['dons'] as $d): ?>
                            <tr>
                                <td><?= htmlspecialchars($d['nom_objets']) ?> (<?= $d['unite_objets'] ?>)</td>
                                <td><?= $d['total_dons'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

        </div>
    </div>
<?php endforeach; ?>

<?php require_once("footer.php"); ?>