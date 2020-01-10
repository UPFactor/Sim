# sort

Выполняет сортировку массива по возрастанию или убыванию.

### **Синтаксис**

```text
array $var:sort{direction};
```

Направление сортировки определяется необязательным параметром «direction», который может принимать два значения:

`ask` — сортировать по возрастанию \(используется по умолчанию\)

`desc` — сортировать по убыванию

\*\*\*\*

### **Пример использования**

Входные данные:

```php
Array
(
    [users] => Array
        (
            [0] => 'Alex'
            [1] => 'William'
            [2] => 'Daniel'
        )
)
```

Шаблон:

```markup
<ul data-sim="foreach($data.users:sort{ask} as $user);">
    <li data-sim="content($user.item)"></li>
</ul>
   
<ul data-sim="foreach($data.users:sort{desc} as $user);">
    <li data-sim="content($user.item)"></li>
</ul>
```

Результат выполнения:

```markup
<ul>
    <li>Alex</li>
    <li>Daniel</li>
    <li>William</li>
</ul>
    
<ul>
    <li>William</li>
    <li>Daniel</li>
    <li>Alex</li>
</ul>​
```

