<?php

namespace Database\Seeders;

use App\Models\Annotation;
use App\Models\Dataset;
use App\Models\Image;
use App\Models\Tache;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DataLoopTestSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->resetData();

            $users = $this->seedUsers();
            $tasks = $this->seedImagesAndTasks();
            $annotations = $this->seedAnnotationsAndRewards($users, $tasks);
            $this->seedDatasets($tasks, $annotations);
        });
    }

    private function resetData(): void
    {
        DB::table('personal_access_tokens')->delete();
        DB::table('annotation_dataset')->delete();
        Transaction::query()->delete();
        Annotation::query()->delete();
        Tache::query()->delete();
        Image::query()->delete();
        Dataset::query()->delete();
        User::query()->delete();
    }

    /**
     * @return array{admin: User, contributeurs: array<int, User>}
     */
    private function seedUsers(): array
    {
        $adminPassword = Hash::make('Admin@1234');

        $admin = User::create([
            'name' => 'Admin DataLoop',
            'telephone' => '0700000001',
            'email' => 'admin@dataloop.ci',
            'password' => $adminPassword,
            'role' => 'admin',
            'statut' => 'actif',
            'score_confiance' => 98.50,
            'solde_virtuel' => 0,
        ]);

        $noms = [
            'Awa Konan',
            'Serge Kouassi',
            'Mariam Traore',
            'Yao Koffi',
            'Fatou Bamba',
            'Didier Gohi',
            'Nadia Aka',
            'Boris Nguessan',
            'Clarisse Yapo',
            'Kevin Soro',
        ];

        $scores = [86.2, 73.4, 91.8, 67.5, 79.3, 88.1, 82.9, 58.6, 76.4, 69.2];
        $contributeurs = [];

        foreach ($noms as $index => $nom) {
            $password = Hash::make('Password@123');

            $contributeurs[] = User::create([
                'name' => $nom,
                'telephone' => '07' . str_pad((string) ($index + 2), 8, '0', STR_PAD_LEFT),
                'email' => Str::slug($nom, '.') . '@dataloop.ci',
                'password' => $password,
                'role' => 'contributeur',
                'statut' => $index === 7 ? 'suspendu' : 'actif',
                'motif_statut' => $index === 7 ? 'Reponses trop rapides detectees' : null,
                'score_confiance' => $scores[$index],
                'solde_virtuel' => 0,
            ]);
        }

        return [
            'admin' => $admin,
            'contributeurs' => $contributeurs,
        ];
    }

    /**
     * @return array<int, Tache>
     */
    private function seedImagesAndTasks(): array
    {
        $villes = [
            ['ville' => 'Abidjan', 'lat' => 5.3453, 'lng' => -4.0244],
            ['ville' => 'Bouake', 'lat' => 7.6906, 'lng' => -5.0300],
            ['ville' => 'Yamoussoukro', 'lat' => 6.8276, 'lng' => -5.2893],
            ['ville' => 'San Pedro', 'lat' => 4.7485, 'lng' => -6.6363],
            ['ville' => 'Korhogo', 'lat' => 9.4580, 'lng' => -5.6296],
        ];

        $definitions = [
            [
                'categorie' => 'transport',
                'type_tache' => 'classification',
                'question' => 'Quel type de voie voyez-vous ?',
                'options_reponse' => ['route_goudronnee', 'piste', 'voie_degradee'],
            ],
            [
                'categorie' => 'agriculture',
                'type_tache' => 'classification',
                'question' => 'Cette scene correspond a quelle activite agricole ?',
                'options_reponse' => ['cacao', 'anacarde', 'maraichage', 'autre'],
            ],
            [
                'categorie' => 'sante',
                'type_tache' => 'detection',
                'question' => 'Un centre de sante est-il visible ?',
                'options_reponse' => ['oui', 'non', 'incertain'],
            ],
            [
                'categorie' => 'commerce',
                'type_tache' => 'classification',
                'question' => 'Quel type de commerce est present ?',
                'options_reponse' => ['marche', 'boutique', 'superette', 'aucun'],
            ],
            [
                'categorie' => 'environnement',
                'type_tache' => 'evaluation',
                'question' => 'Le niveau de proprete de la zone ?',
                'options_reponse' => ['propre', 'moyen', 'insalubre'],
            ],
        ];

        $tasks = [];

        for ($i = 0; $i < 24; $i++) {
            $def = $definitions[$i % count($definitions)];
            $ville = $villes[$i % count($villes)];

            $image = Image::create([
                'url_stockage' => 'images/demo/ci_' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT) . '.jpg',
                'categorie' => $def['categorie'],
                'source' => 'collecte_terrain_hackathon',
                'metadata_geo' => [
                    'ville' => $ville['ville'],
                    'lat' => $ville['lat'],
                    'lng' => $ville['lng'],
                ],
                'taille_fichier' => random_int(120000, 950000),
            ]);

            $tasks[] = Tache::create([
                'image_id' => $image->id,
                'type_tache' => $def['type_tache'],
                'question' => $def['question'],
                'options_reponse' => $def['options_reponse'],
                'nb_annotations_requises' => 3,
                'statut' => $i < 16 ? 'terminee' : ($i < 21 ? 'en_cours' : 'nouvelle'),
            ]);
        }

        return $tasks;
    }

    /**
     * @param  array{admin: User, contributeurs: array<int, User>}  $users
     * @param  array<int, Tache>  $tasks
     * @return array<int, Annotation>
     */
    private function seedAnnotationsAndRewards(array $users, array $tasks): array
    {
        $contributeurs = collect($users['contributeurs'])
            ->filter(fn(User $u): bool => $u->statut === 'actif')
            ->values();

        $balances = $contributeurs->mapWithKeys(fn(User $u): array => [$u->id => 0.0])->all();
        $annotations = [];

        foreach ($tasks as $task) {
            $minimum = $task->statut === 'terminee' ? 3 : 2;
            $nombreAnnotations = min($minimum + random_int(0, 1), $contributeurs->count());
            $selected = $contributeurs->shuffle()->take($nombreAnnotations);

            $options = $task->options_reponse ?? ['oui', 'non'];

            foreach ($selected as $user) {
                $tempsExecution = random_int(900, 6500);
                if (random_int(1, 100) <= 8) {
                    $tempsExecution = random_int(350, 850);
                }

                $annotation = Annotation::create([
                    'utilisateur_id' => $user->id,
                    'tache_id' => $task->id,
                    'reponse_choisie' => $options[array_rand($options)],
                    'temps_execution_ms' => $tempsExecution,
                    'ip_address' => '41.202.' . random_int(10, 220) . '.' . random_int(2, 250),
                    'device_info' => 'Android ' . random_int(9, 14) . ' | DataLoop App v1.0',
                ]);

                $soldeAvant = $balances[$user->id];
                $soldeApres = $soldeAvant + 50.0;
                $balances[$user->id] = $soldeApres;

                Transaction::create([
                    'utilisateur_id' => $user->id,
                    'annotation_id' => $annotation->id,
                    'type' => 'gain',
                    'libelle' => 'Annotation validee',
                    'montant' => 50,
                    'solde_avant' => $soldeAvant,
                    'solde_apres' => $soldeApres,
                    'reference_tache' => (string) $task->id,
                ]);

                $annotations[] = $annotation;
            }
        }

        foreach ($contributeurs as $user) {
            if (($balances[$user->id] ?? 0.0) >= 300 && random_int(1, 100) <= 35) {
                $montant = 200.0;
                $soldeAvant = $balances[$user->id];
                $soldeApres = $soldeAvant - $montant;
                $balances[$user->id] = $soldeApres;

                Transaction::create([
                    'utilisateur_id' => $user->id,
                    'annotation_id' => null,
                    'type' => 'retrait',
                    'libelle' => 'Demande de retrait',
                    'montant' => $montant,
                    'solde_avant' => $soldeAvant,
                    'solde_apres' => $soldeApres,
                    'reference_tache' => 'orange_money',
                ]);
            }

            $user->update([
                'solde_virtuel' => $balances[$user->id] ?? 0,
            ]);
        }

        return $annotations;
    }

    /**
     * @param  array<int, Tache>  $tasks
     * @param  array<int, Annotation>  $annotations
     */
    private function seedDatasets(array $tasks, array $annotations): void
    {
        $annotationsCollection = collect($annotations);
        $validatedTaskIds = collect($tasks)
            ->filter(fn(Tache $task): bool => $task->statut === 'terminee')
            ->pluck('id')
            ->all();

        $validatedAnnotations = $annotationsCollection
            ->whereIn('tache_id', $validatedTaskIds)
            ->values();

        $datasetFr = Dataset::create([
            'nom' => 'dataloop_ci_dataset_classification_v1',
            'description' => 'Jeu de donnees local pour taches de classification en contexte ivoirien.',
            'version' => 'v1.0',
            'nb_images' => count($validatedTaskIds),
            'nb_annotations_validees' => $validatedAnnotations->count(),
            'format_export' => 'json',
        ]);

        $datasetExport = Dataset::create([
            'nom' => 'dataloop_ci_dataset_export_csv_v1',
            'description' => 'Version exportable pour demo jury et visualisation rapide.',
            'version' => 'v1.0',
            'nb_images' => count($validatedTaskIds),
            'nb_annotations_validees' => $validatedAnnotations->count(),
            'format_export' => 'csv',
        ]);

        $datasetFr->annotations()->attach($validatedAnnotations->pluck('id')->all());
        $datasetExport->annotations()->attach($validatedAnnotations->take(30)->pluck('id')->all());
    }
}
