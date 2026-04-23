<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@dataloop.ci')],
            [
                'name' => env('ADMIN_NAME', 'Admin DataLoop'),
                'telephone' => env('ADMIN_TELEPHONE', '0700000001'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'Admin@1234')),
                'role' => 'admin',
                'statut' => 'actif',
                'motif_statut' => null,
                'score_confiance' => 100,
                'solde_virtuel' => 0,
            ]
        );
    }
}
