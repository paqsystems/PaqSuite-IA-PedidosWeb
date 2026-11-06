<?php

namespace App\Repositories\Gen\Emissions;

use Illuminate\Support\Facades\File;
use PaqSuite\LaravelCore\Emissions\Contracts\EmissionArtifactStorage;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionArtifact;

final class FileEmissionArtifactStorage implements EmissionArtifactStorage
{
    public function put(string $jobId, int|string|null $companyId, EmissionArtifact $artifact): string
    {
        unset($companyId);
        $directory = storage_path('app/emissions');
        File::ensureDirectoryExists($directory);
        $path = $directory.DIRECTORY_SEPARATOR.$jobId;
        File::put($path, $artifact->content);

        return $path;
    }

    public function get(string $path): ?EmissionArtifact
    {
        if (! is_file($path)) {
            return null;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return null;
        }

        $fileName = basename($path).'.pdf';

        return new EmissionArtifact($content, $fileName, 'application/pdf');
    }

    public function delete(string $path): void
    {
        if (is_file($path)) {
            unlink($path);
        }
    }
}
