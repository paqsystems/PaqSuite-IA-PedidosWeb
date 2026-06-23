import Button from 'devextreme-react/button';
import FileUploader from 'devextreme-react/file-uploader';
import { useCallback, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import {
  chatAssistantMaxImages,
  type ChatAssistantSelectedImage,
} from '../model/chatAssistantImage';
import { resolveChatAssistantImageValidationErrorKey } from '../utils/validateChatAssistantImages';
import { ChatAssistantImageValidationMessage } from './ChatAssistantImageValidationMessage';
import './ChatAssistantImagePicker.css';

type ChatAssistantImagePickerProps = {
  disabled?: boolean;
  supportsVision: boolean;
  selectedImages: ChatAssistantSelectedImage[];
  onSelectedImagesChange: (images: ChatAssistantSelectedImage[]) => void;
};

function createSelectedImage(file: File): ChatAssistantSelectedImage {
  return {
    id: `img-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
    file,
    previewUrl: URL.createObjectURL(file),
  };
}

export function ChatAssistantImagePicker({
  disabled = false,
  supportsVision,
  selectedImages,
  onSelectedImagesChange,
}: ChatAssistantImagePickerProps) {
  const { t } = useTranslation();
  const [validationErrorKey, setValidationErrorKey] = useState<string | null>(null);

  const isDisabled = disabled || !supportsVision;

  const handleFilesAdded = useCallback(
    (files: File[] | null | undefined) => {
      if (!files || files.length === 0) {
        return;
      }

      const nextImages = [...selectedImages];

      const existingKeys = new Set(
        selectedImages.map((image) => `${image.file.name}:${image.file.size}:${image.file.lastModified}`),
      );

      for (const file of files) {
        const fileKey = `${file.name}:${file.size}:${file.lastModified}`;

        if (existingKeys.has(fileKey)) {
          continue;
        }

        const errorKey = resolveChatAssistantImageValidationErrorKey(file, nextImages.length);

        if (errorKey) {
          setValidationErrorKey(errorKey);
          return;
        }

        nextImages.push(createSelectedImage(file));
      }

      setValidationErrorKey(null);
      onSelectedImagesChange(nextImages);
    },
    [onSelectedImagesChange, selectedImages],
  );

  const removeImage = useCallback(
    (imageId: string) => {
      const imageToRemove = selectedImages.find((image) => image.id === imageId);

      if (imageToRemove) {
        URL.revokeObjectURL(imageToRemove.previewUrl);
      }

      setValidationErrorKey(null);
      onSelectedImagesChange(selectedImages.filter((image) => image.id !== imageId));
    },
    [onSelectedImagesChange, selectedImages],
  );

  const remainingSlots = useMemo(
    () => Math.max(0, chatAssistantMaxImages - selectedImages.length),
    [selectedImages.length],
  );

  if (!supportsVision) {
    return (
      <p
        className="chatAssistantImagePicker__unsupported"
        data-testid="chatAssistantImageUnsupportedProviderMessage"
      >
        {t('chatAssistant.images.unsupportedProvider')}
      </p>
    );
  }

  return (
    <section className="chatAssistantImagePicker" data-testid="chatAssistantImagePicker">
      <FileUploader
        accept="image/png,image/jpeg,image/webp,.png,.jpg,.jpeg,.webp"
        uploadMode="useForm"
        multiple={remainingSlots > 1}
        disabled={isDisabled || remainingSlots === 0}
        showFileList={false}
        labelText={t('chatAssistant.images.pickerLabel', {
          remaining: remainingSlots,
          max: chatAssistantMaxImages,
        })}
        selectButtonText={t('chatAssistant.images.pickerButton')}
        onValueChanged={(event) => {
          handleFilesAdded(event.value as File[] | null | undefined);
        }}
      />

      <ChatAssistantImageValidationMessage errorKey={validationErrorKey} />

      {selectedImages.length > 0 ? (
        <ul className="chatAssistantImagePicker__previewList">
          {selectedImages.map((image) => (
            <li key={image.id} className="chatAssistantImagePicker__previewItem">
              <img
                src={image.previewUrl}
                alt={image.file.name}
                className="chatAssistantImagePicker__previewImage"
                data-testid="chatAssistantImagePreview"
              />
              <div className="chatAssistantImagePicker__previewMeta">
                <span>{image.file.name}</span>
                <Button
                  text={t('chatAssistant.images.remove')}
                  stylingMode="text"
                  disabled={isDisabled}
                  onClick={() => {
                    removeImage(image.id);
                  }}
                  elementAttr={{ 'data-testid': 'chatAssistantImageRemoveButton' }}
                />
              </div>
            </li>
          ))}
        </ul>
      ) : null}
    </section>
  );
}
