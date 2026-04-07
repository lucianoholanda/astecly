<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    /**
     * O Laravel chama este método automaticamente para registrar os eventos do Model.
     */
    protected static function bootLogsActivity()
    {
        static::created(fn (Model $model) => $model->logActivity('created'));
        static::updated(fn (Model $model) => $model->logActivity('updated'));
        static::deleted(fn (Model $model) => $model->logActivity('deleted'));
        
        // Verifica se o Model usa SoftDeletes antes de tentar registrar o evento restored
        if (method_exists(static::class, 'restored')) {
            static::restored(fn (Model $model) => $model->logActivity('restored'));
        }
    }

    /**
     * Função central que processa e salva o log no banco de dados.
     */
    protected function logActivity(string $event)
    {
        $oldValues = null;
        $newValues = null;

        if ($event === 'created') {
            $newValues = $this->getAttributes();
        } elseif ($event === 'updated') {
            // Ignora se o evento disparou mas nada realmente mudou (ex: clicou em salvar sem alterar nada)
            if (! $this->isDirty()) {
                return;
            }
            
            $newValues = $this->getChanges();
            $oldValues = [];
            
            // Pega os valores antigos apenas das colunas que sofreram alteração
            foreach ($newValues as $key => $value) {
                $oldValues[$key] = $this->getOriginal($key);
            }
        } elseif ($event === 'deleted' || $event === 'restored') {
            $oldValues = $this->getAttributes();
        }

        ActivityLog::create([
            // Tenta pegar o tenant_id do registro. Se não tiver (ex: model Tenant), pega do usuário logado
            'tenant_id' => $this->tenant_id ?? (auth()->user()->tenant_id ?? null),
            'user_id' => auth()->id(),
            'auditable_type' => static::class,
            'auditable_id' => $this->id,
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}