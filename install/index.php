<?php

use Bitrix\Main\Application;
use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Config\Option;


Loc::loadMessages(__FILE__);

class intensa_telestatus extends CModule
{
    protected string $partnerUri = 'https://intensa.ru/';

    public function __construct()
    {
        $arModuleVersion = [];
        include __DIR__ . '/version.php';

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_ID = str_replace("_", ".", get_class($this));
        $this->MODULE_NAME = Loc::getMessage('TELESTATUS_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('TELESTATUS_MODULE_DESCRIPTION');
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = 'Y';
        $this->MODULE_GROUP_RIGHTS = 'Y';
        $this->PARTNER_NAME = Loc::getMessage('TELESTATUS_MODULE_PARTNER_NAME');
        $this->PARTNER_URI = $this->partnerUri;
    }

    public function doInstall(): void
    {
        global $APPLICATION;

        if ($this->isVersionD7()) {
            ModuleManager::registerModule($this->MODULE_ID);
            $this->InstallEvents();
        } else {
            $APPLICATION->ThrowException(Loc::getMessage('TELESTATUS_INSTALL_ERROR_VERSION'));
        }
    }

    public function doUninstall(): void
    {
        global $APPLICATION;

        $context = Application::GetInstance()->getContext();
        $request = $context->getRequest();

        if ($request['step'] === null) {
            $APPLICATION->IncludeAdminFile(Loc::getMessage('TELESTATUS_UNINSTALL_TITLE'),
                __DIR__ . '/unstep.php');
        } elseif ($request['step'] === '2') {
            if ($request['savedata'] !== 'Y') {
                Option::delete($this->MODULE_ID);
            }

            $this->UnInstallEvents();
            ModuleManager::unRegisterModule($this->MODULE_ID);
        }
    }

    public function InstallEvents(): bool
    {
        EventManager::getInstance()->registerEventHandler(
            "sale",
            "OnSaleStatusOrderChange",
            $this->MODULE_ID,
            "Intensa\Telestatus\Sender",
            "onStatusChange"
        );

        return false;
    }

    public function UnInstallEvents(): bool
    {
        EventManager::getInstance()->unRegisterEventHandler(
            "sale",
            "OnSaleStatusOrderChange",
            $this->MODULE_ID,
            "Intensa\Telestatus\Sender",
            "onStatusChange"
        );

        return false;
    }

    public function isVersionD7(): bool
    {
        return CheckVersion(ModuleManager::getVersion('main'), '14.00.00');
    }
}