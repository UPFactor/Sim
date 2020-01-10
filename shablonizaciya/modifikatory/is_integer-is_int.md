# is\_integer/is\_int

Возвращает `true`, если переменная содержит целочисленное значение, в противном случае `false`

### **Синтаксис**

```text
mixed $var:is_integer;
mixed $var:is_int;
```

\*\*\*\*

### **Пример использования**

Входные данные:

```php
Array
(
    ['myvar'] => 123456
)
```

Шаблон:

```markup
<p data-sim="if($data.myvar:is_integer ? content('This is an integer'));"> 
   … 
</p>
```

Результат выполнения:

```markup
<p>This is an integer</p>​
```

