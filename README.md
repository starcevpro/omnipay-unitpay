# UnitPay для Omnipay

Платежный шлюз [UnitPay](https://unitpay.ru/) для [Omnipay](https://github.com/thephpleague/omnipay).

## Установка

Установка через composer:

```bash
composer require your-vendor/omnipay-unitpay
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
    'email' => 'customer@example.com' // Опционально
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
* `email` - email плательщика (опционально)

### CompletePurchase

Не требует дополнительных параметров, все необходимые данные получает из webhook-запроса.

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