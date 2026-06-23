import { describe, expect, it } from 'vitest';
import { findProviderCatalogItem } from './providerCatalog';
import type { ChatAssistantProviderCatalogItem } from './providerCatalog';

const sampleItems: ChatAssistantProviderCatalogItem[] = [
  {
    providerId: 'ollama',
    displayName: 'Ollama',
    supportsVision: true,
    requiresBaseUrl: true,
    supportUrl: 'https://ollama.com/download',
    suggestedModels: ['llama3.1'],
  },
  {
    providerId: 'openai',
    displayName: 'OpenAI',
    supportsVision: true,
    requiresBaseUrl: false,
    supportUrl: 'https://platform.openai.com/api-keys',
    suggestedModels: ['gpt-4o-mini'],
  },
];

describe('providerCatalog', () => {
  it('resuelve un proveedor por providerId', () => {
    expect(findProviderCatalogItem(sampleItems, 'openai')).toEqual(sampleItems[1]);
  });

  it('devuelve null cuando no hay seleccion', () => {
    expect(findProviderCatalogItem(sampleItems, null)).toBeNull();
  });
});
