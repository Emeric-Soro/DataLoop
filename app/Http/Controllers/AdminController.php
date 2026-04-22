<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskUploadRequest;
use App\Http\Resources\TaskResource;
use App\Models\Annotation;
use App\Models\Dataset;
use App\Models\Image;
use App\Models\Tache;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function dashboard(): JsonResponse
    {
        $annotationsAujourdhui = Annotation::query()
            ->whereDate('created_at', now()->toDateString())
            ->count();

        $utilisateursInscrits = User::query()->count();

        $soldeTotalDistribue = (float) Transaction::query()
            ->where('type', 'gain')
            ->sum('montant');

        return response()->json([
            'message' => 'Metriques dashboard admin recuperees.',
            'data' => [
                'annotations_aujourdhui' => $annotationsAujourdhui,
                'utilisateurs_inscrits' => $utilisateursInscrits,
                'solde_total_distribue' => $soldeTotalDistribue,
            ],
        ]);
    }

    public function users(Request $request): JsonResponse
    {
        $perPage = max(1, min((int) $request->query('per_page', 20), 100));

        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('telephone', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('statut', $request->string('status'));
        }

        if ($request->query('sort') === 'score') {
            $query->orderByDesc('score_confiance');
        } else {
            $query->latest();
        }

        return response()->json($query->paginate($perPage));
    }

    public function updateUserStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'statut' => ['required', 'in:actif,suspendu'],
            'motif' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::query()->findOrFail($id);

        $user->update([
            'statut' => $validated['statut'],
            'motif_statut' => $validated['motif'] ?? null,
        ]);

        return response()->json([
            'message' => 'Statut utilisateur mis a jour.',
            'user' => $user->fresh(),
        ]);
    }

    public function alerts(Request $request): JsonResponse
    {
        $severity = $request->query('severity', 'high');
        $resolved = filter_var($request->query('resolved', false), FILTER_VALIDATE_BOOL);

        $threshold = $severity === 'high' ? 1200 : 2500;

        $alerts = Annotation::query()
            ->with(['utilisateur:id,name,telephone', 'tache:id,question'])
            ->where('temps_execution_ms', '<', $threshold)
            ->when(!$resolved, function ($query): void {
                $query->where('created_at', '>=', now()->subDays(7));
            })
            ->latest()
            ->limit(50)
            ->get()
            ->map(function (Annotation $annotation) use ($severity): array {
                return [
                    'id' => $annotation->id,
                    'severity' => $severity,
                    'reason' => 'Temps d\'execution suspectement faible.',
                    'temps_execution_ms' => $annotation->temps_execution_ms,
                    'created_at' => $annotation->created_at,
                    'utilisateur' => $annotation->utilisateur,
                    'tache' => $annotation->tache,
                ];
            });

        return response()->json([
            'alerts' => $alerts,
        ]);
    }

    public function tasksUpload(TaskUploadRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $createdTasks = DB::transaction(function () use ($validated): array {
            $tasks = [];

            foreach ($validated['images'] as $file) {
                $path = $file->store('uploads/tasks', 'public');

                $image = Image::create([
                    'url_stockage' => $path,
                    'categorie' => 'upload_admin',
                    'taille_fichier' => $file->getSize(),
                ]);

                $tasks[] = Tache::create([
                    'image_id' => $image->id,
                    'type_tache' => $validated['type_tache'],
                    'question' => $validated['question'],
                    'options_reponse' => $validated['options'] ?? null,
                    'statut' => 'nouvelle',
                ]);
            }

            return $tasks;
        });

        return response()->json([
            'message' => 'Upload traite et taches creees.',
            'count' => count($createdTasks),
            'tasks' => TaskResource::collection(collect($createdTasks)),
        ], 201);
    }

    public function datasets(Request $request): JsonResponse
    {
        $perPage = max(1, min((int) $request->query('per_page', 20), 100));

        return response()->json(
            Dataset::query()
                ->withCount('annotations')
                ->latest()
                ->paginate($perPage)
        );
    }

    public function exportDataset(Request $request, int $id): JsonResponse|StreamedResponse
    {
        $dataset = Dataset::query()->with('annotations')->findOrFail($id);
        $format = strtolower((string) $request->query('format', 'json'));

        if ($format === 'csv') {
            $filename = 'dataset_' . $dataset->id . '.csv';

            return response()->streamDownload(function () use ($dataset): void {
                $handle = fopen('php://output', 'wb');
                fputcsv($handle, ['annotation_id', 'tache_id', 'utilisateur_id', 'reponse_choisie', 'temps_execution_ms']);

                foreach ($dataset->annotations as $annotation) {
                    fputcsv($handle, [
                        $annotation->id,
                        $annotation->tache_id,
                        $annotation->utilisateur_id,
                        $annotation->reponse_choisie,
                        $annotation->temps_execution_ms,
                    ]);
                }

                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv',
            ]);
        }

        return response()->json([
            'dataset' => $dataset,
            'annotations' => $dataset->annotations,
        ]);
    }

    public function updateConfig(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'seuil_consensus' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'freq_sentinelle' => ['nullable', 'integer', 'min:0'],
        ]);

        $currentConfig = Cache::get('system_config', []);
        $newConfig = array_merge($currentConfig, $validated);

        Cache::forever('system_config', $newConfig);

        return response()->json([
            'message' => 'Configuration systeme mise a jour.',
            'config' => $newConfig,
        ]);
    }
}
