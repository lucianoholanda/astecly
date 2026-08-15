<?php

namespace App\Filament\Resources\ServiceOrders\Pages;

use App\Filament\Resources\ServiceOrders\ServiceOrderResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Device;
use App\Models\ServiceModality;
use App\Models\ServiceOrderStatus;

class CreateServiceOrder extends CreateRecord
{
    protected static string $resource = ServiceOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenantId = auth()->user()->tenant_id ?? 1;

        // 1. Lógica do CLIENTE (Cria novo ou atualiza telefone/email do existente)
        if (empty($data['customer_id']) && !empty($data['customer_name'])) {
            $customer = Customer::create([
                'tenant_id' => $tenantId,
                'name' => $data['customer_name'],
                'document' => preg_replace('/[^0-9]/', '', $data['customer_document'] ?? ''),
                'phone' => preg_replace('/[^0-9]/', '', $data['customer_phone'] ?? ''),
                'email' => $data['customer_email'] ?? null,
                'marketing_source_id' => $data['marketing_source_id'] ?? null,
            ]);
            $data['customer_id'] = $customer->id;
        } elseif (!empty($data['customer_id'])) {
            Customer::where('id', $data['customer_id'])->update([
                'phone' => preg_replace('/[^0-9]/', '', $data['customer_phone'] ?? ''),
                'email' => $data['customer_email'] ?? null,
            ]);
        }

        // 2. Lógica do APARELHO (Cria novo aparelho para o cliente)
        if (empty($data['device_id']) && !empty($data['device_model']) && !empty($data['customer_id'])) {
            $device = Device::create([
                'tenant_id' => $tenantId,
                'customer_id' => $data['customer_id'],
                'device_type_id' => $data['device_type_id'] ?? null,
                'device_brand_id' => $data['device_brand_id'] ?? null,
                'model' => $data['device_model'],
                'serial_number' => $data['device_serial_number'] ?? null,
                'color' => $data['color'] ?? null,
            ]);
            $data['device_id'] = $device->id;
        } elseif (!empty($data['device_id'])) {
            Device::where('id', $data['device_id'])->update([
                'model' => $data['device_model'],
                'serial_number' => $data['device_serial_number'] ?? null,
                'color' => $data['color'] ?? null,
            ]);
        }

        // 3. Lógica do ENDEREÇO (Só cria se a tela de endereço estiver preenchida)
        if (empty($data['customer_address_id']) && !empty($data['zip_code']) && !empty($data['customer_id'])) {
            $address = CustomerAddress::create([
                'tenant_id' => $tenantId,
                'customer_id' => $data['customer_id'],
                'zip_code' => preg_replace('/[^0-9]/', '', $data['zip_code']),
                'street' => $data['street'] ?? null,
                'number' => $data['number'] ?? null,
                'complement' => $data['complement'] ?? null,
                'neighborhood' => $data['neighborhood'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
            ]);
            $data['customer_address_id'] = $address->id;
        }

        // 4. AUTOMAÇÃO DO STATUS INICIAL
        if (!empty($data['service_modality_id'])) {
            $modality = ServiceModality::find($data['service_modality_id']);
            $statusName = 'Em Análise'; // Padrão
            
            if ($modality) {
                if (str_contains(strtolower($modality->name), 'coleta')) {
                    $statusName = 'Aguardando Coleta';
                } elseif (str_contains(strtolower($modality->name), 'visita')) {
                    $statusName = 'Aguardando Visita';
                }
            }
            
            // Procura o status pelo nome. Se não existir no banco, ele cria sozinho!
            $status = ServiceOrderStatus::firstOrCreate(
                ['name' => $statusName, 'tenant_id' => $tenantId],
                ['color' => 'warning']
            );
            $data['service_order_status_id'] = $status->id;
        }

        // 5. Limpa as variáveis "fantasmas" para o Laravel não tentar salvar na tabela de OS e dar erro
        unset(
            $data['customer_name'], $data['customer_document'], $data['customer_phone'], $data['customer_email'], $data['marketing_source_id'],
            $data['device_type_id'], $data['device_brand_id'], $data['device_model'], $data['device_serial_number'], $data['color'],
            $data['zip_code'], $data['street'], $data['number'], $data['complement'], $data['neighborhood'], $data['city'], $data['state']
        );

        return $data;
    }
}