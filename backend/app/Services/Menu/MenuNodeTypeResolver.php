<?php

namespace App\Services\Menu;

final class MenuNodeTypeResolver
{
    public function resolve(?string $routeName, ?string $tipoProceso): string
    {
        if (trim((string) $routeName) !== '') {
            return 'process';
        }

        if (strtoupper(trim((string) $tipoProceso)) === 'P') {
            return 'process';
        }

        return 'group';
    }
}
