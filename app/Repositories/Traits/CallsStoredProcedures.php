<?php

namespace App\Repositories\Traits;

use Illuminate\Support\Facades\DB;
use PDO;

trait CallsStoredProcedures
{
    /**
     * Llama a un Stored Procedure que tiene parámetros OUT y retorna sus valores.
     *
     * @param string $procedure Nombre del SP
     * @param array $inParams Parámetros IN (en el orden que el SP espera)
     * @param array $outVars Nombres de variables de sesión para OUT (ej: ['@nuevo_id', '@error'])
     * @return array Array asociativo con los valores retornados
     */
    protected function callWithOut(string $procedure, array $inParams, array $outVars): array
    {
        $pdo = DB::connection()->getPdo();

        $placeholders = implode(', ', array_fill(0, count($inParams), '?'));
        $outPlaceholders = implode(', ', $outVars);
        $sql = "CALL {$procedure}({$placeholders}, {$outPlaceholders})";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($inParams);
        $stmt->closeCursor();

        $selectSql = 'SELECT ' . implode(', ', $outVars);
        $result = $pdo->query($selectSql)->fetch(PDO::FETCH_ASSOC);

        // Limpiar las arrobas para tener keys limpios ('@error' -> 'error')
        $cleanResult = [];
        if ($result) {
            foreach ($result as $key => $value) {
                $cleanResult[ltrim($key, '@')] = $value;
            }
        }

        return $cleanResult;
    }
}
