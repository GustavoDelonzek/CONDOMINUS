<?php

namespace Database\Seeders;

use App\Models\Membership;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AdminCompany;
use App\Models\Condominium;
use App\Models\Block;


class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminCompany = AdminCompany::factory()->create([
            'name' => 'Admin Tech Gestão',
        ]);

        $condo = Condominium::create([
            'admin_company_id' => $adminCompany->id,
            'name' => 'Residencial Aurora',
            'address_full' => 'Rua das Flores, 123',
        ]);

        $blocks = ['Bloco A', 'Bloco B'];
        foreach ($blocks as $blockName) {
            $block = Block::create([
                'condominium_id' => $condo->id,
                'name' => $blockName,
            ]);

            Unit::factory(5)->create([
                'condominium_id' => $condo->id,
                'block_id' => $block->id,
            ]);
        }

        $syndicUser = User::create([
            'full_name' => 'Carlos Síndico',
            'phone_number' => '11999999999',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'email' => 'teste@teste.com'
        ]);

        Membership::create([
            'user_id' => $syndicUser->id,
            'condominium_id' => $condo->id,
            'role' => 'syndic',
        ]);

        $residentUser = User::create([
            'full_name' => 'Ana Moradora',
            'phone_number' => '11888888888',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'email' => 'testeResident@teste.com'
        ]);

        $randomUnit = Unit::where('condominium_id', $condo->id)->first();

        Membership::create([
            'user_id' => $residentUser->id,
            'condominium_id' => $condo->id,
            'unit_id' => $randomUnit->id,
            'role' => 'resident',
        ]);
    }
}
