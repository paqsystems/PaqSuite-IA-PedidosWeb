import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import Popup from 'devextreme-react/popup';
import TextBox from 'devextreme-react/text-box';
import { ApiClientError } from '../../shared/http/client';
import {
  getApiBaseUrlOverride,
  setActiveTenant,
  setApiBaseUrlOverride,
} from '../../shared/mobile/mobileRuntime';
import { isValidTenantSlug, normalizeTenant } from '../../shared/mobile/normalizeTenant';
import { healthCheckRequest } from './mobileHealthApi';
import './mobileConfigPopup.css';

type MobileConfigPopupProps = {
  isOpen: boolean;
  onClose: () => void;
  tenantForHealthCheck?: string;
};

export function MobileConfigPopup({ isOpen, onClose, tenantForHealthCheck }: MobileConfigPopupProps) {
  const { t } = useTranslation();
  const [apiBaseUrl, setApiBaseUrl] = useState('');
  const [testMessageKey, setTestMessageKey] = useState<string | null>(null);
  const [isTesting, setIsTesting] = useState(false);
  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    if (!isOpen) {
      return;
    }

    let isCancelled = false;

    async function loadOverride() {
      const override = await getApiBaseUrlOverride();
      if (!isCancelled) {
        setApiBaseUrl(override);
        setTestMessageKey(null);
      }
    }

    void loadOverride();

    return () => {
      isCancelled = true;
    };
  }, [isOpen]);

  async function handleSave() {
    setIsSaving(true);
    setTestMessageKey(null);

    try {
      await setApiBaseUrlOverride(apiBaseUrl);
      setTestMessageKey('mobile.config.saved');
      onClose();
    } finally {
      setIsSaving(false);
    }
  }

  async function handleTestConnection() {
    setIsTesting(true);
    setTestMessageKey(null);

    try {
      if (tenantForHealthCheck) {
        const normalizedTenant = normalizeTenant(tenantForHealthCheck);

        if (!isValidTenantSlug(normalizedTenant)) {
          setTestMessageKey('tenant.invalid');
          return;
        }

        await setActiveTenant(normalizedTenant);
      }

      if (apiBaseUrl.trim().length > 0) {
        await setApiBaseUrlOverride(apiBaseUrl);
      }

      await healthCheckRequest();
      setTestMessageKey('mobile.config.testOk');
    } catch (error) {
      if (error instanceof ApiClientError) {
        setTestMessageKey(error.respuestaKey === 'tenant.invalid' ? 'tenant.invalid' : 'mobile.config.testFailed');
      } else {
        setTestMessageKey('mobile.config.testFailed');
      }
    } finally {
      setIsTesting(false);
    }
  }

  return (
    <Popup
      visible={isOpen}
      onHiding={onClose}
      showTitle
      title={t('mobile.config.title')}
      width={360}
      height="auto"
      dragEnabled={false}
      hideOnOutsideClick
    >
      <div className="mobileConfigPopup">
        <label className="mobileConfigPopup__field">
          <span className="mobileConfigPopup__label">{t('mobile.config.apiUrl')}</span>
          <TextBox
            value={apiBaseUrl}
            stylingMode="outlined"
            inputAttr={{
              'data-testid': 'mobileConfigApiUrl',
              placeholder: t('mobile.config.apiUrlPlaceholder'),
            }}
            onValueChanged={(event) => {
              setApiBaseUrl(String(event.value ?? ''));
            }}
          />
        </label>

        {testMessageKey !== null && (
          <p className="mobileConfigPopup__message" data-testid="mobileConfigTestResult">
            {t(testMessageKey)}
          </p>
        )}

        <div className="mobileConfigPopup__actions">
          <Button
            text={t('mobile.config.testConnection')}
            stylingMode="outlined"
            disabled={isTesting}
            elementAttr={{ 'data-testid': 'mobileConfigTestConnection' }}
            onClick={() => {
              void handleTestConnection();
            }}
          />
          <Button
            text={t('mobile.config.save')}
            type="default"
            disabled={isSaving}
            elementAttr={{ 'data-testid': 'mobileConfigSave' }}
            onClick={() => {
              void handleSave();
            }}
          />
        </div>
      </div>
    </Popup>
  );
}
