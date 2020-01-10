# integer

Преобразует тип переменной в integer.

## **Синтаксис**

```text
mixed $var:integer;
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
<p data-sim="content($data.myvar:integer);"> … </p>
```

Результат выполнения:

```markup
<p>3</p>
```

