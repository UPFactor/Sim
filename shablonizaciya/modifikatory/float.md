# float

Преобразует тип переменной в float.

### **Синтаксис**

```text
mixed $var:float;
```

\*\*\*\*

### **Пример использования**

Входные данные:

```php
Array
(
    ['myvar'] => '3.14 mm'
)
```

Шаблон:

```markup
<p data-sim="content($data.myvar:float);"> … </p>
```

Результат выполнения:

```markup
<p>3.14</p>
```

