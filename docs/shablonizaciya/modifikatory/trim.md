# trim

Удаляет пробелы \(или другие символы\) из начала и конца строки

### **Синтаксис**

```text
string $var:trim;
```



### **Пример использования**

Входные данные:

```php
Array
(
    [myvar] => '  text  '
)
```

Шаблон:

```markup
<p data-sim="content($data.myvar:trim);"> … </p>​
```

Результат выполнения:

```markup
<p>text</p>​
```

