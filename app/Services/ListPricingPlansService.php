<?php

declare(strict_types=1);

namespace App\Services;

use DomainException;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Collection;

class ListPricingPlansService
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly ConfigRepository $config,
    ) {}

    /**
     * @return Collection<int, object>
     */
    public function getAll(): Collection
    {
        $currentPlan = $this->getCurrentPlan();

        return collect($this->getCatalog())
            ->map(fn(array $plan): object => (object) $this->hydratePlan($plan, $currentPlan))
            ->values();
    }

    /**
     * @throws DomainException
     */
    public function getById(string $planId): object
    {
        $plan = collect($this->getCatalog())->firstWhere('id', $planId);

        if ($plan === null) {
            throw new DomainException('Plano não encontrado.', 404);
        }

        return (object) $this->hydratePlan($plan, $this->getCurrentPlan());
    }

    public function getCurrentPlan(): ?string
    {
        return $this->tenantContext->isSet()
            ? $this->tenantContext->get()->plan
            : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getCatalog(): array
    {
        /** @var array<int, array<string, mixed>> $plans */
        $plans = $this->config->get('pricing.plans', []);

        return $plans;
    }

    /**
     * @param array<string, mixed> $plan
     * @return array<string, mixed>
     */
    private function hydratePlan(array $plan, ?string $currentPlan): array
    {
        if (($plan['id'] ?? null) === 'pro') {
            $plan['price_id'] = $this->config->get('services.stripe.price_id');
        }

        $plan['is_current'] = ($plan['id'] ?? null) === $currentPlan;
        $plan['price_formatted'] = $this->formatPrice(
            price: (int) ($plan['price'] ?? 0),
            currency: (string) ($plan['currency'] ?? 'BRL'),
        );

        return $plan;
    }

    private function formatPrice(int $price, string $currency): string
    {
        if ($price <= 0) {
            return 'Grátis';
        }

        return match ($currency) {
            'BRL' => 'R$ ' . number_format($price / 100, 2, ',', '.'),
            default => number_format($price / 100, 2, '.', ',') . ' ' . $currency,
        };
    }
}
