# array

Преобразует тип переменной в array.

## **Синтаксис**

```text
mixed $var:array;
```

\*\*\*\*

## **Пример использования**

Входные данные:

```php
Array
(
    ['myvar'] => 'yes'
)
```

Шаблон:

```markup
<p data-sim="repeat($data.myvar:array as $element); content($element.item)">
     ... 
</p>
```

Результат выполнения:

```markup
<p>yes</p>
```

