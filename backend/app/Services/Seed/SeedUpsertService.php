<?php

namespace App\Services\Seed;

use App\Exceptions\SeedConflictException;
use Illuminate\Database\Eloquent\Model;

final class SeedUpsertService
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, string>  $conflictKeys
     */
    public function upsertByNaturalKey(
        Model $model,
        array $naturalKey,
        array $attributes,
        array $conflictKeys = [],
    ): Model {
        $query = $model->newQuery();

        foreach ($naturalKey as $column => $value) {
            $query->where($column, $value);
        }

        $existing = $query->first();

        if ($existing === null) {
            return $model->newQuery()->create(array_merge($naturalKey, $attributes));
        }

        $keysToCheck = $conflictKeys !== [] ? $conflictKeys : array_keys($attributes);

        foreach ($keysToCheck as $key) {
            if (! array_key_exists($key, $attributes)) {
                continue;
            }

            $expected = $this->normalizeValue($attributes[$key]);
            $actual = $this->normalizeValue($existing->getAttribute($key));

            if ($expected !== $actual) {
                $naturalKeyLabel = json_encode($naturalKey, JSON_THROW_ON_ERROR);

                throw new SeedConflictException(
                    "Conflicto de seed en {$model->getTable()}: clave {$naturalKeyLabel}, campo {$key} esperado [{$expected}] actual [{$actual}]."
                );
            }
        }

        $existing->fill($attributes);
        $existing->save();

        return $existing->fresh();
    }

    private function normalizeValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if ($value === null) {
            return '';
        }

        return (string) $value;
    }
}
