import { describe, expect, it } from 'vitest';
import { emptyMyChatAssistantConfiguration } from '../model/myChatAssistantConfiguration';
import { isChatAssistantConfigurationOperational } from './resolveChatAssistantOperationalConfiguration';
import {
  isChatAssistantMessageLengthExceeded,
  isChatAssistantMessageLengthValid,
  resolveChatAssistantMessageMaxLength,
} from './validateChatAssistantMessageLength';

describe('validateChatAssistantMessageLength', () => {
  it('uses 2000 characters for text-only messages', () => {
    expect(resolveChatAssistantMessageMaxLength(false)).toBe(2000);
    expect(isChatAssistantMessageLengthValid(2000, false)).toBe(true);
    expect(isChatAssistantMessageLengthExceeded(2001, false)).toBe(true);
  });

  it('uses 1000 characters when images are included', () => {
    expect(resolveChatAssistantMessageMaxLength(true)).toBe(1000);
    expect(isChatAssistantMessageLengthValid(1000, true)).toBe(true);
    expect(isChatAssistantMessageLengthExceeded(1001, true)).toBe(true);
  });
});

describe('isChatAssistantConfigurationOperational', () => {
  it('requires configuration, api key, enabled state and active provider', () => {
    expect(
      isChatAssistantConfigurationOperational(
        {
          ...emptyMyChatAssistantConfiguration,
          credentialId: 1,
          hasConfiguration: true,
          hasApiKey: true,
          isEnabled: true,
          providerId: 'ollama',
        },
        ['ollama'],
      ),
    ).toBe(true);

    expect(
      isChatAssistantConfigurationOperational(
        {
          ...emptyMyChatAssistantConfiguration,
          hasConfiguration: true,
          hasApiKey: true,
          isEnabled: false,
          providerId: 'ollama',
        },
        ['ollama'],
      ),
    ).toBe(false);
  });
});
