# string

Преобразует тип переменной в string.

### **Синтаксис**

```text
mixed $var:string;
```

\*\*\*\*

### **Пример использования**

Входные данные:

```php
Array
(
    ['myvar'] => 0
)
```

Шаблон:

```markup
<p data-sim="content($data.myvar:string);"> ... </p>
```

Результат выполнения:

```markup
<p>0</p>
```

