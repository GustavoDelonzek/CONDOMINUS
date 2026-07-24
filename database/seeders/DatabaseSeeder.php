<?php

namespace Database\Seeders;

use App\Models\Block;
use App\Models\Condominium;
use App\Models\Membership;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Seeder único do projeto: rode sempre `php artisan db:seed` (sem --class).
 * Idempotente — usa firstOrCreate, pode rodar quantas vezes quiser sem
 * duplicar dados nem quebrar por unique constraint.
 *
 * Vai crescendo por seção conforme os módulos forem sendo implementados.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    private const DEFAULT_PASSWORD = 'password123';
    private const QA_PASSWORD = 'senha123';

    public function run(): void
    {
        $this->seedResidencialAurora();
        $this->seedAuthQaUsers();
    }

    /**
     * Dados de dev "reais": um condomínio com blocos/unidades geradas e um
     * síndico + morador básicos.
     */
    private function seedResidencialAurora(): void
    {
        $condo = Condominium::firstOrCreate(
            ['name' => 'Residencial Aurora'],
            ['address_full' => 'Rua das Flores, 123']
        );

        $blocks = ['Bloco A', 'Bloco B'];
        foreach ($blocks as $blockName) {
            $block = Block::firstOrCreate([
                'condominium_id' => $condo->id,
                'name' => $blockName,
            ]);

            if ($block->units()->count() === 0) {
                Unit::factory(5)->create([
                    'condominium_id' => $condo->id,
                    'block_id' => $block->id,
                ]);
            }
        }

        $syndicUser = $this->makeUser(
            'teste@teste.com',
            '11999999999',
            'Carlos Síndico',
            self::DEFAULT_PASSWORD
        );

        Membership::firstOrCreate([
            'user_id' => $syndicUser->id,
            'condominium_id' => $condo->id,
            'role' => 'syndic',
        ]);

        $residentUser = $this->makeUser(
            'testeResident@teste.com',
            '11888888888',
            'Ana Moradora',
            self::DEFAULT_PASSWORD
        );

        $randomUnit = Unit::where('condominium_id', $condo->id)->first();

        Membership::firstOrCreate([
            'user_id' => $residentUser->id,
            'condominium_id' => $condo->id,
            'role' => 'resident',
        ], [
            'unit_id' => $randomUnit->id,
        ]);
    }

    /**
     * Usuários pensados pra exercitar cenários de autenticação/membership
     * (login, logout, /me): múltiplas memberships, membership inativa, super
     * admin sem membership. Senha de todos: self::QA_PASSWORD.
     */
    private function seedAuthQaUsers(): void
    {
        $condoA = Condominium::firstOrCreate(
            ['name' => 'QA Condomínio A'],
            ['address_full' => 'Rua de Testes, 100']
        );

        $condoB = Condominium::firstOrCreate(
            ['name' => 'QA Condomínio B'],
            ['address_full' => 'Rua de Testes, 200']
        );

        $blockA = Block::firstOrCreate([
            'condominium_id' => $condoA->id,
            'name' => 'Bloco QA',
        ]);

        $unitA = Unit::firstOrCreate(
            ['condominium_id' => $condoA->id, 'block_id' => $blockA->id, 'number' => '101'],
            ['floor' => '1']
        );

        // Síndico com uma única membership ativa em um condomínio.
        $syndic = $this->makeUser('qa.syndic@teste.com', '+5541900000001', 'QA Síndico', self::QA_PASSWORD);
        Membership::firstOrCreate([
            'user_id' => $syndic->id,
            'condominium_id' => $condoA->id,
            'role' => 'syndic',
        ], ['is_active' => true]);

        // Admin da empresa administradora, com membership ativa em um condomínio.
        $companyAdmin = $this->makeUser('qa.company_admin@teste.com', '+5541900000006', 'QA Company Admin', self::QA_PASSWORD);
        Membership::firstOrCreate([
            'user_id' => $companyAdmin->id,
            'condominium_id' => $condoA->id,
            'role' => 'company_admin',
        ], ['is_active' => true]);

        // Morador com unidade vinculada.
        $resident = $this->makeUser('qa.resident@teste.com', '+5541900000002', 'QA Morador', self::QA_PASSWORD);
        Membership::firstOrCreate([
            'user_id' => $resident->id,
            'condominium_id' => $condoA->id,
            'unit_id' => $unitA->id,
            'role' => 'resident',
        ], ['is_active' => true]);

        // Usuário com memberships em dois condomínios (papéis diferentes) —
        // útil para testar o seletor de condomínio (Fase 2) a partir do /me.
        $multi = $this->makeUser('qa.multi@teste.com', '+5541900000003', 'QA Multi Condomínio', self::QA_PASSWORD);
        Membership::firstOrCreate([
            'user_id' => $multi->id,
            'condominium_id' => $condoA->id,
            'role' => 'syndic',
        ], ['is_active' => true]);
        Membership::firstOrCreate([
            'user_id' => $multi->id,
            'condominium_id' => $condoB->id,
            'role' => 'resident',
        ], ['is_active' => true]);

        // Usuário só com membership inativa — /me deve devolver memberships: [].
        $inactive = $this->makeUser('qa.inactive@teste.com', '+5541900000004', 'QA Membership Inativa', self::QA_PASSWORD);
        Membership::firstOrCreate([
            'user_id' => $inactive->id,
            'condominium_id' => $condoA->id,
            'role' => 'resident',
        ], ['is_active' => false]);

        // Super admin, sem membership nenhuma.
        $this->makeUser('qa.superadmin@teste.com', '+5541900000005', 'QA Super Admin', self::QA_PASSWORD, isSuperAdmin: true);
    }

    private function makeUser(
        string $email,
        string $phone,
        string $fullName,
        string $password,
        bool $isSuperAdmin = false
    ): User {
        return User::firstOrCreate(
            ['email' => $email],
            [
                'full_name' => $fullName,
                'phone_number' => $phone,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'is_super_admin' => $isSuperAdmin,
            ]
        );
    }
}
