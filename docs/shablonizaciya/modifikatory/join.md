# join

Объединяет элементы массива в строку с использованием разделителя `$delimiter` \(по умолчанию «,»\)

### **Синтаксис**

```text
string $var:join{string $delimiter};
```



### **Пример использования**

Входные данные:

```php
Array
(
    [user] => Array
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
<p data-sim="content($data.user:join);"> ... </p>
<p data-sim="content($data.user:join{'<br>'});"> ... </p>
```

Результат выполнения:

```markup
<p>Alex,admin,+71234567890,alex@mail.com</p>
<p>Alex<br>admin<br>+71234567890<br>alex@mail.com</p>
```

