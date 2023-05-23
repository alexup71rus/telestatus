<?php

namespace Intensa\Telestatus;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Sender
{
    public static function onStatusChange(\Bitrix\Main\Event $event)
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

        $request = "https://api.telegram.org/bot{$token}/sendMessage?chat_id=@{$channel}&text=test";

        $request .= 'id: '
            . $orderId
            . ', old status: '
            . $oldStatusName
            . ', status: '
            . $currentStatusName
            . ', price: '
            . $sum
            . 'Руб.';

        if (!file_get_contents($request)) {
            // logger
        }

        return false;
    }
}