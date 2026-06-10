<?php

namespace Tests\Unit;

use App\Http\Responses\ApiResponse;
use Tests\TestCase;

final class ApiResponseTest extends TestCase
{
    public function testErrorEnvelopeUsesEmptyObjectAsResultado(): void
    {
        $response = ApiResponse::error(1001, 'tenant.invalid', 400);

        $this->assertStringContainsString('"resultado":{}', $response->getContent());
    }

    public function testSuccessEnvelopeUsesZeroErrorCode(): void
    {
        $json = ApiResponse::success(['status' => 'up'])->getData(true);

        $this->assertSame(0, $json['error']);
        $this->assertSame('ok', $json['respuesta']);
        $this->assertSame('up', $json['resultado']['status']);
    }
}
