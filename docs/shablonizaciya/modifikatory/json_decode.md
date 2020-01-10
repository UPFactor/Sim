# json\_decode

Декодирует JSON строку в ассоциативный массив

## **Синтаксис**

```text
string $var:json_decode;
```

## **Пример использования**

Входные данные:

```php
Array
(
    [user] => {"name":"Alex","status":"admin","phone":"+71234567890"}
)
```

Шаблон:

```markup
<div data-sim="foreach($data.user:json_decode as $property);">
     <p data-sim="content($property.item);"> ... </p>
</div>​
```

Результат выполнения:

```markup
<div >
    <p >Alex</p>
    <p >admin</p>
    <p >+71234567890</p>
</div>
```

