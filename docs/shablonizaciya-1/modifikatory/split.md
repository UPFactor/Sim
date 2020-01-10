# split

Разбивает строку с помощью разделителя `delimiter` \(по умолчанию «,»\) и возвращает массив строк

## **Синтаксис**

```text
array $var:split{string $delimiter};
```

\*\*\*\*

## **Пример использования**

Входные данные:

```php
Array
(
    [phones] => '+71234567890', '+70987654321'
)
```

Шаблон:

```markup
<div data-sim="foreach($data.phones:split{', '} as $phone);">
     <p data-sim="content($phone.item);"> ... </p>
</div>​
```

Результат выполнения:

```markup
<div>
    <p>+71234567890</p>
    <p>+70987654321</p>
</div>
```

