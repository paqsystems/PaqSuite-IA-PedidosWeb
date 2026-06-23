import { describe, expect, it } from 'vitest';
import { resolveChatAssistantSaveValidationErrorKey } from './validateChatAssistantConfiguration';

describe('validateChatAssistantConfiguration', () => {
  it('exige baseUrl cuando el proveedor la requiere', () => {
        expect(
      resolveChatAssistantSaveValidationErrorKey({
        displayName: 'Mi OpenAI',
        providerId: 'ollama',
        modelId: 'llama3.1',
        baseUrl: '',
        apiKey: 'secret',
        requiresBaseUrl: true,
        hasExistingApiKey: false,
      }),
    ).toBe('chatAssistant.settings.baseUrlRequired');
  });

  it('permite guardar sin baseUrl cuando no es requerida', () => {
    expect(
      resolveChatAssistantSaveValidationErrorKey({
        displayName: 'Mi OpenAI',
        providerId: 'openai',
        modelId: 'gpt-4o-mini',
        baseUrl: '',
        apiKey: 'secret',
        requiresBaseUrl: false,
        hasExistingApiKey: false,
      }),
    ).toBeNull();
  });

  it('permite editar sin reingresar apiKey si ya existe', () => {
    expect(
      resolveChatAssistantSaveValidationErrorKey({
        displayName: 'Mi OpenAI',
        providerId: 'openai',
        modelId: 'gpt-4o-mini',
        baseUrl: '',
        apiKey: '',
        requiresBaseUrl: false,
        hasExistingApiKey: true,
      }),
    ).toBeNull();
  });
});
