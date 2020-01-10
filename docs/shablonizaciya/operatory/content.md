# content

Добавляет значение внутрь элемента DOM. При этом если элемент имел содержимое, то оно будет заменено.

## **Синтаксис**

```text
content(expression ~default expression);
```

## Примеры использования

**Пример \#1** вывод значения в элемент DOM:

Входные данные:

```php
Array
(
    [myvar] => Variable text
)
```

Шаблон:

```markup
<p data-sim="content($data.mytext+' Example of concatenation')">
    Demo content
</p>​
```

Результат выполнения:

```markup
<p>
    Variable text Example of concatenation
</p>
```

**Пример \#2** вывод значения в элемент DOM с указанным значением по умолчанию:

Шаблон:

```markup
<p data-sim="content($empty_var ~default 'Default value')">
     Demo content
</p>​
```

Результат выполнения:

```markup
<p>Default value</p>​
```

