<?php

declare(strict_types=1);

namespace Tests\Unit\Emission;

use PaqSuite\LaravelCore\Emissions\Designer\EmissionDesignerSchemaCanonicalValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class EmissionDesignerSchemaGen37Test extends TestCase
{
    #[DataProvider('emisibleProcessCodesProvider')]
    public function testEveryFieldHasLabelAndWireNamesUnchanged(string $processCode): void
    {
        $schemaPath = base_path('database/emission-designer-schemas/'.$processCode.'.json');
        $portPath = base_path('database/emission-designer-schemas/'.$processCode.'.port-names.json');
        $this->assertFileIsReadable($schemaPath);
        $this->assertFileIsReadable($portPath);

        $raw = (string) file_get_contents($schemaPath);
        $validator = new EmissionDesignerSchemaCanonicalValidator();
        $validator->validateJsonString($raw);

        $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        $fields = $decoded['fields'] ?? [];
        $this->assertNotEmpty($fields);

        foreach ($fields as $field) {
            $this->assertArrayHasKey('label', $field);
            $this->assertNotSame('', trim((string) $field['label']));
        }

        $port = json_decode((string) file_get_contents($portPath), true, 512, JSON_THROW_ON_ERROR);
        $portNames = $port['fieldNames'] ?? [];
        $schemaNames = array_map(static fn (array $f): string => $f['name'], $fields);
        $this->assertSame($portNames, $schemaNames);
    }

    /**
     * @return list<array{0: string}>
     */
    public static function emisibleProcessCodesProvider(): array
    {
        return [
            ['pw_consultacheques'],
            ['pw_consultastock'],
            ['pw_detallepedidos'],
            ['pw_deudaclientes'],
            ['pw_historialventas'],
            ['pw_pedidosingresados'],
            ['pw_pedidospendientes'],
            ['pw_presupuestosingresados'],
        ];
    }
}