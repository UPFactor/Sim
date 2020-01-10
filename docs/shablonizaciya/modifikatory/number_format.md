# number\_format

Форматирует число с разделением групп

### **Синтаксис**

```text
float $var:number_format{integer $precision | string $decimal | string $thousands};
```

Модификатор имеет три необязательных параметра:

`$precision` — число знаков после запятой \(по умолчанию 0\).

`$decimal` — разделитель дробной части \(по умолчанию «.»\)

`$thousands` — разделитель тысяч \(по умолчанию пробел « »\)



### **Пример использования**

Входные данные:

```php
Array
(
    [myvar] => 123456789.115
)
```

Шаблон:

```markup
<p data-sim="content($data.myvar:number_format);"> … </p>
<p data-sim="content($data.myvar:number_format{2|','|'.'});"> … </p>​
```

Результат выполнения:

```markup
<p>123 456 789</p>
<p>123.456.789,12</p>
```

