# include

Оператор включает в себя шаблон и возвращает отображаемое содержимое этого файла в текущее пространство имен.

## **Синтаксис**

```text
include($template [, $params ~set $var]);
```

`$template` — переменная или строка содержащая адрес или путь до шаблона

`$data` — массив значений который будет передан в шаблон \(необязательный\). Если параметр передан то для шаблона доступ к текущему пространству имен будет отключен.

`~set` — если параметр установлен, то сгенерированный включаемый шаблон будет сохранен в переменную `$var` \(необязательный\). Если параметр `~set` не указан, то полученное значение будет добавлено внутрь текущего элемента DOM.

## Примеры использования

**Пример \#1** Подключение шаблона в текущую область имен:

Шаблон:

```markup
<!DOCTYPE html>
<html lang="en">
    <head data-sim="include('/templates/include_head.html')">
        ...
    </head>
    <body>
        <h1 data-sim="content($data.title)">Header Example</h1>
        <div data-sim="include('/templates/include_table.html')">
            ...
        </div>
    </body>
</html>​
```

**Пример \#2** Подключение шаблона в текущую область имен и передача его отображаемого содержимого в переменную:

Шаблон:

```markup
<!DOCTYPE html>
<html lang="en" data-sim="
    include('/templates/include_head.html' ~set $head_template);
    include('/templates/include_table.html' ~set $table_template);
">
    <head data-sim="content($head_template);">
        ...
    </head>
    <body>
        <h1 data-sim="content($data.title)">Header Example</h1>
        <div data-sim="content($table_template);">
            ...
        </div>
    </body>
</html>
```

**Пример \#3** Подключение шаблона в контексте

```markup
<!DOCTYPE html>
<html lang="en">
    <head data-sim="include('/templates/include_head.html', ['title' => $data.title]);">
        ...
    </head>
    <body>
        <h1 data-sim="content($data.title)">Header Example</h1>
        <div data-sim="include('/templates/include_table.html', $data);">
            ...
        </div>
    </body>
</html>​
```

Все представленные примеры использования оператора «include» дадут одинаковый результат выполнения:

```markup
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title>Employee phone numbers</title>
    </head>
    <body>
        <h1>Employee phone numbers</h1>
        <div>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Alex</td>
                        <td>+12132775504</td>
                    </tr>
                    <tr>
                        <td>William</td>
                        <td>+12132102192</td>
                    </tr>
                    <tr>
                        <td>Daniel</td>
                        <td>+12136402431</td>
                    </tr>    
                </tbody>
            </table>
        </div>
    </body>
</html>
```

Для реализации примеров были использованы:

Входные данные:

```php
Array
(
    [title] => Employee phone numbers
    [people] => Array
        (
            [0] => Array
                (
                    [name] => Alex
                    [phone] => +12132775504
                )

            [1] => Array
                (
                    [name] => William
                    [phone] => +12132102192
                )

            [2] => Array
                (
                    [name] => Daniel
                    [phone] => +12136402431
                )

        )
)
```

Шаблон include\_head.html

```markup
<meta charset="UTF-8" />
<title data-sim="content($data.title);">Title</title>
```

Шаблон include\_table.html

```markup
<table>
    <thead>
    <tr>
        <th>Name</th>
        <th>Phone</th>
    </tr>
    </thead>
    <tbody>
    <tr data-sim="repeat($data.people as $person);">
        <td data-sim="content($person.item.name);">Someone's name</td>
        <td data-sim="content($person.item.phone);">Someone's phone</td>
    </tr>
    <tr data-sim="ignore();">
        <td>Andreas</td>
        <td>5226611</td>
    </tr>
    <tr data-sim="ignore();">
        <td>Wolfgang</td>
        <td>5226611</td>
    </tr>
    </tbody>
</table>
```

