# upper

Преобразует строку в верхний регистр

### **Синтаксис**

```text
string $var:upper;
```



### **Пример использования**

Входные данные:

```php
Array
(
    ['myvar'] => 'This Is String'
)
```

Шаблон:

```markup
<p data-sim="content($data.myvar:upper);"> … </p>
```

Результат выполнения:

```markup
<p>THIS IS STRING</p>
```

