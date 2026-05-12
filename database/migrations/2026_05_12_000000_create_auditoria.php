<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id();
            $table->timestamp('dt_criacao')->useCurrent();
            $table->string('ip', 45);
            $table->string('user_agent', 500)->nullable();
            $table->string('session_id', 128)->nullable();
            $table->unsignedInteger('user_id');
            $table->string('user_name', 100);
            $table->string('componente', 100)->default('sicoob');
            $table->string('categoria', 50)->default('geral');
            $table->string('acao', 50)->default('custom');
            $table->string('http_method', 10)->default('GET');
            $table->string('tabela', 64)->nullable();
            $table->integer('registro_id')->nullable();
            $table->unsignedBigInteger('fk_referencia')->nullable();
            $table->text('descricao')->nullable();
            $table->unsignedInteger('duracao_ms')->nullable();
            $table->enum('nivel', ['info', 'warning', 'error'])->default('info');
            $table->json('dados_antes')->nullable();
            $table->json('dados_depois')->nullable();
            $table->string('request_uri', 1024)->nullable();

            $table->index('dt_criacao');
            $table->index('user_id');
            $table->index('session_id');
            $table->index('acao');
            $table->index('componente');
            $table->index('categoria');
            $table->index('http_method');
            $table->index('tabela');
            $table->index('nivel');
            $table->index('fk_referencia');
            $table->index('duracao_ms');
        });

        DB::statement('CREATE INDEX idx_audit_user_agent ON auditoria (user_agent(100))');
        DB::statement('CREATE INDEX idx_audit_uri ON auditoria (request_uri(200))');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX idx_audit_user_agent ON auditoria');
        DB::statement('DROP INDEX idx_audit_uri ON auditoria');

        Schema::dropIfExists('auditoria');
    }
};
