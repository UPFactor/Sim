# lower

Преобразует строку в нижний регистр

## **Синтаксис**

```text
string $var:lower;
```

\*\*\*\*

## **Пример использования**

Входные данные:

```php
Array
(
    ['myvar'] => 'This Is String'
)
```

Шаблон:

```markup
<p data-sim="content($data.myvar:lower);"> … </p>
```

Результат выполнения:

```markup
<p>this is string</p>
```

