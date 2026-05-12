<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index(Request $request): View
    {
        $perPage = min((int) $request->integer('per_page', 20), 100);

        $logs = AuditLog::query()
            ->when($request->filled('user_id'), fn($query) => $query->where('user_id', (int) $request->user_id))
            ->when($request->filled('acao'), fn($query) => $query->where('acao', $request->string('acao')->toString()))
            ->when($request->filled('nivel'), fn($query) => $query->where('nivel', $request->string('nivel')->toString()))
            ->when($request->filled('categoria'), fn($query) => $query->where('categoria', $request->string('categoria')->toString()))
            ->when($request->filled('componente'), fn($query) => $query->where('componente', 'like', '%' . $request->string('componente')->toString() . '%'))
            ->when($request->filled('tabela'), fn($query) => $query->where('tabela', 'like', '%' . $request->string('tabela')->toString() . '%'))
            ->when($request->filled('inicio'), fn($query) => $query->whereDate('dt_criacao', '>=', $request->string('inicio')->toString()))
            ->when($request->filled('fim'), fn($query) => $query->whereDate('dt_criacao', '<=', $request->string('fim')->toString()))
            ->orderByDesc('dt_criacao')
            ->paginate($perPage)
            ->withQueryString();

        return view('audit.index', [
            'logs' => $logs,
            'filters' => $request->only([
                'user_id',
                'acao',
                'nivel',
                'categoria',
                'componente',
                'tabela',
                'inicio',
                'fim',
                'per_page',
            ]),
        ]);
    }

    public function show(AuditLog $auditLog): View
    {
        return view('audit.show', [
            'log' => $auditLog,
        ]);
    }
}
