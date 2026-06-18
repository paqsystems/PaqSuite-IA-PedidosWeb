<?php

namespace Tests\Support;

use App\Models\User;
use Tests\TestCase;

abstract class ExcelImportFeatureTestCase extends TestCase
{
    use AuthenticatesPaqTenant;
    use BuildsExcelImportWorkbooks;
    use SeedsExcelImportCatalog;
    use SeedsPedidosWebFeatureData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpPedidosWebFeature();
        config()->set('paqsuite_mvp.excelImportEnabled', true);
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_06_16_100000_create_pq_excel_catalog_tables.php']);
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_06_16_110000_create_pq_excel_import_tables.php']);
        $this->seedExcelImportCatalog();
    }

    protected function supervisorUser(): User
    {
        return User::query()->where('codigo', 'supervisor.mvp')->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    protected function createLotFromFile(
        \Illuminate\Http\UploadedFile $archivo,
        string $codigoProceso = 'ARTICULOS_ALTA',
        string $hoja = 'Hoja1',
    ): array {
        $response = $this->actingAs($this->supervisorUser())
            ->post('/api/v1/excel-import/procesos/'.$codigoProceso.'/lotes', [
                'archivo' => $archivo,
                'hojaSeleccionada' => $hoja,
            ], $this->tenantHeaders());

        $response->assertOk();

        return (array) $response->json('resultado');
    }
}
