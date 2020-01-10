# is\_bool

Возвращает `true`, если переменная содержит логическое значение, в противном случае `false`

### **Синтаксис**

```text
mixed $var:is_bool;
```

\*\*\*\*

### **Пример использования**

Входные данные:

```php
Array
(
    ['myvar'] => false
)
```

Шаблон:

```markup
<p data-sim="if($data.myvar:is_bool ? content('This is a boolean'));"> 
  … 
</p>
```

Результат выполнения:

```markup
<p>This is a boolean</p>
```

