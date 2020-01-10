# class

Добавляет или удаляет класс для элемента DOM. Если у текущего элемента отсутствует атрибут `class`, то он будет создан, в противном случае указанный класс будет добавлен в конец строки.

## **Синтаксис**

Добавление или замена класса в элемент DOM:

```text
class($expression);
```

Удаление класса из элемента DOM:

```text
class(remove $expression);
```

## Примеры использования

**Пример \#1** Добавляем класс:

Входные данные:

```php
Array
(
    [newsclass] => news
)
```

Шаблон:

```markup
<p data-sim="class('myclass');"> … </p>
<p class="myclass" data-sim="class($data.newsclass);"> … </p>​
```

Результат выполнения:

```markup
<p class="myclass" > … </p>
<p class="myclass news" > … </p>​
```

**Пример \#2** Удаляем класс:

Входные данные:

```php
Array
(
    [newsclass] => news
)
```

Шаблон:

```markup
<p class="myclass news" data-sim="class(remove $data.newsclass);"> 
   … 
</p>​
```

Результат выполнения:

```markup
<p class="myclass" > … </p>​
```

