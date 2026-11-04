<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DxReportingProxyTest extends TestCase
{
    public function test_forwards_get_designer_model_to_sidecar(): void
    {
        config(['services.dx_reporting_sidecar_url' => 'http://127.0.0.1:5055']);

        Http::fake([
            'http://127.0.0.1:5055/DXXRD/GetDesignerModel' => Http::response(
                '{"reportModel":"{}"}',
                200,
                ['Content-Type' => 'application/json'],
            ),
        ]);

        $response = $this->post(
            '/DXXRD/GetDesignerModel',
            ['reportUrl' => 'pw_consultastock'],
        );

        $response->assertOk();
        $response->assertSee('reportModel', false);

        Http::assertSent(function ($request) {
            return $request->url() === 'http://127.0.0.1:5055/DXXRD/GetDesignerModel'
                && $request->method() === 'POST';
        });
    }

    public function test_returns_503_when_sidecar_url_missing(): void
    {
        config(['services.dx_reporting_sidecar_url' => '']);

        $response = $this->post('/DXXRD/GetDesignerModel', ['reportUrl' => 'x']);

        $response->assertStatus(503);
    }
}
