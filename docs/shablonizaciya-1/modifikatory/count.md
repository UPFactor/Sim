# count

Возвращает количество элементов в массиве.

## **Синтаксис**

```text
integer $var:count;
```

\*\*\*\*

## **Пример использования**

Входные данные:

```php
Array
(
    [myvar] => Array
        (
            [0] => 'Alex'
            [1] => 'William'
            [2] => 'Daniel'
        )
)
```

Шаблон:

```markup
<p data-sim="content($data.myvar:count);"> 
   … 
</p>
```

Результат выполнения:

```markup
<p>3</p>
```

