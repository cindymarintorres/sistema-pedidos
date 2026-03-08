<?php

namespace Tests\Unit;

use App\Http\Requests\StoreProductoRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ProductoValidationTest extends TestCase
{
    public function test_datos_validos_pasan_la_validacion(): void
    {
        $data = [
            'sku'    => 'TEST-123',
            'nombre' => 'Producto de Prueba',
            'precio' => 15.50,
            'stock'  => 10,
        ];

        $request = new StoreProductoRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_precio_negativo_falla(): void
    {
        $data = [
            'sku'    => 'TEST-123',
            'nombre' => 'Producto de Prueba',
            'precio' => -5.00,
            'stock'  => 10,
        ];

        $request = new StoreProductoRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('precio', $validator->errors()->toArray());
    }

    public function test_sku_con_caracteres_invalidos_falla(): void
    {
        $data = [
            'sku'    => 'TEST_123@', // El regex del request solo permite a-zA-Z0-9\-
            'nombre' => 'Producto de Prueba',
            'precio' => 10.00,
            'stock'  => 10,
        ];

        $request = new StoreProductoRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('sku', $validator->errors()->toArray());
    }
}
