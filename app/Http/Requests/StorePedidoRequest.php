<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items'               => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'integer', 'min:1'],
            'items.*.cantidad'    => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'               => 'Se requiere al menos un item para crear el pedido.',
            'items.array'                  => 'Los items deben enviarse como arreglo.',
            'items.min'                    => 'El pedido debe contener al menos 1 producto.',
            'items.*.producto_id.required' => 'El ID del producto es requerido.',
            'items.*.producto_id.integer'  => 'El ID del producto debe ser un número entero.',
            'items.*.producto_id.min'      => 'El ID del producto debe ser mayor a 0.',
            'items.*.cantidad.required'    => 'La cantidad es requerida.',
            'items.*.cantidad.integer'     => 'La cantidad debe ser un número entero.',
            'items.*.cantidad.min'         => 'La cantidad debe ser al menos 1.',
        ];
    }
}
