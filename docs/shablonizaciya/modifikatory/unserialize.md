# unserialize

Конвертирует сериализованное значение переменной.

### **Синтаксис**

```text
array $var:unserialize;
```



### **Пример использования**

Входные данные:

```php
Array
(
    [user] => a:4:{s:4:"name";s:4:"Alex";s:6:"status";s:5:"admin";s:5:"phone";s:12:"+71234567890";s:4:"mail";s:13:"alex@mail.com";}
)
```

Шаблон:

```markup
<div data-sim="foreach($data.user:unserialize as $property);">
   <p data-sim="content($property.item);"> ... </p>
</div>​
```

Результат выполнения:

```markup
<div>
    <p >Alex</p>
    <p >admin</p>
    <p >+71234567890</p>
    <p >alex@mail.com</p>
</div>​
```

