import Button from 'devextreme-react/button';
import TextArea from 'devextreme-react/text-area';
import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import type { ChatAssistantImagePayload, ChatAssistantSelectedImage } from '../model/chatAssistantImage';
import { encodeChatAssistantImageFile } from '../utils/validateChatAssistantImages';
import {
  isChatAssistantMessageLengthExceeded,
  resolveChatAssistantMessageMaxLength,
} from '../utils/validateChatAssistantMessageLength';
import { ChatAssistantImagePicker } from './ChatAssistantImagePicker';
import './ChatAssistantComposer.css';

export type ChatAssistantComposerSubmitPayload = {
  message: string;
  images: ChatAssistantImagePayload[];
};

type ChatAssistantComposerProps = {
  disabled?: boolean;
  isSubmitting?: boolean;
  supportsVision: boolean;
  onSubmit: (payload: ChatAssistantComposerSubmitPayload) => Promise<boolean>;
};

function revokeSelectedImagePreviews(images: ChatAssistantSelectedImage[]) {
  images.forEach((image) => {
    URL.revokeObjectURL(image.previewUrl);
  });
}

export function ChatAssistantComposer({
  disabled = false,
  isSubmitting = false,
  supportsVision,
  onSubmit,
}: ChatAssistantComposerProps) {
  const { t } = useTranslation();
  const [message, setMessage] = useState('');
  const [selectedImages, setSelectedImages] = useState<ChatAssistantSelectedImage[]>([]);
  const hasImages = selectedImages.length > 0;
  const maxLength = resolveChatAssistantMessageMaxLength(hasImages);
  const messageLength = message.length;
  const isLengthExceeded = isChatAssistantMessageLengthExceeded(messageLength, hasImages);
  const hasContent = message.trim() !== '' || hasImages;
  const canSubmit = !disabled && !isSubmitting && hasContent && !isLengthExceeded;

  const counterLabel = useMemo(
    () => t('chatAssistant.composer.counter', { current: messageLength, max: maxLength }),
    [messageLength, maxLength, t],
  );

  return (
    <section className="chatAssistantComposer" data-testid="chatAssistantComposer">
      <TextArea
        value={message}
        valueChangeEvent="input"
        height={120}
        disabled={disabled || isSubmitting}
        label={t('chatAssistant.composer.label')}
        onValueChanged={(event) => {
          setMessage(String(event.value ?? ''));
        }}
        inputAttr={{ 'data-testid': 'chatAssistantComposerInput' }}
      />

      <ChatAssistantImagePicker
        disabled={disabled || isSubmitting}
        supportsVision={supportsVision}
        selectedImages={selectedImages}
        onSelectedImagesChange={setSelectedImages}
      />

      <div className="chatAssistantComposer__footer">
        <p
          className={[
            'chatAssistantComposer__counter',
            isLengthExceeded ? 'chatAssistantComposer__counter--error' : '',
          ]
            .filter(Boolean)
            .join(' ')}
          data-testid="chatAssistantCharacterCounter"
        >
          {counterLabel}
        </p>

        {isLengthExceeded ? (
          <p className="chatAssistantComposer__limitError" role="alert">
            {t('chatAssistant.composer.limitExceeded', { max: maxLength })}
          </p>
        ) : null}

        <Button
          text={t('chatAssistant.composer.submit')}
          type="default"
          disabled={!canSubmit}
          onClick={() => {
            void (async () => {
              const encodedImages = await Promise.all(
                selectedImages.map((image) => encodeChatAssistantImageFile(image.file)),
              );

              const accepted = await onSubmit({
                message: message.trim(),
                images: encodedImages,
              });

              if (accepted) {
                revokeSelectedImagePreviews(selectedImages);
                setMessage('');
                setSelectedImages([]);
              }
            })();
          }}
          elementAttr={{ 'data-testid': 'chatAssistantSubmitButton' }}
        />
      </div>
    </section>
  );
}
