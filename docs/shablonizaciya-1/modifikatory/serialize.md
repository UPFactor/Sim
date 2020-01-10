# serialize

Выполняет сериализацию значения переменной

## **Синтаксис**

```text
array $var:serialize;
```

## **Пример использования**

Входные данные:

```php
Array
(
    [myarray] => Array
        (
            [name] => Alex
            [status] => admin
            [phone] => +71234567890
            [mail] => alex@mail.com
        )
)
```

Шаблон:

```markup
<p data-sim="attributes('data', $data.myarray:serialize);"> … </p>
```

Результат выполнения:

```markup
<p data='a:4:{s:4:"name";s:4:"Alex";s:6:"status";s:5:"admin";s:5:"phone";s:12:"+71234567890";s:4:"mail";s:13:"alex@mail.com";}' > 
    … 
</p>
```

