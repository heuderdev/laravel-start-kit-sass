<?php

// app/Jobs/CreateStripeCustomer.php
namespace App\Jobs;

use App\Models\Tenant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CreateStripeCustomer implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 10; // segundos entre tentativas

    public function __construct(
        private Tenant $tenant,
        private string $name,
        private string $email,
    ) {}

    public function handle(): void
    {
        // Evita criar duplicado se já tiver stripe_id
        if ($this->tenant->stripe_id) {
            return;
        }

        $this->tenant->createAsStripeCustomer([
            'name'  => $this->name,
            'email' => $this->email,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Falha ao criar customer no Stripe', [
            'tenant_id' => $this->tenant->id,
            'email'     => $this->email,
            'error'     => $exception->getMessage(),
        ]);
    }
}
