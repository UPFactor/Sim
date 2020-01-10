# attributes/attr

Создает, удаляет или изменяет значение атрибута элемента DOM. Если указанный атрибут отсутствует, то он будет создан с заданным значением. Если указанный атрибут существует, то его значение будет заме

### **Синтаксис**

Добавление или замена значения атрибута элемента DOM:

```text
attributes('name', $expression);
attr('name', $expression);
```

Удаление атрибута элемента DOM:

```text
attributes(remove 'name');
attr(remove 'name');
```



### Примеры использования

**Пример \#1** Заменяем значение атрибута:

Шаблон:

```markup
<input name="name" data-sim="attributes('name', 'user_name')" />​
```

Результат выполнения:

```markup
<input name='user_name' />​
```



**Пример \#2** Удаляем атрибут из элемента DOM:

Шаблон:

```markup
<input type="radio" checked="checked" data-sim="attributes(remove 'checked')" />​
```

Результат выполнения:

```markup
<input type="radio" />​
```



**Пример \#3** Добавляем атрибут с заданным значением:

Входные данные:

```php
Array
(
    [user] => Array
        (
            [name] => Alex
        )

)
```

Шаблон:

```markup
<input name="user_name" data-sim="attributes('value', $data.user.name);" />​
```

Результат выполнения:

```markup
<input name="user_name" value='Alex' />​
```

