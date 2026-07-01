import { useState } from 'react';
import Button from 'devextreme-react/button';
import { MobileConfigPopup } from './MobileConfigPopup';
import './mobileConfigPopup.css';

type MobileConfigButtonProps = {
  testId?: string;
  tenantForHealthCheck?: string;
};

export function MobileConfigButton({
  testId = 'mobileConfigOpen',
  tenantForHealthCheck,
}: MobileConfigButtonProps) {
  const [isOpen, setIsOpen] = useState(false);

  return (
    <>
      <Button
        className="mobileConfigButton"
        icon="preferences"
        stylingMode="text"
        elementAttr={{ 'data-testid': testId }}
        onClick={() => {
          setIsOpen(true);
        }}
      />
      <MobileConfigPopup
        isOpen={isOpen}
        tenantForHealthCheck={tenantForHealthCheck}
        onClose={() => {
          setIsOpen(false);
        }}
      />
    </>
  );
}
