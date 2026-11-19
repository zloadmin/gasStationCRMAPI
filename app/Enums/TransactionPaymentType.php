<?php

namespace App\Enums;

use App\Enums\Traits\HasDictionaryResponse;

enum TransactionPaymentType : string
{
    use HasDictionaryResponse;

    case payment_cash = 'payment_cash';
    case payment_card = 'payment_card';
    case payment_sbp = 'payment_sbp';
    case payment_cash_cancel = 'payment_cash_cancel';
    case no_activity_cancel = 'no_activity_cancel';
    case free_user = 'free_user';
    case free_corp = 'free_corp';
    case ret_cash = 'ret_cash';
    case ret_card = 'ret_card';
    case encash = 'encash';
    case test_refill = 'test_refill';
    case payment_promo = 'payment_promo';
    case payment_fuel_card = 'payment_fuel_card';
    case payment_yandex_tank = 'payment_yandex_tank';

    public function label(): string
    {
        return match ($this) {
            self::payment_cash => 'Наличными',
            self::payment_card => 'Картой',
            self::payment_sbp => 'СБП',
            self::payment_cash_cancel => 'Отмена пользователем',
            self::no_activity_cancel => 'Отмена по неактивности',
            self::free_user => 'Оплата бонусами',
            self::free_corp => 'Оплата корп. бонусами',
            self::ret_cash => 'Возврат (нал)',
            self::ret_card => 'Возврат (безнал)',
            self::encash => 'Инкассация',
            self::test_refill => 'Тестовый налив',
            self::payment_promo => 'Оплата чеками возврата',
            self::payment_fuel_card => 'Оплата топливной картой',
            self::payment_yandex_tank => 'Яндекс заправки',
        };
    }
}
