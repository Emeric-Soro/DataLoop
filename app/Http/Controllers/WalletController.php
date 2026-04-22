<?php

namespace App\Http\Controllers;

use App\Http\Requests\WithdrawRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function balance(Request $request): JsonResponse
    {
        return response()->json([
            'solde_virtuel' => (float) $request->user()->solde_virtuel,
        ]);
    }

    public function transactions(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = max(1, min((int) $request->query('per_page', 20), 100));

        $query = Transaction::query()
            ->where('utilisateur_id', $user->id)
            ->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->string('from_date'));
        }

        return TransactionResource::collection($query->paginate($perPage))->response();
    }

    public function withdraw(WithdrawRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = $request->user();

        $result = DB::transaction(function () use ($user, $validated): array {
            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $soldeAvant = (float) $lockedUser->solde_virtuel;
            $montant = (float) $validated['montant'];

            if ($soldeAvant < $montant) {
                return [null, null, 'Solde insuffisant.'];
            }

            $soldeApres = $soldeAvant - $montant;

            $lockedUser->update([
                'solde_virtuel' => $soldeApres,
            ]);

            $transaction = Transaction::create([
                'utilisateur_id' => $lockedUser->id,
                'annotation_id' => null,
                'type' => 'retrait',
                'libelle' => 'Demande de retrait',
                'montant' => $montant,
                'solde_avant' => $soldeAvant,
                'solde_apres' => $soldeApres,
                'reference_tache' => 'methode:' . $validated['methode_paiement'],
            ]);

            return [$lockedUser, $transaction, null];
        });

        if ($result[2]) {
            return response()->json([
                'message' => $result[2],
            ], 422);
        }

        return response()->json([
            'message' => 'Demande de retrait enregistree.',
            'solde_virtuel' => (float) $result[0]->solde_virtuel,
            'transaction' => TransactionResource::make($result[1]),
        ], 201);
    }
}
