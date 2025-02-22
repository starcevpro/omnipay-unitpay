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

Основные параметры:
* `amount` - сумма платежа в выбранной валюте
* `currency` - валюта платежа (RUB, UAH, BYN, EUR, USD и др. по стандарту ISO 4217)
* `transactionId` - уникальный идентификатор платежа в вашей системе
* `description` - описание платежа
* `email` - email плательщика (обязателен, если не указан телефон)
* `phone` - телефон плательщика в формате 79991234567 (обязателен, если не указан email)
* `cashItems` - позиции чека для фискализации (опционально)

Дополнительные параметры:
* `locale` - язык платежной формы (ru, en)
* `backUrl` - адрес возврата пользователя при отмене платежа. Должен использовать домен проекта
* `subscription` - создание подписки (true/false)

```php
// Пример использования дополнительных параметров
$response = $gateway->purchase([
    'amount' => '100.00',
    'currency' => 'RUB',
    'transactionId' => '123456',
    'description' => 'Оплата заказа №123456',
    'email' => 'customer@example.com',
    
    // Дополнительные параметры
    'locale' => 'ru',
    'backUrl' => 'https://your-domain.com/payment/cancel',
    'subscription' => 'true',
    
    // Позиции чека
    'cashItems' => [
        [
            'name' => 'Название товара',
            'count' => 1,
            'price' => 100.00,
            'currency' => 'RUB',
            'nds' => 'vat20',
            'type' => 'commodity',
            'paymentMethod' => 'full_payment'
        ]
    ]
])->send();
```

### Параметры для формирования чека (cashItems)

Каждая позиция в массиве `cashItems` может содержать следующие параметры:

* `name` - название позиции (обязательный, не более 128 символов)
* `count` - количество (обязательный)
* `price` - цена за единицу (обязательный)
* `currency` - валюта (по умолчанию RUB)
* `nds` - ставка НДС:
    * none - без НДС
    * vat0 - НДС 0%
    * vat10 - НДС 10%
    * vat20 - НДС 20%
    * vat110 - НДС 10/110 (для предоплаты)
    * vat120 - НДС 20/120 (для предоплаты)
* `type` - тип позиции (commodity - товар, service - услуга и др.)
* `paymentMethod` - признак способа расчета:
    * full_payment - полный расчет
    * full_prepayment - предоплата 100%
    * prepayment - предоплата
    * advance - аванс

### Маркировка товаров

Для маркированных товаров в позициях `cashItems` доступны дополнительные параметры:

* `markCode` - код маркировки товара
* `measure` - единица измерения:
    * 0 - штуки
    * 10 - грамм
    * 11 - килограмм
    * 12 - тонна
    * 20 - сантиметр
* `markQuantity` - объем маркированной партии:
  ```php
  'markQuantity' => [
      'numerator' => 2,    // числитель
      'denominator' => 10  // знаменатель
  ]
  ```

```php
// Пример с маркированным товаром
$gateway->purchase([
    // ... основные параметры ...
    'cashItems' => [
        [
            'name' => 'Маркированный товар',
            'count' => 1,
            'price' => 100.00,
            'nds' => 'vat20',
            'type' => 'commodity',
            'markCode' => 'код маркировки',
            'measure' => 0,
            'markQuantity' => [
                'numerator' => 2,
                'denominator' => 10
            ]
        ]
    ]
]);
```

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
