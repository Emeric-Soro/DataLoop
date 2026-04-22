<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewContributionRequest;
use App\Http\Requests\SubmitContributionRequest;
use App\Http\Resources\ContributionResource;
use App\Models\Contribution;
use App\Models\ContributionReview;
use App\Services\ContributionConsensusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContributionController extends Controller
{
    public function __construct(
        private ContributionConsensusService $consensusService,
    ) {
    }

    public function submit(SubmitContributionRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $typeContenu = $validated['type_contenu'];
        $file = $request->file('fichier');

        if (in_array($typeContenu, ['image', 'audio'], true) && !$file) {
            return response()->json([
                'message' => 'Le fichier est requis pour ce type de contribution.',
            ], 422);
        }

        if ($typeContenu === 'texte' && empty($validated['texte_contenu'])) {
            return response()->json([
                'message' => 'Le champ texte_contenu est requis pour une contribution texte.',
            ], 422);
        }

        if ($file) {
            $allowedMimes = $typeContenu === 'image'
                ? ['image/jpeg', 'image/png', 'image/webp']
                : ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/x-wav'];

            if (!in_array((string) $file->getMimeType(), $allowedMimes, true)) {
                return response()->json([
                    'message' => 'Type de fichier non autorise pour ce type de contribution.',
                ], 422);
            }
        }

        $contribution = DB::transaction(function () use ($validated, $request, $file, $typeContenu): Contribution {
            $path = null;
            $size = null;

            if ($file) {
                $path = $file->store('uploads/' . $typeContenu, 'contributions');
                $size = $file->getSize();
            }

            $contribution = Contribution::create([
                'utilisateur_id' => $request->user()->id,
                'type_contenu' => $typeContenu,
                'fichier_url' => $path,
                'taille_fichier' => $size,
                'texte_contenu' => $validated['texte_contenu'] ?? null,
                'langue' => $validated['langue'],
                'description' => $validated['description'],
                'categorie' => $validated['categorie'] ?? null,
                'statut' => 'en_attente',
                'nb_reviews_requises' => $validated['nb_reviews_requises'] ?? 3,
                'metadata' => $validated['metadata'] ?? null,
            ]);

            $contribution->update(['statut' => 'en_revue']);

            return $contribution->fresh();
        });

        return response()->json([
            'message' => 'Contribution soumise avec succes.',
            'contribution' => ContributionResource::make($contribution),
        ], 201);
    }

    public function myContributions(Request $request): JsonResponse
    {
        $perPage = max(1, min((int) $request->query('per_page', 20), 100));

        $query = Contribution::query()
            ->withCount('reviews')
            ->where('utilisateur_id', $request->user()->id)
            ->latest();

        if ($request->filled('statut')) {
            $query->where('statut', $request->string('statut'));
        }

        return ContributionResource::collection($query->paginate($perPage))->response();
    }

    public function nextReview(Request $request): JsonResponse
    {
        $user = $request->user();

        $contribution = Contribution::query()
            ->with(['utilisateur:id,name,telephone', 'reviews:contribution_id,reviewer_id,is_valid,note_veracite'])
            ->where('statut', 'en_revue')
            ->where('utilisateur_id', '!=', $user->id)
            ->whereDoesntHave('reviews', function ($query) use ($user): void {
                $query->where('reviewer_id', $user->id);
            })
            ->whereRaw('(
                select count(*)
                from contribution_reviews
                where contribution_reviews.contribution_id = contributions.id
            ) < nb_reviews_requises')
            ->inRandomOrder()
            ->first();

        if (!$contribution) {
            return response()->json([
                'message' => 'Aucune contribution disponible pour revue.',
            ], 404);
        }

        return response()->json([
            'message' => 'Contribution de revue recuperee.',
            'contribution' => ContributionResource::make($contribution),
        ]);
    }

    public function review(ReviewContributionRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();

        $user = $request->user();

        $contribution = Contribution::query()
            ->whereIn('statut', ['en_attente', 'en_revue'])
            ->findOrFail($id);

        if ($contribution->utilisateur_id === $user->id) {
            return response()->json([
                'message' => 'Vous ne pouvez pas reviewer votre propre contribution.',
            ], 403);
        }

        $alreadyReviewed = ContributionReview::query()
            ->where('contribution_id', $contribution->id)
            ->where('reviewer_id', $user->id)
            ->exists();

        if ($alreadyReviewed) {
            return response()->json([
                'message' => 'Vous avez deja reviewe cette contribution.',
            ], 409);
        }

        $review = DB::transaction(function () use ($validated, $user, $contribution): ContributionReview {
            $review = ContributionReview::create([
                'contribution_id' => $contribution->id,
                'reviewer_id' => $user->id,
                'note_veracite' => $validated['note_veracite'],
                'is_valid' => $validated['is_valid'],
                'commentaire' => $validated['commentaire'] ?? null,
            ]);

            if ($contribution->statut === 'en_attente') {
                $contribution->update(['statut' => 'en_revue']);
            }

            $this->consensusService->checkAndApply($contribution);

            return $review;
        });

        return response()->json([
            'message' => 'Review enregistree avec succes.',
            'review' => $review,
            'contribution' => ContributionResource::make($contribution->fresh()),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $contribution = Contribution::query()
            ->with(['utilisateur:id,name,telephone', 'reviews.reviewer:id,name,telephone', 'datasets:id,nom,version'])
            ->withCount('reviews')
            ->findOrFail($id);

        if ($contribution->utilisateur_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'message' => 'Acces refuse a cette contribution.',
            ], 403);
        }

        if (!empty($contribution->fichier_url)) {
            $baseUrl = (string) config('filesystems.disks.contributions.url', '');
            $contribution->setAttribute(
                'fichier_url',
                rtrim($baseUrl, '/') . '/' . ltrim((string) $contribution->fichier_url, '/')
            );
        }

        return response()->json([
            'contribution' => ContributionResource::make($contribution),
        ]);
    }
}
