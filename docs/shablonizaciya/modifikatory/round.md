# round

Округляет число типа float

### **Синтаксис**

```text
float $var:round{integer $precision | mode};
```

Модификатор имеет два необязательных параметра:

`$precision` — количество десятичных знаков, до которых округлять   
\(по умолчанию 0\).

`mode` — метод округления. up: в большую сторону \(по умолчанию\),   
down: в меньшую сторону.



### **Пример использования**

Входные данные:

```php
Array
(
    [myvar] => 10.115
)
```

Шаблон:

```markup
<p data-sim="content($data.myvar:round);"> … </p>
<p data-sim="content($data.myvar:round{2});"> … </p>
<p data-sim="content($data.myvar:round{2|down});"> … </p>
<p data-sim="content($data.myvar:round{2|up});"> … </p>
```

Результат выполнения:

```markup
<p>10</p>
<p>10.12</p>
<p>10.11</p>
<p>10.12</p>​
```

