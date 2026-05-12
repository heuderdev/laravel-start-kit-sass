<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuditService
{
    /**
     * Registra uma ação de auditoria.
     *
     * @param array{
     *   acao?: string,
     *   tabela?: string,
     *   registro_id?: int,
     *   descricao?: string,
     *   dados_antigos?: array,
     *   dados_novos?: array,
     *   componente?: string,
     *   categoria?: string,
     *   nivel?: string,
     *   fk_referencia?: int,
     *   duracao_ms?: int,
     * } $data
     */
    public function log(array $data): bool
    {
        try {
            AuditLog::create([
                'ip'            => request()->ip() ?? 'unknown',
                'user_agent'    => request()->userAgent(),
                'session_id'    => session()->getId(),
                'user_id'       => (int) auth()->id(),
                'user_name'     => auth()->user()?->name ?? 'system',
                'componente'    => $data['componente']    ?? 'sicoob',
                'categoria'     => $data['categoria']     ?? 'geral',
                'acao'          => $data['acao']          ?? 'custom',
                'http_method'   => $this->resolveHttpMethod(),
                'tabela'        => $data['tabela']        ?? null,
                'registro_id'   => $data['registro_id']   ?? null,
                'fk_referencia' => $data['fk_referencia'] ?? null,
                'descricao'     => $data['descricao']     ?? '',
                'duracao_ms'    => $data['duracao_ms']    ?? null,
                'nivel'         => $data['nivel']         ?? 'info',
                'dados_antes'   => $data['dados_antigos'] ?? null,
                'dados_depois'  => $data['dados_novos']   ?? null,
                'request_uri'   => request()->getRequestUri(),
            ]);

            return true;
        } catch (Throwable $e) {
            Log::error('Erro ao gravar audit_log: ' . $e->getMessage(), [
                'exception' => $e,
                'data'      => $data,
            ]);

            return false;
        }
    }

    /**
     * Query paginada com filtros opcionais.
     *
     * @param array{
     *   user_id?: int,
     *   acao?: string,
     *   nivel?: string,
     *   tabela?: string,
     *   componente?: string,
     *   categoria?: string,
     *   fk_referencia?: int,
     *   periodo?: array{0: string, 1: string},
     *   per_page?: int,
     * } $filters
     */
    public function query(array $filters = [])
    {
        $q = AuditLog::query()->orderByDesc('dt_criacao');

        if (!empty($filters['user_id']))       $q->deUsuario($filters['user_id']);
        if (!empty($filters['acao']))          $q->porAcao($filters['acao']);
        if (!empty($filters['nivel']))         $q->porNivel($filters['nivel']);
        if (!empty($filters['tabela']))        $q->porTabela($filters['tabela']);
        if (!empty($filters['componente']))    $q->where('componente', $filters['componente']);
        if (!empty($filters['categoria']))     $q->where('categoria', $filters['categoria']);
        if (!empty($filters['fk_referencia'])) $q->where('fk_referencia', $filters['fk_referencia']);

        if (!empty($filters['periodo']) && count($filters['periodo']) === 2) {
            $q->porPeriodo($filters['periodo'][0], $filters['periodo'][1]);
        }

        return $q->paginate($filters['per_page'] ?? 50);
    }

    /**
     * Carrega qualquer item de qualquer tabela — equivalente ao loadItem() do Joomla.
     *
     * @param array{
     *   table: string,
     *   fields?: string|array<string>,
     *   where: array<string, mixed>,
     * } $options
     */
    public function loadItem(array $options): ?array
    {
        try {
            $fields = $options['fields'] ?? ['*'];

            $row = DB::table($options['table'])
                ->select(is_string($fields) ? [$fields] : $fields)
                ->where($options['where'])
                ->first();

            return $row ? (array) $row : null;
        } catch (Throwable $e) {
            Log::error('Erro ao carregar item para auditoria: ' . $e->getMessage(), [
                'options' => $options,
            ]);

            return null;
        }
    }

    private function resolveHttpMethod(): string
    {
        return app()->runningInConsole() ? 'CLI' : strtoupper(request()->method());
    }
}
