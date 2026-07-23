import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { isNativeApp } from '../../../shared/platform/isNativeApp';
import { ThemeSelectorModal } from '../../theme/components/ThemeSelectorModal';
import { useAvatarMenu } from '../hooks/useAvatarMenu';
import { resolveAvatarInitials } from '../utils/avatarInitials';
import './avatarMenu.css';

type AvatarMenuProps = {
  displayName: string;
  openInNewTab: boolean;
  isSavingOpenInNewTab: boolean;
  onOpenInNewTabChange: (value: boolean) => void;
  onLogout: () => void;
};

export function AvatarMenu({
  displayName,
  openInNewTab,
  isSavingOpenInNewTab,
  onOpenInNewTabChange,
  onLogout,
}: AvatarMenuProps) {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { containerRef, isOpen, toggleMenu, closeMenu } = useAvatarMenu();
  const [isThemeModalOpen, setIsThemeModalOpen] = useState(false);
  const initials = resolveAvatarInitials(displayName);
  const nativeApp = isNativeApp();

  function handleChangePassword() {
    closeMenu();
    navigate('/change-password');
  }

  function handleOpenChatAssistant() {
    closeMenu();

    if (nativeApp) {
      navigate('/chat-assistant');
      return;
    }

    window.open(`${window.location.origin}/chat-assistant`, '_blank', 'noopener,noreferrer');
  }

  function handleAppearance() {
    closeMenu();
    setIsThemeModalOpen(true);
  }

  async function handleLogout() {
    closeMenu();
    await onLogout();
  }

  return (
    <>
      <div className="avatarMenu" ref={containerRef}>
        <button
          type="button"
          className="avatarMenuTrigger"
          data-testid="avatarMenuTrigger"
          aria-haspopup="menu"
          aria-expanded={isOpen}
          aria-label={t('avatar.triggerLabel', { name: displayName })}
          onClick={toggleMenu}
        >
          <span className="avatarMenuInitials" aria-hidden="true">
            {initials}
          </span>
        </button>

        {isOpen && (
          <div className="avatarMenuPanel" data-testid="avatarMenuPanel" role="menu">
            {!nativeApp && (
              <label className="avatarMenuToggle" data-testid="avatarMenuItemOpenInNewTab">
                <input
                  type="checkbox"
                  checked={openInNewTab}
                  disabled={isSavingOpenInNewTab}
                  onChange={(event) => {
                    onOpenInNewTabChange(event.target.checked);
                  }}
                />
                <span>{t('avatar.openInNewTab')}</span>
              </label>
            )}

            {!nativeApp && (
              <button
                type="button"
                className="avatarMenuAction"
                role="menuitem"
                data-testid="avatarMenuItemChatAssistant"
                onClick={handleOpenChatAssistant}
              >
                {t('avatar.chatAssistant')}
              </button>
            )}

            <button
              type="button"
              className="avatarMenuAction"
              role="menuitem"
              data-testid="avatarMenuItemAppearance"
              onClick={handleAppearance}
            >
              {t('avatar.appearance')}
            </button>

            <button
              type="button"
              className="avatarMenuAction"
              role="menuitem"
              data-testid="avatarMenuItemChangePassword"
              onClick={handleChangePassword}
            >
              {t('avatar.changePassword')}
            </button>

            <button
              type="button"
              className="avatarMenuAction avatarMenuActionDanger"
              role="menuitem"
              data-testid="avatarMenuItemLogout"
              onClick={() => {
                void handleLogout();
              }}
            >
              {t('avatar.logout')}
            </button>
          </div>
        )}
      </div>

      <ThemeSelectorModal
        isOpen={isThemeModalOpen}
        onClose={() => {
          setIsThemeModalOpen(false);
        }}
      />
    </>
  );
}
