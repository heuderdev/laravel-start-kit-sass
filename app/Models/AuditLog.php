<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AuditLog extends Model
{
    protected $table = 'auditoria';

    const CREATED_AT = 'dt_criacao';
    const UPDATED_AT = null;

    public $timestamps = false;

    protected $fillable = [
        'ip',
        'user_agent',
        'session_id',
        'user_id',
        'user_name',
        'componente',
        'categoria',
        'acao',
        'http_method',
        'tabela',
        'registro_id',
        'fk_referencia',
        'descricao',
        'duracao_ms',
        'nivel',
        'dados_antes',
        'dados_depois',
        'request_uri',
    ];

    protected $casts = [
        'dt_criacao'   => 'datetime',
        'dados_antes'  => 'array',
        'dados_depois' => 'array',
    ];

    public function scopeDeUsuario(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopePorAcao(Builder $query, string $acao): Builder
    {
        return $query->where('acao', $acao);
    }

    public function scopePorNivel(Builder $query, string $nivel): Builder
    {
        return $query->where('nivel', $nivel);
    }

    public function scopePorTabela(Builder $query, string $tabela): Builder
    {
        return $query->where('tabela', $tabela);
    }

    public function scopePorPeriodo(Builder $query, string $inicio, string $fim): Builder
    {
        return $query->whereBetween('dt_criacao', [$inicio, $fim]);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
