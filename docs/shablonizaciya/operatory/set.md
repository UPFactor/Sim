# set

Устанавливает значение для переменной

### **Синтаксис**

```text
set($var, expression);
```



### Примеры использования

**Пример \#1** Создаем новую переменную и устанавливаем в нее значение:

Шаблон:

```markup
<p data-sim="set($var, 'Variable text'); content($var);">
     Demo content
</p>​
```

Результат выполнения:

```markup
<p>Variable text</p>​
```



**Пример \#2** Переопределение значения переменной массива `$data`:

Входные данные:

```php
Array
(
    [myvar] => Variable text
)
```

Шаблон:

```markup
<p data-sim="set($data.myvar, 'New text'); content($data.myvar);">
     Demo content
</p>-
```

Результат выполнения:

```markup
<p>New text</p>​
```



**Пример \#3** Передаем в переменную массив:

Шаблон:

```markup
<p data-sim="set($var, [0,1,2,3,4,5]); content($var:join{' and '});">
     Demo content
</p>​
```

Результат выполнения:

```markup
<p>0 and 1 and 2 and 3 and 4 and 5</p>
```



**Пример \#4** Передаем в переменную ассоциативный массив:

Шаблон:

```markup
<p data-sim="set($var, ['key1'=>'val1','key2'=>'val2']); content($var:join);">
     Demo content
</p>​
```

Результат выполнения:

```markup
<p>val1,val2</p>
```

