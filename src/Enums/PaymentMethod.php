<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\Enums;

enum PaymentMethod: string
{
    case Multibanco = 'PC:PT';
    case MBWay = 'MW:PT';
    case CreditCard = 'CC:PT';
    case GooglePay = 'GP:PT';
    case ApplePay = 'AP:PT';
}
