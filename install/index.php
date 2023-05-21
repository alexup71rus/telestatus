<?php

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\ModuleManager;
use \Bitrix\Main\Config\Option;


Loc::loadMessages(__FILE__);

class intensa_telestatus extends CModule
{
    public function __construct()
    {
        $arModuleVersion = [];
        include __DIR__ . '/version.php';

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_ID = 'intensa.telestatus';
        $this->MODULE_NAME = Loc::getMessage('TELESTATUS_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('TELESTATUS_MODULE_DESCRIPTION');
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = 'Y';
        $this->MODULE_GROUP_RIGHTS = 'Y';
        $this->PARTNER_NAME = Loc::getMessage('TELESTATUS_MODULE_PARTNER_NAME');
        $this->PARTNER_URI = 'https://intensa.ru/';
    }

    public function doInstall()
    {
        global $APPLICATION;

        if ($this->isVersionD7()) {
            ModuleManager::registerModule($this->MODULE_ID);
        } else {
            $APPLICATION->ThrowException(Loc::getMessage('TELESTATUS_INSTALL_ERROR_VERSION'));
        }
    }

    public function doUninstall()
    {
        global $APPLICATION;

        $context = \Bitrix\Main\Application::GetInstance()->getContext();
        $request = $context->getRequest();

        if ($request['step'] === null) {
            $APPLICATION->IncludeAdminFile(Loc::getMessage('TELESTATUS_UNINSTALL_TITLE'),
                __DIR__ . '/unstep.php');
        } elseif ($request['step'] === '2') {
            if ($request['savedata'] !== 'Y') {
                Option::delete($this->MODULE_ID);
            }

            ModuleManager::unRegisterModule($this->MODULE_ID);
        }
    }

    public function isVersionD7(): bool
    {
        return CheckVersion(ModuleManager::getVersion('main'), '14.00.00');
    }
}