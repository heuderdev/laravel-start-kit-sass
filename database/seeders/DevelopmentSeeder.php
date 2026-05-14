<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Services\ProvisionNewAccount;
use App\Services\TenantMembershipService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DevelopmentSeeder extends Seeder
{
    public function __construct(
        private readonly ProvisionNewAccount $provisionNewAccount,
        private readonly TenantMembershipService $membershipService,
    ) {}

    public function run(): void
    {
        // ─── 1. Super Admin com seu próprio tenant ───────────────────────────

        $admin = $this->provisionNewAccount->handle(
            name: 'Super Admin',
            email: 'super-admin@sass.com.br',
            password: Hash::make('password'),
            is_super_admin: true
        );

        $adminTenant = $admin->defaultTenant();
        $adminTenant->update([
            'name' => 'Admin Workspace',
            'slug' => 'admin-workspace',
        ]);

        // ─── 2. Owner com Tenant Free ────────────────────────────────────────

        $owner = $this->provisionNewAccount->handle(
            name: 'John Owner',
            email: 'owner@dev.local',
            password: Hash::make('password'),
            is_super_admin: false
        );

        $freeTenant = $owner->defaultTenant();
        $freeTenant->update([
            'name' => 'Acme Corp (Free)',
            'slug' => 'acme-free',
        ]);

        // ─── 3. Manager com Tenant Pro + Stripe Fake ─────────────────────────

        $manager = $this->provisionNewAccount->handle(
            name: 'Jane Manager',
            email: 'manager@dev.local',
            password: Hash::make('password'),
            is_super_admin: false
        );

        $proTenant = $manager->defaultTenant();
        $proTenant->update([
            'name' => 'TechCo (Pro)',
            'slug' => 'techco-pro',
            'plan' => 'pro',
            'stripe_id' => 'cus_test_' . fake()->bothify('??????????'),
        ]);

        // Criar subscription fake (simula plano ativo)
        $proTenant->subscriptions()->create([
            'type' => 'default',
            'stripe_id' => 'sub_test_' . fake()->bothify('??????????'),
            'stripe_status' => 'active',
            'stripe_price' => config('cashier.prices.pro') ?? 'price_test_pro',
            'quantity' => 1,
            'trial_ends_at' => null,
            'ends_at' => null,
        ]);

        // ─── 4. Tenant com Trial Ativo ───────────────────────────────────────

        $trialUser = $this->provisionNewAccount->handle(
            name: 'Bob Trial',
            email: 'trial@dev.local',
            password: Hash::make('password'),
            is_super_admin: false
        );

        $trialTenant = $trialUser->defaultTenant();
        $trialTenant->update([
            'name' => 'StartupX (Trial)',
            'slug' => 'startupx-trial',
            'plan' => 'pro',
            'trial_ends_at' => now()->addDays(14),
        ]);

        // ─── 5. Tenant com Bypass Ativado ────────────────────────────────────

        $bypassUser = $this->provisionNewAccount->handle(
            name: 'Alice Partner',
            email: 'partner@dev.local',
            password: Hash::make('password'),
            is_super_admin: false
        );

        $bypassTenant = $bypassUser->defaultTenant();
        $bypassTenant->update([
            'name' => 'Partner Corp (Bypass)',
            'slug' => 'partner-bypass',
            'plan' => 'free',
            'bypass_plan_limits' => true,
            'bypass_plan_limits_data_limite' => now()->addMonths(6),
        ]);

        // ─── 6. Adicionar Members ao Tenant Pro ──────────────────────────────

        $member1 = User::create([
            'name' => 'Mike Member',
            'email' => 'member1@dev.local',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $this->membershipService->attachUserToTenant(
            user: $member1,
            tenant: $proTenant,
            role: 'member',
            isDefault: true,
            status: 'active',
        );

        $member2 = User::create([
            'name' => 'Sara Admin',
            'email' => 'member2@dev.local',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $this->membershipService->attachUserToTenant(
            user: $member2,
            tenant: $proTenant,
            role: 'admin',
            isDefault: false,
            status: 'active',
        );

        // ─── 7. Pending Invitation no Tenant Free ────────────────────────────

        $freeTenant->invitations()->create([
            'email' => 'invited@example.com',
            'role' => 'member',
            'token' => fake()->uuid(),
            'expires_at' => now()->addDays(7),
        ]);

        DB::table('auditoria')->insert([
            [
                'dt_criacao' => now(),
                'ip' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Seeder)',
                'session_id' => 'seed_session_' . fake()->uuid(),
                'user_id' => $manager->id,
                'user_name' => $manager->name,
                'componente' => 'subscription',
                'categoria' => 'billing',
                'acao' => 'subscription.created',
                'http_method' => 'POST',
                'tabela' => 'subscriptions',
                'registro_id' => $proTenant->subscriptions()->first()?->id,
                'fk_referencia' => $proTenant->id,
                'descricao' => 'Subscription Pro criada via seeder',
                'duracao_ms' => rand(50, 200),
                'nivel' => 'info',
                'dados_antes' => json_encode(['plan' => 'free']),
                'dados_depois' => json_encode(['plan' => 'pro', 'stripe_id' => $proTenant->stripe_id]),
                'request_uri' => '/api/subscription/checkout',
            ],
            [
                'dt_criacao' => now()->subMinutes(5),
                'ip' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Seeder)',
                'session_id' => 'seed_session_' . fake()->uuid(),
                'user_id' => $owner->id,
                'user_name' => $owner->name,
                'componente' => 'members',
                'categoria' => 'team',
                'acao' => 'member.invited',
                'http_method' => 'POST',
                'tabela' => 'tenant_invitations',
                'registro_id' => $freeTenant->invitations()->first()?->id,
                'fk_referencia' => $freeTenant->id,
                'descricao' => 'Convite enviado para invited@example.com',
                'duracao_ms' => rand(100, 300),
                'nivel' => 'info',
                'dados_antes' => null,
                'dados_depois' => json_encode([
                    'email' => 'invited@example.com',
                    'role' => 'member',
                ]),
                'request_uri' => '/api/invitations',
            ],
            [
                'dt_criacao' => now()->subHours(2),
                'ip' => '192.168.1.100',
                'user_agent' => 'PostmanRuntime/7.32.0',
                'session_id' => null,
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'componente' => 'admin',
                'categoria' => 'security',
                'acao' => 'user.promoted',
                'http_method' => 'POST',
                'tabela' => 'users',
                'registro_id' => $admin->id,
                'fk_referencia' => null,
                'descricao' => 'Usuário promovido a Super Admin',
                'duracao_ms' => 85,
                'nivel' => 'warning',
                'dados_antes' => json_encode(['is_super_admin' => false]),
                'dados_depois' => json_encode(['is_super_admin' => true]),
                'request_uri' => '/admin/users/' . $admin->id . '/promote-super-admin',
            ],
        ]);

        // ─── 9. Output Console ────────────────────────────────────────────────

        $this->command->newLine();
        $this->command->info('✅ Development data seeded successfully!');
        $this->command->newLine();

        $this->command->table(
            ['Email', 'Password', 'Role', 'Tenant', 'Plan', 'Status'],
            [
                ['super-admin@sass.com.br', 'password', 'Super Admin', 'Admin Workspace', 'free', 'owner'],
                ['owner@dev.local', 'password', 'Owner', 'Acme Corp', 'free', 'owner'],
                ['manager@dev.local', 'password', 'Owner', 'TechCo', 'pro', 'active subscription'],
                ['trial@dev.local', 'password', 'Owner', 'StartupX', 'pro', 'trial (14 days left)'],
                ['partner@dev.local', 'password', 'Owner', 'Partner Corp', 'free', 'bypass enabled'],
                ['member1@dev.local', 'password', 'Member', 'TechCo', 'pro', 'member'],
                ['member2@dev.local', 'password', 'Admin', 'TechCo', 'pro', 'admin'],
            ]
        );

        $this->command->newLine();
        $this->command->warn('⚠️  Tokens Sanctum: faça login via API para obter tokens');
        $this->command->info('📧 Invitation pending: invited@example.com (Acme Corp)');
        $this->command->newLine();
    }
}
