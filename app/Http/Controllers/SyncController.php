<?php

namespace App\Http\Controllers;

use App\Models\Annotation;
use App\Models\Tache;
use App\Models\Transaction;
use App\Services\ConsensusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SyncController extends Controller
{
    public function __construct(
        private ConsensusService $consensusService,
    ) {}

    public function push(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'annotations' => ['required', 'array', 'min:1'],
            'annotations.*.tache_id' => ['required', 'integer', 'exists:taches,id'],
            'annotations.*.reponse_choisie' => ['required', 'string', 'max:255'],
            'annotations.*.temps_execution_ms' => ['required', 'integer', 'min:0'],
        ]);

        $user = $request->user();
        $created = 0;
        $ignored = 0;

        DB::transaction(function () use (&$created, &$ignored, $validated, $request, $user): void {
            foreach ($validated['annotations'] as $item) {
                $task = Tache::query()
                    ->whereIn('statut', ['nouvelle', 'en_cours'])
                    ->find($item['tache_id']);

                if (!$task) {
                    $ignored++;
                    continue;
                }

                $exists = Annotation::query()
                    ->where('utilisateur_id', $user->id)
                    ->where('tache_id', $item['tache_id'])
                    ->exists();

                if ($exists) {
                    $ignored++;
                    continue;
                }

                $annotation = Annotation::create([
                    'utilisateur_id' => $user->id,
                    'tache_id' => $item['tache_id'],
                    'reponse_choisie' => $item['reponse_choisie'],
                    'temps_execution_ms' => $item['temps_execution_ms'],
                    'ip_address' => $request->ip(),
                    'device_info' => (string) $request->userAgent(),
                ]);

                // Passer en_cours dès la première annotation
                if ($task->statut === 'nouvelle') {
                    $task->update(['statut' => 'en_cours']);
                }

                // Vérifier sentinelle
                $isSentinelle = $this->consensusService->handleSentinelle($task, $annotation);

                // Vérifier le consensus (seulement pour les tâches non-sentinelles)
                if (!$isSentinelle) {
                    $consensusAnswer = $this->consensusService->checkConsensus($task);

                    if ($consensusAnswer !== null) {
                        $this->consensusService->applyConsensus($task, $consensusAnswer);
                    }
                }

                $created++;
            }
        });

        return response()->json([
            'message' => 'Synchronisation push terminee.',
            'created' => $created,
            'ignored' => $ignored,
        ]);
    }

    public function pull(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'last_sync_timestamp' => ['nullable', 'date'],
        ]);

        $since = isset($validated['last_sync_timestamp'])
            ? Carbon::parse($validated['last_sync_timestamp'])
            : now()->subDays(30);

        $tasks = Tache::query()
            ->with('image')
            ->where('updated_at', '>=', $since)
            ->latest('updated_at')
            ->limit(100)
            ->get();

        $transactions = Transaction::query()
            ->where('utilisateur_id', $request->user()->id)
            ->where('updated_at', '>=', $since)
            ->latest('updated_at')
            ->limit(100)
            ->get();

        return response()->json([
            'server_timestamp' => now()->toIso8601String(),
            'tasks' => $tasks,
            'transactions' => $transactions,
        ]);
    }
}
