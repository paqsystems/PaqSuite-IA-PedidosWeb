<?php

namespace App\Support\PedidosWeb;

use Illuminate\Support\Facades\DB;
use PDO;

final class SqlServerStoredProcedureMultiResultReader
{
    /**
     * @param  list<mixed>  $inputParams  Parámetros de entrada en orden
     * @param  list<int>  $outputParamIndexes  Índices 1-based que son OUTPUT (p. ej. [8] para @total)
     * @return array{resultSets: list<list<object>>, outputs: array<int, mixed>}
     */
    public function execute(string $procedure, array $inputParams, array $outputParamIndexes = []): array
    {
        if (! preg_match('/^[A-Za-z0-9_]+$/', $procedure)) {
            throw new \InvalidArgumentException('Nombre de procedimiento inválido.');
        }

        $pdo = DB::connection()->getPdo();
        $placeholders = implode(', ', array_fill(0, count($inputParams), '?'));
        $statement = $pdo->prepare("EXEC [{$procedure}] {$placeholders}");

        $outputs = [];
        foreach ($inputParams as $index => $value) {
            $position = $index + 1;
            if (in_array($position, $outputParamIndexes, true)) {
                $outputs[$position] = 0;
                $statement->bindParam(
                    $position,
                    $outputs[$position],
                    PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT,
                    4,
                );
            } else {
                $statement->bindValue($position, $value);
            }
        }

        $statement->execute();

        $resultSets = [];
        do {
            $rows = [];
            while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
                $rows[] = $row;
            }
            if ($rows !== []) {
                $resultSets[] = $rows;
            }
        } while ($statement->nextRowset());

        return [
            'resultSets' => $resultSets,
            'outputs' => $outputs,
        ];
    }
}