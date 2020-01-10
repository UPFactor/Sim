# striptags

Удаляет HTML-теги из строки

## **Синтаксис**

```text
string $var:striptags;
```

\*\*\*\*

## **Пример использования**

Входные данные:

```php
Array
(
    ['myvar'] => "this<br> is<br> 'string'<br>"
)
```

Шаблон:

```markup
<p data-sim="content($data.myvar:striptags);"> … </p>
```

Результат выполнения:

```markup
<p>this is 'string'</p>
```

