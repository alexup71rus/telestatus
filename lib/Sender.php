<?php

namespace Intensa\Telestatus;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Sender
{
    public static $options = [
        '%ID%' => '',
        '%STATUS%' => '',
        '%PREV_STATUS%' => '',
        '%PRICE%' => '',
    ];

    public static function onStatusChange(\Bitrix\Main\Event $event): bool
    {
        $module_id = pathinfo(dirname(__DIR__))["basename"];

        $token = Option::get($module_id, "field_token", "");
        $channel = Option::get($module_id, "field_channel", "");
        $message = Option::get($module_id, "field_message", "");

        if (!$token || !$channel || !$message) {
            return false;
        } else {
            $channel = str_replace('@', '', $channel);
        }

        $order = $event->getParameter("ENTITY");
        $orderId = $order->getId();
        $sum = $order->getField('PRICE');
        $currentStatus = $event->getParameter('VALUE');
        $arCurrentStatus = \CSaleStatus::GetByID($currentStatus);
        $currentStatusName = $arCurrentStatus['NAME'];
        $oldStatus = $event->getParameter('OLD_VALUE');
        $arOldStatus = \CSaleStatus::GetByID($oldStatus);
        $oldStatusName = $arOldStatus['NAME'];

        self::$options['%ID%'] = $orderId;
        self::$options['%STATUS%'] = $currentStatusName;
        self::$options['%PREV_STATUS%'] = $oldStatusName;
        self::$options['%PRICE%'] = $sum . ' Руб.';

        $request = "https://api.telegram.org/bot{$token}/sendMessage?chat_id=@{$channel}&text=";
        $request .= self::buildTemplate($message, self::$options);

        if (!file_get_contents($request)) {
            // logger
        }

        return false;
    }

    public static function buildTemplate(string $template, array $options): string
    {
        foreach ($options as $key => $value) {
            $template = str_replace($key, $value, $template);
        }

        $template = str_replace(PHP_EOL, "\r\n", $template);

        return $template;
    }
}