<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnnotateRequest;
use App\Http\Resources\AnnotationResource;
use App\Http\Resources\TaskResource;
use App\Models\Annotation;
use App\Models\Tache;
use App\Services\ConsensusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function __construct(
        private ConsensusService $consensusService,
    ) {
    }

    public function next(Request $request): JsonResponse
    {
        $user = $request->user();
        $count = max(1, min((int) $request->query('count', 1), 50));
        $skippedTaskIds = cache()->get('skipped_tasks:' . $user->id, []);

        $query = Tache::query()
            ->with('image')
            ->whereIn('statut', ['nouvelle', 'en_cours'])
            ->when(!empty($skippedTaskIds), function ($builder) use ($skippedTaskIds): void {
                $builder->whereNotIn('id', $skippedTaskIds);
            })
            ->whereDoesntHave('annotations', function ($query) use ($user): void {
                $query->where('utilisateur_id', $user->id);
            })
            ->inRandomOrder();

        if ($count > 1) {
            $tasks = $query->limit($count)->get();

            if ($tasks->isEmpty()) {
                return response()->json([
                    'message' => 'Aucune tache disponible pour le moment.',
                ], 404);
            }

            return response()->json([
                'message' => 'Taches recuperees avec succes.',
                'tasks' => TaskResource::collection($tasks),
            ]);
        }

        $task = $query->first();

        if (!$task) {
            return response()->json([
                'message' => 'Aucune tache disponible pour le moment.',
            ], 404);
        }

        return response()->json([
            'message' => 'Tache recuperee avec succes.',
            'task' => TaskResource::make($task),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min((int) $request->query('per_page', 20), 100));
        $status = $request->query('status');

        $query = Tache::query()
            ->with('image')
            ->withCount('annotations')
            ->latest();

        if (is_string($status) && in_array($status, ['nouvelle', 'en_cours', 'terminee'], true)) {
            $query->where('statut', $status);
        }

        return TaskResource::collection($query->paginate($perPage))->response();
    }

    public function annotate(AnnotateRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();

        $user = $request->user();

        $task = Tache::query()
            ->whereIn('statut', ['nouvelle', 'en_cours'])
            ->findOrFail($id);

        $alreadyAnnotated = Annotation::query()
            ->where('utilisateur_id', $user->id)
            ->where('tache_id', $task->id)
            ->exists();

        if ($alreadyAnnotated) {
            return response()->json([
                'message' => 'Cette tache a deja ete annotee par cet utilisateur.',
            ], 409);
        }

        $result = DB::transaction(function () use ($request, $validated, $user, $task): array {
            $annotation = Annotation::create([
                'utilisateur_id' => $user->id,
                'tache_id' => $task->id,
                'reponse_choisie' => $validated['reponse_choisie'],
                'temps_execution_ms' => $validated['temps_execution_ms'],
                'ip_address' => $request->ip(),
                'device_info' => (string) $request->userAgent(),
            ]);

            // Passer en_cours dès la première annotation
            if ($task->statut === 'nouvelle') {
                $task->update(['statut' => 'en_cours']);
            }

            // Vérifier si c'est une tâche sentinelle
            $isSentinelle = $this->consensusService->handleSentinelle($task, $annotation);

            // Vérifier le consensus (seulement pour les tâches non-sentinelles)
            $consensusReached = false;
            $consensusAnswer = null;

            if (!$isSentinelle) {
                $consensusAnswer = $this->consensusService->checkConsensus($task);

                if ($consensusAnswer !== null) {
                    $this->consensusService->applyConsensus($task, $consensusAnswer);
                    $consensusReached = true;
                }
            }

            return [
                'annotation' => $annotation,
                'is_sentinelle' => $isSentinelle,
                'consensus_reached' => $consensusReached,
                'consensus_answer' => $consensusAnswer,
            ];
        });

        $message = 'Annotation enregistree.';
        if ($result['consensus_reached']) {
            $message = 'Annotation enregistree. Consensus atteint, recompenses distribuees.';
        } elseif ($result['is_sentinelle']) {
            $message = 'Annotation enregistree.';
        }

        return response()->json([
            'message' => $message,
            'annotation' => AnnotationResource::make($result['annotation']),
            'consensus_reached' => $result['consensus_reached'],
        ], 201);
    }

    public function skip(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        Tache::query()->findOrFail($id);

        $key = 'skipped_tasks:' . $user->id;
        $skippedTaskIds = cache()->get($key, []);

        if (!in_array($id, $skippedTaskIds, true)) {
            $skippedTaskIds[] = $id;

            // Limiter à 200 tâches skippées maximum
            if (count($skippedTaskIds) > 200) {
                $skippedTaskIds = array_slice($skippedTaskIds, -200);
            }

            cache()->put($key, $skippedTaskIds, now()->addDays(7));
        }

        return response()->json([
            'message' => 'Tache ignoree.',
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = max(1, min((int) $request->query('per_page', 20), 100));
        $status = $request->query('status');

        $query = Annotation::query()
            ->with(['tache.image', 'transaction'])
            ->where('utilisateur_id', $user->id)
            ->latest();

        if ($status === 'validee') {
            $query->whereHas('transaction', function ($subQuery): void {
                $subQuery->where('type', 'gain');
            });
        }

        return AnnotationResource::collection($query->paginate($perPage))->response();
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $task = Tache::query()
            ->with('image')
            ->withCount('annotations')
            ->findOrFail($id);

        $alreadyAnnotated = Annotation::query()
            ->where('utilisateur_id', $request->user()->id)
            ->where('tache_id', $task->id)
            ->exists();

        return response()->json([
            'task' => TaskResource::make($task),
            'already_annotated' => $alreadyAnnotated,
        ]);
    }
}
