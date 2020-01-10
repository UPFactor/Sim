# url\_encode

URL-кодирование строки. Возвращает строку, в которой все не цифробуквенные символы, кроме -\_. должны быть заменены знаком процента \(%\), за которым следует два шестнадцатеричных числа, а пробелы кодируются как знак сложения \(+\).

## **Синтаксис**

```text
string $var:url_encode;
```

\*\*\*\*

## **Пример использования**

Входные данные:

```php
Array
(
    ['myvar'] => 'hi google'
)
```

Шаблон:

```markup
<a data-sim="attributes('href', 'https://www.google.com/search?q='+$data.myvar:url_encode);">
     hi google
</a>​
```

Результат выполнения:

```markup
<a href='https://www.google.com/search?q=hi+google'>hi google</a>
```

