<?php

namespace Database\Seeders\ChatAssistant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class ChatAssistantProviderCatalogSeeder extends Seeder
{
    /**
     * @return list<array{
     *     provider_id: string,
     *     nombre_visible: string,
     *     tipo_integracion: string,
     *     soporta_byok: bool,
     *     soporta_imagenes: bool,
     *     requiere_base_url_editable: bool,
     *     url_documentacion: string,
     *     url_onboarding: string,
     *     activo: bool,
     *     observacion: ?string
     * }>
     */
    public static function catalogRows(): array
    {
        return [
            [
                'provider_id' => 'ollama',
                'nombre_visible' => 'Ollama',
                'tipo_integracion' => 'runtime_local',
                'soporta_byok' => true,
                'soporta_imagenes' => true,
                'requiere_base_url_editable' => true,
                'url_documentacion' => 'https://docs.ollama.com/',
                'url_onboarding' => 'https://ollama.com/download',
                'activo' => true,
                'observacion' => null,
            ],
            [
                'provider_id' => 'openai',
                'nombre_visible' => 'OpenAI',
                'tipo_integracion' => 'api_publica',
                'soporta_byok' => true,
                'soporta_imagenes' => true,
                'requiere_base_url_editable' => false,
                'url_documentacion' => 'https://platform.openai.com/docs/overview',
                'url_onboarding' => 'https://platform.openai.com/api-keys',
                'activo' => true,
                'observacion' => null,
            ],
            [
                'provider_id' => 'anthropic',
                'nombre_visible' => 'Anthropic',
                'tipo_integracion' => 'api_publica',
                'soporta_byok' => true,
                'soporta_imagenes' => true,
                'requiere_base_url_editable' => false,
                'url_documentacion' => 'https://docs.anthropic.com/',
                'url_onboarding' => 'https://console.anthropic.com/',
                'activo' => true,
                'observacion' => null,
            ],
            [
                'provider_id' => 'googleGemini',
                'nombre_visible' => 'Google Gemini',
                'tipo_integracion' => 'api_publica',
                'soporta_byok' => true,
                'soporta_imagenes' => true,
                'requiere_base_url_editable' => false,
                'url_documentacion' => 'https://ai.google.dev/gemini-api/docs',
                'url_onboarding' => 'https://aistudio.google.com/',
                'activo' => true,
                'observacion' => null,
            ],
            [
                'provider_id' => 'azureOpenAi',
                'nombre_visible' => 'Azure OpenAI',
                'tipo_integracion' => 'api_administrada',
                'soporta_byok' => true,
                'soporta_imagenes' => true,
                'requiere_base_url_editable' => true,
                'url_documentacion' => 'https://learn.microsoft.com/azure/ai-services/openai/',
                'url_onboarding' => 'https://portal.azure.com/',
                'activo' => true,
                'observacion' => null,
            ],
            [
                'provider_id' => 'openRouter',
                'nombre_visible' => 'OpenRouter',
                'tipo_integracion' => 'agregador',
                'soporta_byok' => true,
                'soporta_imagenes' => true,
                'requiere_base_url_editable' => false,
                'url_documentacion' => 'https://openrouter.ai/docs',
                'url_onboarding' => 'https://openrouter.ai/settings/keys',
                'activo' => true,
                'observacion' => null,
            ],
            [
                'provider_id' => 'groq',
                'nombre_visible' => 'Groq',
                'tipo_integracion' => 'api_publica',
                'soporta_byok' => true,
                'soporta_imagenes' => true,
                'requiere_base_url_editable' => false,
                'url_documentacion' => 'https://console.groq.com/docs/overview',
                'url_onboarding' => 'https://console.groq.com/keys',
                'activo' => true,
                'observacion' => null,
            ],
            [
                'provider_id' => 'mistral',
                'nombre_visible' => 'Mistral',
                'tipo_integracion' => 'api_publica',
                'soporta_byok' => true,
                'soporta_imagenes' => true,
                'requiere_base_url_editable' => false,
                'url_documentacion' => 'https://docs.mistral.ai/',
                'url_onboarding' => 'https://console.mistral.ai/',
                'activo' => true,
                'observacion' => null,
            ],
            [
                'provider_id' => 'legacyInactive',
                'nombre_visible' => 'Legacy Inactive',
                'tipo_integracion' => 'api_publica',
                'soporta_byok' => true,
                'soporta_imagenes' => false,
                'requiere_base_url_editable' => false,
                'url_documentacion' => 'https://example.com/docs',
                'url_onboarding' => 'https://example.com/onboarding',
                'activo' => false,
                'observacion' => 'Fixture inactivo para tests de filtro',
            ],
        ];
    }

    public function run(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_asistente_ia_proveedores')) {
            return;
        }

        foreach (self::catalogRows() as $row) {
            DB::table('pq_pedidosweb_asistente_ia_proveedores')->updateOrInsert(
                ['provider_id' => $row['provider_id']],
                [
                    'nombre_visible' => $row['nombre_visible'],
                    'tipo_integracion' => $row['tipo_integracion'],
                    'soporta_byok' => $row['soporta_byok'],
                    'soporta_imagenes' => $row['soporta_imagenes'],
                    'requiere_base_url_editable' => $row['requiere_base_url_editable'],
                    'url_documentacion' => $row['url_documentacion'],
                    'url_onboarding' => $row['url_onboarding'],
                    'activo' => $row['activo'],
                    'observacion' => $row['observacion'],
                ],
            );
        }
    }
}
