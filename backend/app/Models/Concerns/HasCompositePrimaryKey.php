<?php

namespace App\Models\Concerns;

/**
 * Soporte mínimo para PK compuestas en Eloquent.
 * Los repositories deben preferir queries explícitas para update/delete masivos.
 */
trait HasCompositePrimaryKey
{
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * @return list<string>
     */
    abstract protected function getCompositeKeyNames(): array;

    protected function setKeysForSaveQuery($query)
    {
        foreach ($this->getCompositeKeyNames() as $keyName) {
            $query->where($keyName, '=', $this->getAttribute($keyName));
        }

        return $query;
    }
}
