<?php

namespace App\Filament\Resources\ServiceOrders\Pages;

use App\Filament\Resources\ServiceOrders\ServiceOrderResource;
use Filament\Resources\Pages\EditRecord;

class EditServiceOrder extends EditRecord
{
    protected static string $resource = ServiceOrderResource::class;

    public function getTitle(): string
    {
        return 'Editar Ordem de Serviço #' . $this->record->id;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Atualiza os dados do Aparelho (Modelo e Série) caso o técnico tenha alterado na edição
        if (isset($data['device_model']) || isset($data['device_serial_number'])) {
            $this->record->device->update([
                'model' => $data['device_model'] ?? $this->record->device->model,
                'serial_number' => $data['device_serial_number'] ?? $this->record->device->serial_number,
            ]);
        }

        // Limpa as variáveis virtuais para o Laravel não tentar salvá-las na tabela de OS
        unset($data['device_model'], $data['device_serial_number']);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        // Ao clicar em Salvar, volta para a lista
        return $this->getResource()::getUrl('index');
    }
}