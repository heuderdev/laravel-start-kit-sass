<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $r = is_array($this->resource) ? $this->resource : (array) $this->resource;

        return [
            'id'              => $r['id'],
            'name'            => $r['name'],
            'price'           => $r['price'],
            'price_formatted' => $r['price_formatted'] ?? null,
            'currency'        => $r['currency'],
            'interval'        => $r['interval'],
            'trial_days'      => $r['trial_days'],
            'features'        => $r['features'],
            'is_current'      => $r['is_current'] ?? false,
            'cta_label'       => $r['cta_label'],
            'cta_action'      => $r['cta_action'],
            // price_id exposto somente para uso interno do frontend no checkout
            // nunca expor a sk_ do stripe aqui
            'price_id'        => $r['price_id'] ?? null,
        ];
    }
}
