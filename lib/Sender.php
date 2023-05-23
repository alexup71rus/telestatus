<?php

namespace Intensa\Telestatus;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Event;
use Bitrix\Main\Localization\Loc;
use CSaleStatus;

Loc::loadMessages(__FILE__);

class Sender
{
    public static function onStatusChange(Event $event): bool
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
        $arCurrentStatus = CSaleStatus::GetByID($currentStatus);
        $currentStatusName = $arCurrentStatus['NAME'];
        $oldStatus = $event->getParameter('OLD_VALUE');
        $arOldStatus = CSaleStatus::GetByID($oldStatus);
        $oldStatusName = $arOldStatus['NAME'];
        $options = [
            '%ID%' => $orderId,
            '%STATUS%' => $currentStatusName,
            '%PREV_STATUS%' => $oldStatusName,
            '%PRICE%' => $sum . ' Руб.',
        ];

        $request = "https://api.telegram.org/bot{$token}/sendMessage?chat_id=@{$channel}&text=";
        $request .= self::buildTemplate($message, $options);

        if (!file_get_contents($request)) {
            // logger
        }

        return false;
    }

    public static function buildTemplate(string $template, array $options): string
    {
        $template = str_replace("\r", "", $template);
        $template = str_replace("\n", "%0A", $template);

        foreach ($options as $key => $value) {
            $template = str_replace($key, $value, $template);
        }

        return $template;
    }
}