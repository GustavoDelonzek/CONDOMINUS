<?php

namespace Database\Seeders;

use App\Models\Block;
use App\Models\CommonArea;
use App\Models\Condominium;
use App\Models\Membership;
use App\Models\Reservation;
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
        $this->seedCommonAreas();
        $this->seedReservations();
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

    /**
     * Áreas comuns do Residencial Aurora com booking_rules variadas —
     * cobre requires_approval true/false, com/sem taxa, e uma área inativa
     * (pra exercitar o badge "Inativa" na tela de Áreas Comuns).
     */
    private function seedCommonAreas(): void
    {
        $condo = Condominium::where('name', 'Residencial Aurora')->first();

        $areas = [
            [
                'name' => 'Salão de Festas',
                'capacity' => 80,
                'is_active' => true,
                'booking_rules' => [
                    'opens_at' => '10:00',
                    'closes_at' => '23:00',
                    'min_advance_hours' => 48,
                    'max_duration_hours' => 8,
                    'max_reservations_per_unit_per_month' => 2,
                    'requires_approval' => true,
                    'fee' => 150.00,
                ],
            ],
            [
                'name' => 'Churrasqueira',
                'capacity' => 25,
                'is_active' => true,
                'booking_rules' => [
                    'opens_at' => '11:00',
                    'closes_at' => '22:00',
                    'min_advance_hours' => 24,
                    'max_duration_hours' => 4,
                    'max_reservations_per_unit_per_month' => 4,
                    'requires_approval' => false,
                    'fee' => 80.00,
                ],
            ],
            [
                'name' => 'Academia',
                'capacity' => 15,
                'is_active' => true,
                'booking_rules' => [
                    'opens_at' => '06:00',
                    'closes_at' => '23:00',
                    'requires_approval' => false,
                ],
            ],
            [
                'name' => 'Quadra Poliesportiva',
                'capacity' => 20,
                'is_active' => true,
                'booking_rules' => [
                    'opens_at' => '09:00',
                    'closes_at' => '22:00',
                    'min_advance_hours' => 12,
                    'max_duration_hours' => 2,
                    'requires_approval' => true,
                    'fee' => 20.00,
                ],
            ],
            [
                'name' => 'Espaço Gourmet',
                'capacity' => 40,
                'is_active' => false,
                'booking_rules' => [
                    'requires_approval' => true,
                    'fee' => 200.00,
                ],
            ],
        ];

        foreach ($areas as $area) {
            CommonArea::firstOrCreate(
                ['condominium_id' => $condo->id, 'name' => $area['name']],
                [
                    'capacity' => $area['capacity'],
                    'is_active' => $area['is_active'],
                    'booking_rules' => $area['booking_rules'],
                ]
            );
        }
    }

    /**
     * Reservas com status variados (pending/confirmed/denied/canceled/completed)
     * pra exercitar a fila de aprovação, o filtro por status e "outras reservas
     * no dia". Cria 3 moradores extras, cada um com sua própria unidade, pra
     * o painel mostrar solicitantes diferentes.
     */
    private function seedReservations(): void
    {
        $condo = Condominium::where('name', 'Residencial Aurora')->first();
        $units = Unit::where('condominium_id', $condo->id)->orderBy('number')->get();

        $residentsData = [
            ['email' => 'bruno.morador@teste.com', 'phone' => '11977777001', 'name' => 'Bruno Morador', 'unit' => $units[0]],
            ['email' => 'carla.moradora@teste.com', 'phone' => '11977777002', 'name' => 'Carla Moradora', 'unit' => $units[1]],
            ['email' => 'diego.morador@teste.com', 'phone' => '11977777003', 'name' => 'Diego Morador', 'unit' => $units[2]],
        ];

        $residents = [];
        foreach ($residentsData as $data) {
            $user = $this->makeUser($data['email'], $data['phone'], $data['name'], self::DEFAULT_PASSWORD);
            Membership::firstOrCreate([
                'user_id' => $user->id,
                'condominium_id' => $condo->id,
                'role' => 'resident',
            ], ['unit_id' => $data['unit']->id, 'is_active' => true]);

            $residents[] = ['user' => $user, 'unit' => $data['unit']];
        }

        $salao = CommonArea::where('condominium_id', $condo->id)->where('name', 'Salão de Festas')->first();
        $churrasqueira = CommonArea::where('condominium_id', $condo->id)->where('name', 'Churrasqueira')->first();
        $academia = CommonArea::where('condominium_id', $condo->id)->where('name', 'Academia')->first();
        $quadra = CommonArea::where('condominium_id', $condo->id)->where('name', 'Quadra Poliesportiva')->first();

        $reservations = [
            // Pendentes — aguardando aprovação do síndico.
            ['area' => $salao, 'resident' => $residents[0], 'start' => now()->addDays(5)->setTime(18, 0), 'hours' => 6, 'status' => 'pending', 'notes' => 'Aniversário de 10 anos, cerca de 40 convidados.'],
            ['area' => $quadra, 'resident' => $residents[1], 'start' => now()->addDays(2)->setTime(16, 0), 'hours' => 2, 'status' => 'pending', 'notes' => null],

            // Confirmadas — duas na Churrasqueira no mesmo dia, pra testar "outras reservas no dia".
            ['area' => $churrasqueira, 'resident' => $residents[2], 'start' => now()->addDays(3)->setTime(12, 0), 'hours' => 4, 'status' => 'confirmed', 'notes' => null],
            ['area' => $churrasqueira, 'resident' => $residents[0], 'start' => now()->addDays(3)->setTime(18, 0), 'hours' => 3, 'status' => 'confirmed', 'notes' => 'Vamos usar caixa de som própria.'],
            ['area' => $academia, 'resident' => $residents[1], 'start' => now()->addDay()->setTime(7, 0), 'hours' => 1, 'status' => 'confirmed', 'notes' => null],

            // Recusada.
            ['area' => $salao, 'resident' => $residents[2], 'start' => now()->addDays(10)->setTime(20, 0), 'hours' => 8, 'status' => 'denied', 'notes' => 'Data já reservada para manutenção do salão.'],

            // Cancelada.
            ['area' => $quadra, 'resident' => $residents[0], 'start' => now()->addDays(7)->setTime(9, 0), 'hours' => 2, 'status' => 'canceled', 'notes' => null],

            // Concluídas — no passado.
            ['area' => $churrasqueira, 'resident' => $residents[1], 'start' => now()->subDays(5)->setTime(12, 0), 'hours' => 4, 'status' => 'completed', 'notes' => null],
            ['area' => $academia, 'resident' => $residents[2], 'start' => now()->subDays(2)->setTime(6, 0), 'hours' => 1, 'status' => 'completed', 'notes' => null],
        ];

        foreach ($reservations as $r) {
            $start = $r['start'];
            $end = $start->copy()->addHours($r['hours']);

            Reservation::firstOrCreate(
                [
                    'common_area_id' => $r['area']->id,
                    'unit_id' => $r['resident']['unit']->id,
                    'start_time' => $start,
                ],
                [
                    'condominium_id' => $condo->id,
                    'user_id' => $r['resident']['user']->id,
                    'end_time' => $end,
                    'status' => $r['status'],
                    'notes' => $r['notes'],
                ]
            );
        }
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
