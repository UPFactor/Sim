Sim — Template language for PHP
===============================

SimTemplater — это инструмент, который позволяет отделить логику скрипта от его представления и организовать работу между профильными специалистами на уровне обмена нотациями о структуре данных (интерфейсов).
Синтаксис SimTemplater основан на data-атрибутах элементов DOM. Такой подход исключить зависимость представления от шаблонизатора, что дает возможность предварительного просмотра шаблонов с использованием демонстрационных данных, также устраняет проблему подсветки HTML синтаксиса в редакторе разработчика.

В известных шаблонизаторах, представления могут выглядеть приблизительно так:

```html
<h1>Users</h1>
<ul>
      {% for user in users %}
           <li>
                {{ user.username|e }}
           </li>
      {% endfor %}
</ul>
```

Представление в SimTemplater

```html
<h1>Users</h1>
<ul>
     <li data-sim="repeat($data.users as $user); content($user.item:e);">
          User 1
     </li>
     <li data-sim="ignore();">User 2</li>
     <li data-sim="ignore();">User 3</li>
     <li data-sim="ignore();">User 4</li>
</ul>
```

Такой шаблон будет корректно отображен в браузере при прямом вызове, что позволит продемонстрировать его заказчику даже на том этапе, когда данных с которыми он работает не существует.

Быстрый старт
-------------

Шаблоном может быть любой HTML документ или синтаксически верный XML документ. Для нашего примера, создадим файл 'my_template_file.html'

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Title</title>
</head>
<body>
    <h1 data-sim="content($data.title)">Header Example</h1>
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
</body>
</html>
```

Все, что Вам понадобиться сделать в php - это включить (include) библиотеку Sim и, установить несколько переменных, для настройки шаблонизатора.

```php
<?php
require_once("Sim.php");

//Создаем объект шаблонизатора
$sim = new \SimTemplate\Sim();

//Путь до директории с шаблонами
$sim->setRootPath('/templates/');

//Путь до директории хранения кеш-файлов
$sim->setCachePath('/cache/');

//Создаем массив данных
$data = array(
    'title' => 'Employee phone numbers',
    'people' => array(
        array('name' => 'Alex', 'phone' => '+12132775504'),
        array('name' => 'William', 'phone' => '+12132102192'),
        array('name' => 'Daniel', 'phone' => '+12136402431')
    )
);

//Выполняем обработку шаблона
$sim->execute('my_template_file.html', $data);
```

Результат выполнение:

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Title</title>
</head>
<body>
    <h1>Employee phone numbers</h1>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Phone</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td >Alex</td>
                <td >+12132775504</td>
            </tr>
            <tr>
                <td >William</td>
                <td >+12132102192</td>
            </tr>
            <tr>
                <td >Daniel</td>
                <td >+12136402431</td>
            </tr>            
        </tbody>
    </table>
</body>
</html>
```

SimTemplater не заботится о переносах строки и отступах ни в читаемых, ни в генерируемых файлах. Чтобы получить красивый HTML (с переносами и правильными отступами), вы можете использовать дополнительные утилиты на этапе постпроцессинга.
