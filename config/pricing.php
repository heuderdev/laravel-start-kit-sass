<?php

declare(strict_types=1);

return [
    'plans' => [
        [
            'id' => 'free',
            'name' => 'Free',
            'price' => 0,
            'currency' => 'BRL',
            'interval' => null,
            'trial_days' => 0,
            'features' => [
                'Até 1 usuário',
                'Funcionalidades básicas',
                'Suporte via e-mail',
            ],
            'cta_label' => 'Plano atual',
            'cta_action' => null,
            'price_id' => null,
        ],
        [
            'id' => 'pro',
            'name' => 'Pro',
            'price' => 9700,
            'currency' => 'BRL',
            'interval' => 'month',
            'trial_days' => 3,
            'features' => [
                'Usuários ilimitados',
                'Todas as funcionalidades',
                'Suporte prioritário',
                'Acesso à API',
            ],
            'cta_label' => 'Assinar agora',
            'cta_action' => 'subscription.checkout',
            'price_id' => null,
        ],
    ],
];
