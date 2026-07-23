<?php

namespace Tests\Unit\Support;

use App\Models\PqPedidoswebAsistenteIaCredencial;
use App\Support\ChatAssistantCredentialSelection;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

final class ChatAssistantCredentialSelectionTest extends TestCase
{
    public function testWithoutVisionKeepsAlphabeticalDisplayName(): void
    {
        $picked = ChatAssistantCredentialSelection::pickDefault(
            new Collection([
                $this->credencial(2, 'openai / gpt-4o', 'openai', true),
                $this->credencial(1, 'Mistral', 'mistral', true),
            ]),
            false,
        );

        $this->assertSame(1, $picked?->id_credencial);
        $this->assertSame('mistral', $picked?->provider_id);
    }

    public function testWithVisionPrefersOpenaiOverMistral(): void
    {
        $picked = ChatAssistantCredentialSelection::pickDefault(
            new Collection([
                $this->credencial(1, 'Mistral', 'mistral', true),
                $this->credencial(2, 'openai / gpt-4o', 'openai', true),
            ]),
            true,
        );

        $this->assertSame(2, $picked?->id_credencial);
        $this->assertSame('openai', $picked?->provider_id);
    }

    public function testWithVisionSkipsCredentialsWithoutVisionWhenPossible(): void
    {
        $picked = ChatAssistantCredentialSelection::pickDefault(
            new Collection([
                $this->credencial(1, 'A text only', 'mistral', false),
                $this->credencial(3, 'openai vision', 'openai', true),
                $this->credencial(2, 'B text only openai', 'openai', false),
            ]),
            true,
        );

        $this->assertSame(3, $picked?->id_credencial);
    }

    private function credencial(
        int $id,
        string $displayName,
        string $providerId,
        bool $supportsVision,
    ): PqPedidoswebAsistenteIaCredencial {
        $model = new PqPedidoswebAsistenteIaCredencial();
        $model->id_credencial = $id;
        $model->display_name = $displayName;
        $model->provider_id = $providerId;
        $model->supports_vision = $supportsVision;

        return $model;
    }
}
