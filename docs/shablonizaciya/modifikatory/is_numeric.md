# is\_numeric

Возвращает `true`, если переменная является числом или строкой, содержащей число, в противном случае `false`

### **Синтаксис**

```text
mixed $var:is_numeric;
```



### **Пример использования**

Входные данные:

```php
Array
(
    ['myvar'] => '123456.78'
)
```

Шаблон:

```markup
<p data-sim="if($data.myvar:is_numeric ? content('This is a number'));">
  … 
</p>
```

Результат выполнения:

```markup
<p>This is a number</p>
```

