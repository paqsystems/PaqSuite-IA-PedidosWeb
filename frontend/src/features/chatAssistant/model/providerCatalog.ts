export type ChatAssistantProviderCatalogItem = {
  providerId: string;
  displayName: string;
  supportsVision: boolean;
  requiresBaseUrl: boolean;
  supportUrl: string;
  suggestedModels: string[];
};

export type ChatAssistantProviderCatalogResult = {
  items: ChatAssistantProviderCatalogItem[];
};

export function findProviderCatalogItem(
  items: ChatAssistantProviderCatalogItem[],
  providerId: string | null,
): ChatAssistantProviderCatalogItem | null {
  if (!providerId) {
    return null;
  }

  return items.find((item) => item.providerId === providerId) ?? null;
}

export function resolveProviderSuggestedModels(
  provider: ChatAssistantProviderCatalogItem | null,
): string[] {
  if (!provider) {
    return [];
  }

  return provider.suggestedModels ?? [];
}
