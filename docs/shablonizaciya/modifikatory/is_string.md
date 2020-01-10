# is\_string

Возвращает `true`, если переменная является строкой, в противном случае `false`

## **Синтаксис**

```text
mixed $var:is_string;
```

\*\*\*\*

## **Пример использования**

Входные данные:

```php
Array
(
    ['myvar'] => 'Alex'
)
```

Шаблон:

```markup
<p data-sim="if($data.myvar:is_string ? content('This is string'));"> 
  … 
</p>​
```

Результат выполнения:

```markup
<p>This is string</p>​
```

