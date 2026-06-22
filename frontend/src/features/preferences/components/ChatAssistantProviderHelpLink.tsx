import { useTranslation } from 'react-i18next';
import type { ChatAssistantProviderCatalogItem } from '../../chatAssistant/model/providerCatalog';

type ChatAssistantProviderHelpLinkProps = {
  provider: ChatAssistantProviderCatalogItem | null;
};

export function ChatAssistantProviderHelpLink({ provider }: ChatAssistantProviderHelpLinkProps) {
  const { t } = useTranslation();

  if (!provider?.supportUrl) {
    return null;
  }

  return (
    <a
      className="chatAssistantProviderHelpLink"
      data-testid="chatAssistantProviderSupportLink"
      href={provider.supportUrl}
      rel="noreferrer noopener"
      target="_blank"
    >
      {t('chatAssistant.settings.providerSupportLink')}
    </a>
  );
}
