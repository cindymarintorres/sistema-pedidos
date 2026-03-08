<?php

namespace Tests\Unit;

use App\Support\PedidoCalculator;
use PHPUnit\Framework\TestCase;

class PedidoCalculatorTest extends TestCase
{
    public function test_sin_descuento_cuando_subtotal_es_menor_a_100(): void
    {
        $resultado = PedidoCalculator::calcular(50.00);

        $this->assertEquals(50.00, $resultado['subtotal']);
        $this->assertEquals(0.00, $resultado['descuento']);
        $this->assertEquals(6.00, $resultado['iva']); // 50 * 0.12 = 6
        $this->assertEquals(56.00, $resultado['total']);
    }

    public function test_sin_descuento_cuando_subtotal_es_exactamente_100(): void
    {
        $resultado = PedidoCalculator::calcular(100.00);

        $this->assertEquals(100.00, $resultado['subtotal']);
        $this->assertEquals(0.00, $resultado['descuento']);
        $this->assertEquals(12.00, $resultado['iva']); // 100 * 0.12 = 12
        $this->assertEquals(112.00, $resultado['total']);
    }

    public function test_con_descuento_cuando_subtotal_supera_100(): void
    {
        $resultado = PedidoCalculator::calcular(150.00);

        $this->assertEquals(150.00, $resultado['subtotal']);
        $this->assertEquals(15.00, $resultado['descuento']); // 150 * 0.10 = 15
        $this->assertEquals(16.20, $resultado['iva']); // (150 - 15) * 0.12 = 16.2
        $this->assertEquals(151.20, $resultado['total']); // 135 + 16.2 = 151.2
    }

    public function test_iva_se_calcula_sobre_base_descontada(): void
    {
        $resultado = PedidoCalculator::calcular(200.00);
        
        $base = $resultado['subtotal'] - $resultado['descuento']; // 200 - 20 = 180
        $ivaEsperado = round($base * 0.12, 2); // 21.60
        
        $this->assertEquals(21.60, $resultado['iva']);
        $this->assertEquals($ivaEsperado, $resultado['iva']);
    }

    public function test_total_es_suma_correcta_de_base_mas_iva(): void
    {
        $resultado = PedidoCalculator::calcular(125.50);
        
        // Descuento: 12.55
        // Base: 112.95
        // Iva: 13.55
        // Total: 126.50
        
        $baseCalculada = $resultado['subtotal'] - $resultado['descuento'];
        $totalCalculado = round($baseCalculada + $resultado['iva'], 2);
        
        $this->assertEquals($totalCalculado, $resultado['total']);
    }
}
