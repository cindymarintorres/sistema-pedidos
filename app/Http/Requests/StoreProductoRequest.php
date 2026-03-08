<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sku'    => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9\-]+$/'],
            'nombre' => ['required', 'string', 'min:2', 'max:150'],
            'precio' => ['required', 'numeric', 'min:0'],
            'stock'  => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'sku.required'   => 'El SKU es requerido.',
            'sku.string'     => 'El SKU debe ser una cadena de texto.',
            'sku.max'        => 'El SKU no puede exceder 50 caracteres.',
            'sku.regex'      => 'El SKU solo puede contener letras, números y guiones.',
            'nombre.required'=> 'El nombre es requerido.',
            'nombre.string'  => 'El nombre debe ser texto.',
            'nombre.min'     => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max'     => 'El nombre no puede exceder 150 caracteres.',
            'precio.required'=> 'El precio es requerido.',
            'precio.numeric' => 'El precio debe ser un número.',
            'precio.min'     => 'El precio no puede ser negativo.',
            'stock.required' => 'El stock es requerido.',
            'stock.integer'  => 'El stock debe ser un número entero.',
            'stock.min'      => 'El stock no puede ser negativo.',
        ];
    }
}
