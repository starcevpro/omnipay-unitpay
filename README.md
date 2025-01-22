# UnitPay для Omnipay

Платежный шлюз [UnitPay](https://unitpay.ru/) для [Omnipay](https://github.com/thephpleague/omnipay).

## Установка

Установка через composer:

```bash
composer require starcevpro/omnipay-unitpay
```

## Использование

### Инициализация шлюза

```php
use Omnipay\Omnipay;

$gateway = Omnipay::create('UnitPay');

$gateway->setSecretKey('ваш-секретный-ключ');
$gateway->setPublicKey('ваш-публичный-ключ');
```

### Создание платежа

```php
$response = $gateway->purchase([
    'amount' => '100.00',
    'currency' => 'RUB',
    'transactionId' => '123456', // Ваш уникальный идентификатор платежа
    'description' => 'Оплата заказа №123456',
    // Обязательно указать email или телефон
    'email' => 'customer@example.com',
    // или
    'phone' => '79991234567',
    // Опционально - позиции чека
    'cashItems' => [
        [
            'name' => 'Название товара',
            'count' => 1,
            'price' => 100.00,
            'currency' => 'RUB',
            'nds' => 'vat20', // Ставка НДС
            'type' => 'commodity', // Тип позиции
            'paymentMethod' => 'full_payment' // Признак способа расчета
        ]
    ]
])->send();

if ($response->isRedirect()) {
    // Перенаправляем пользователя на страницу оплаты
    $response->redirect();
}
```

### Обработка уведомления о платеже (webhook)

```php
$gateway = Omnipay::create('UnitPay');
$gateway->setSecretKey('ваш-секретный-ключ');

$response = $gateway->completePurchase($_POST)->send();

if ($response->isSuccessful()) {
    // Платеж успешно оплачен
    $transactionReference = $response->getTransactionReference();
    $amount = $response->getAmount();
    // Обновите статус заказа в вашей системе
}
```

### Тестовый режим

UnitPay предоставляет тестовый режим для отладки. В тестовом режиме используется отдельный секретный ключ:

```php
$gateway->setSecretKey('боевой-секретный-ключ');
$gateway->setTestSecretKey('тестовый-секретный-ключ');
$gateway->setTestMode(true);
```

**Важно**: даже в тестовом режиме подпись запросов генерируется с использованием боевого секретного ключа (особенность UnitPay).

## Поддерживаемые методы

* `purchase()` - создание платежа
* `completePurchase()` - обработка уведомления о платеже

## Поддерживаемые параметры

### Purchase

* `amount` - сумма платежа
* `currency` - валюта платежа (по умолчанию RUB)
* `transactionId` - уникальный идентификатор платежа в вашей системе
* `description` - описание платежа
* `email` - email плательщика (обязателен, если не указан телефон)
* `phone` - телефон плательщика в формате 79991234567 (обязателен, если не указан email)
* `cashItems` - позиции чека для фискализации (опционально)

### CompletePurchase

Не требует дополнительных параметров, все необходимые данные получает из webhook-запроса.

### Параметры для формирования чека (cashItems)

Каждая позиция в массиве `cashItems` может содержать следующие параметры:

* `name` - название позиции (обязательный, не более 128 символов)
* `count` - количество (обязательный)
* `price` - цена за единицу (обязательный)
* `currency` - валюта (по умолчанию RUB)
* `nds` - ставка НДС (none, vat0, vat10, vat20 и др.)
* `type` - тип позиции (commodity, service и др.)
* `paymentMethod` - признак способа расчета (full_payment, full_prepayment и др.)

## Тестирование

```bash
composer test
```

## Требования

* PHP 8.0 или выше
* PHP JSON extension
* PHP cURL extension

## Лицензия

MIT

## Поддержка

Если вы обнаружили ошибку или у вас есть предложения по улучшению пакета, пожалуйста, создайте issue в репозитории.