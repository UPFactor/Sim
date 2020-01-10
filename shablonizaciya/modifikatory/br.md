# br

Заменяет символ перевода строки на тег `<br>`.

### **Синтаксис**

```text
string $var:br;
```

\*\*\*\*

### **Пример использования**

Входные данные:

```php
Array
(
    ['myvar'] => "Alex \n William \n Daniel"
)
```

Шаблон:

```markup
<p data-sim="content($data.myvar:br);"> … </p>
```

Результат выполнения:

```markup
<p>Alex <br> William <br> Daniel</p>
```

