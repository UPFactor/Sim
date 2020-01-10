# json\_encode/json

Возвращает JSON-представление данных

### **Синтаксис**

```text
mixed $var:json_encode;
mixed $var:json;
```

### 

### **Пример использования**

Входные данные:

```php
Array
(
    [myarray] => Array
        (
            [headline] => mypage headline
            [user] => Array
                (
                    [name] => Alex
                    [status] => admin
                    [phone] => +71234567890
                    [mail] => alex@mail.com
                )

        )
)
```

Шаблон:

```markup
<p data-sim="attributes('data-json', $data.myarray:json_encode);"> 
    … 
</p>
```

Результат выполнения:

```markup
<p data-json='{"headline":"mypage headline","user":{"name":"Alex","status":"admin","phone":"+71234567890","mail":"alex@mail.com"}}' > 
    … 
</p>
```

