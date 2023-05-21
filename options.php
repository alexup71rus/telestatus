<?php

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config\Option;

$request = \Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest();
$module_id = htmlspecialchars($request['mid']);

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/options.php');
Loc::loadMessages(__FILE__);

\Bitrix\Main\Loader::includeModule($module_id);

$arTabs = [
    [
        'DIV' => 'settings',
        'TAB' => Loc::getMessage('INTENSA_TELESTATUS_TAB_SETTINGS'),
        'TITLE' => Loc::getMessage('INTENSA_TELESTATUS_TAB_TITLE_SETTINGS'),
        'OPTIONS' => [
            [
                'field_token',
                Loc::getMessage('INTENSA_TELESTATUS_FIELD_TOKEN_SETTINGS'),
                '',
                ['text', 55],
            ],
            [
                'field_message',
                Loc::getMessage('INTENSA_TELESTATUS_FIELD_MESSAGE_SETTINGS'),
                '',
                ['textarea', 10, 50],
            ],
        ],
    ],
    [
        'DIV' => 'RIGHTS',
        'TAB' => Loc::getMessage('MAIN_TAB_RIGHTS'),
        'TITLE' => Loc::getMessage('MAIN_TAB_TITLE_RIGHTS'),
    ],
];

if ($request->isPost() && $request['Update'] && check_bitrix_sessid()) {
    foreach ($arTabs as $arTab) {
        foreach ($arTab['OPTIONS'] as $arOption) {
            // Строка с подсветкой. Используется для разделения настроек в одной вкладке
            if (!is_array($arOption)) {
                continue;
            }

            // Уведомление с подсветкой
            if ($arOption['note']) {
                continue;
            }

            // или __AdmSettingsSaveOption($module_id, $arOption);
            $optionName = $arOption[0];
            $optionValue = $request->getPost($optionName);

            Option::set($module_id, $optionName,
                is_array($optionValue) ? implode(',', $optionValue) : $optionValue);
        }
    }
}

$tabControl = new CAdminTabControl('tabControl', $arTabs);
$formAction = $APPLICATION->GetCurPage() . '?mid=' . $module_id . '&amp;lang=' . $request['lang'];

?>
<?php $tabControl->Begin(); ?>
<form method="post" action="<?= $formAction ?>" name="intensa_telestatus">
    <?php
    foreach ($arTabs as $arTab) {
        if ($arTab['OPTIONS']) {
            $tabControl->BeginNextTab();
            __AdmSettingsDrawList($module_id, $arTab['OPTIONS']);
        }
    }

    $tabControl->BeginNextTab();
    $tabControl->Buttons();
    ?>
    <input type="submit" name="Update" class="adm-btn-save" value="<?= GetMessage('MAIN_SAVE') ?>">
    <input type="reset" name="Reset" value="<?= GetMessage('MAIN_RESET') ?>">
    <?= bitrix_sessid_post(); ?>
</form>
<?php $tabControl->End(); ?>