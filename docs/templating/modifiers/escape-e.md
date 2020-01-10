# escape/e

Преобразует все возможные символы в соответствующие HTML-сущности. Выполняет преобразование как двойных кавычек, так и одинарных кавычек.

## **Синтаксис**

```text
string $var:escape;
string $var:e;
```

## **Пример использования**

Входные данные:

```php
Array
(
    [myvar] => <b>Bold text</b>
)
```

Шаблон:

```markup
<p data-sim="content($data.myvar:escape);"> … </p>
<p data-sim="content($data.myvar:e);"> … </p>
```

Результат выполнения:

```markup
<p>&lt;b&gt;Bold text&lt;/b&gt;</p>
<p>&lt;b&gt;Bold text&lt;/b&gt;</p>
```

