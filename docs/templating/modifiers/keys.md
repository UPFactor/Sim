# keys

Возвращает числовые и строковые ключи массива

## **Синтаксис**

```text
array $var:keys;
```

\*\*\*\*

## **Пример использования**

Входные данные:

```php
Array
(
    [myarray] => Array
        (
            [name] => 'Alex'
            [status] => 'admin'
            [phone] => '+71234567890'
            [mail] => 'alex@mail.com'
        )
)
```

Шаблон:

```markup
<p data-sim="repeat($data.myarray:keys as $key); content($key.item);"> 
   ... 
</p>
```

Результат выполнения:

```markup
<p>name</p>
<p>status</p>
<p>phone</p>
<p>mail</p>
```

