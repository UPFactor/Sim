# is\_float

Возвращает `true`, если переменная является числом с плавающей точкой, в противном случае `false`

## **Синтаксис**

```text
mixed $var:is_float;
```

\*\*\*\*

## **Пример использования**

Входные данные:

```php
Array
(
    ['myvar'] => 3.14
)
```

Шаблон:

```markup
<p data-sim="if($data.myvar:is_float ? content('This is a fractional number'));">
  … 
</p>
```

Результат выполнения:

```markup
<p>This is a fractional number</p>
```

