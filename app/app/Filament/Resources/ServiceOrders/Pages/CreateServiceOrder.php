<?php

namespace App\Filament\Resources\ServiceOrders\Pages;

use App\Filament\Resources\ServiceOrders\ServiceOrderResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;
use App\Models\Customer;
use App\Models\Device;
use Filament\Facades\Filament;

class CreateServiceOrder extends CreateRecord
{
    protected static string $resource = ServiceOrderResource::class;

    public function getTitle(): string
    {
        return 'Abrir Ordem de Serviço';
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenantId = Filament::getTenant()->id;

        // 1. Lógica do Cliente (Cria ou Atualiza)
        if (!empty($data['customer_id'])) {
            $customer = Customer::find($data['customer_id']);
            $customer->update([
                'name' => $data['customer_name'],
                'document' => $data['customer_document'] ?? null, // Salva o CPF/CNPJ
                'phone' => $data['customer_phone'] ?? null,
                'email' => $data['customer_email'] ?? null,
            ]);
        } else {
            $customer = Customer::create([
                'tenant_id' => $tenantId,
                'name' => $data['customer_name'],
                'document' => $data['customer_document'] ?? null, // Salva o CPF/CNPJ
                'phone' => $data['customer_phone'] ?? null,
                'email' => $data['customer_email'] ?? null,
            ]);
            $data['customer_id'] = $customer->id;
        }

        // 2. Lógica do Aparelho (Cria ou Atualiza)
        if (!empty($data['device_id'])) {
            $device = Device::find($data['device_id']);
            $device->update([
                'device_type_id' => $data['device_type_id'],
                'device_brand_id' => $data['device_brand_id'],
                'model' => $data['device_model'],
            ]);
        } else {
            $device = Device::create([
                'tenant_id' => $tenantId,
                'customer_id' => $data['customer_id'],
                'device_type_id' => $data['device_type_id'],
                'device_brand_id' => $data['device_brand_id'],
                'model' => $data['device_model'],
            ]);
            $data['device_id'] = $device->id;
        }

        $data['entry_date'] = now();

        // 3. Limpeza de variáveis virtuais para não dar erro na tabela de OS
        unset(
            $data['customer_name'], $data['customer_document'], $data['customer_phone'], $data['customer_email'],
            $data['device_type_id'], $data['device_brand_id'], $data['device_model']
        );

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}