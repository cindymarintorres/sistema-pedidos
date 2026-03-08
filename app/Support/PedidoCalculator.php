<?php

namespace App\Support;

final class PedidoCalculator
{
    private const UMBRAL_DESCUENTO = 100.00;
    private const TASA_DESCUENTO   = 0.10;
    private const TASA_IVA         = 0.12;

    public static function calcular(float $subtotal): array
    {
        $descuento = $subtotal > self::UMBRAL_DESCUENTO
            ? round($subtotal * self::TASA_DESCUENTO, 2)
            : 0.00;

        $base  = $subtotal - $descuento;
        $iva   = round($base * self::TASA_IVA, 2);
        $total = round($base + $iva, 2);

        return compact('subtotal', 'descuento', 'iva', 'total');
    }
}
