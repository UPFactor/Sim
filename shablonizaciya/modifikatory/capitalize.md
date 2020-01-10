# capitalize

Выполняет преобразование строки: первый символ устанавливается в верхний регистр, все остальные в нижний

### **Синтаксис**

```text
string $var:capitalize;
```

\*\*\*\*

### **Пример использования**

Входные данные:

```php
Array
(
    ['myvar'] => 'this is string'
)
```

Шаблон:

```markup
<p data-sim="content($data.myvar:capitalize);"> … </p>
```

Результат выполнения:

```markup
<p>This is string</p>
```

