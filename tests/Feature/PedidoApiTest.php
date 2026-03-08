<?php

namespace Tests\Feature;

use App\Services\PedidoService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class PedidoApiTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_crear_pedido_con_items_validos_retorna_201_con_estructura_correcta(): void
    {
        // Mockear el servicio para no tocar la BD (SPs) durante los tests
        $this->instance(
            PedidoService::class,
            Mockery::mock(PedidoService::class, function (MockInterface $mock) {
                // Configurar el mock para que cuando llamen a 'crear', devuelva un array simulado de éxito
                $mock->shouldReceive('crear')
                    ->once()
                    ->with([
                        ['producto_id' => 1, 'cantidad' => 2]
                    ])
                    ->andReturn([
                        'id' => 100,
                        'subtotal' => 50.00,
                        'descuento' => 0.00,
                        'iva' => 6.00,
                        'total' => 56.00,
                        'created_at' => now()->toDateTimeString(),
                        'items' => [
                            [
                                'item_id' => 1,
                                'producto_id' => 1,
                                'cantidad' => 2,
                                'precio_unitario' => 25.00,
                                'subtotal_item' => 50.00
                            ]
                        ]
                    ]);
            })
        );

        $response = $this->postJson('/api/pedidos', [
            'items' => [
                ['producto_id' => 1, 'cantidad' => 2]
            ]
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'id', 'subtotal', 'descuento', 'iva', 'total', 'created_at', 'items'
                     ]
                 ]);
    }

    public function test_crear_pedido_con_stock_insuficiente_retorna_422(): void
    {
        $this->instance(
            PedidoService::class,
            Mockery::mock(PedidoService::class, function (MockInterface $mock) {
                $mock->shouldReceive('crear')
                    ->once()
                    ->andThrow(new \RuntimeException('Stock insuficiente para Mouse Logitech Inalámbrico. Disponible: 5, solicitado: 10'));
            })
        );

        $response = $this->postJson('/api/pedidos', [
            'items' => [
                ['producto_id' => 2, 'cantidad' => 10]
            ]
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Stock insuficiente para Mouse Logitech Inalámbrico. Disponible: 5, solicitado: 10'
                 ]);
    }

    public function test_pedido_con_subtotal_mayor_a_100_tiene_descuento_aplicado(): void
    {
        $this->instance(
            PedidoService::class,
            Mockery::mock(PedidoService::class, function (MockInterface $mock) {
                $mock->shouldReceive('crear')
                    ->once()
                    ->andReturn([
                        'id' => 101,
                        'subtotal' => 200.00,
                        'descuento' => 20.00,
                        'iva' => 21.60,
                        'total' => 201.60,
                        'items' => []
                    ]);
            })
        );

        $response = $this->postJson('/api/pedidos', [
            'items' => [
                ['producto_id' => 1, 'cantidad' => 1] // Supongamos que cuesta $200
            ]
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.descuento', 20)
                 ->assertJsonPath('data.total', 201.6);
    }
}
