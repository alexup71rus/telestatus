<?php

namespace Intensa\Telestatus;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Event;
use CSaleStatus;

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

        // текущий статус
        $currentStatus = $event->getParameter('VALUE');
        $arCurrentStatus = CSaleStatus::GetByID($currentStatus);
        $currentStatusName = $arCurrentStatus['NAME'];

        // предыдущий статус
        $oldStatus = $event->getParameter('OLD_VALUE');
        $arOldStatus = CSaleStatus::GetByID($oldStatus);
        $oldStatusName = $arOldStatus['NAME'];

        // это можно было бы вынести в отдельный файл, вместе с id модуля
        $options = [
            '%ID%' => $orderId,
            '%STATUS%' => $currentStatusName,
            '%PREV_STATUS%' => $oldStatusName,
            '%PRICE%' => $sum . ' Руб.', // эту инфу тоже из битрикса можно получать, но не стал усложнять
        ];

        $request = "https://api.telegram.org/bot{$token}/sendMessage?chat_id=@{$channel}&text=";
        $request .= self::buildTemplate($message, $options);

        if (!file_get_contents($request)) {
            // тут можно обрабатывать ошибки
        }

        return false;
    }

    public static function buildTemplate(string $template, array $options): string
    {
        // \r, \n и другие переносы строк, кроме %0A телеграм воспринимает как _. Получается, что было очень много _,
        // поэтому сначала надо очистить \r а потом преобразовать \n в перенос строки, который телеграм поймёт - %0A
        $template = str_replace("\r", "", $template);
        $template = str_replace("\n", "%0A", $template);

        foreach ($options as $key => $value) {
            $template = str_replace($key, $value, $template);
        }

        return $template;
    }
}