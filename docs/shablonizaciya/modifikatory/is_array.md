# is\_array

Возвращает `true`, если переменная является массивом, в противном случае `false`

## **Синтаксис**

```text
mixed $var:is_array;
```

\*\*\*\*

## **Пример использования**

Входные данные:

```php
Array
(
    ['myvar'] => Array
        (
            [0] => 'Alex'
            [1] => 'William'
            [2] => 'Daniel'
        )
)
```

Шаблон:

```markup
<p data-sim="if ($data.myvar:is_array ? content('This is an array'));">
   … 
</p>​
```

Результат выполнения:

```markup
<p>This is an array</p>
```

