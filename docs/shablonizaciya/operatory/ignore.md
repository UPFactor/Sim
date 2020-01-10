# ignore

Игнорирует обработку и компиляцию текущего элемента DOM, включая все вложенные элементы. Так же игнорируется выполнение последующих операторов.

## **Синтаксис**

```text
ignore();
```

## Примеры использования

**Пример \#1** Запрещаем для компиляции заданный элемент DOM:

Входные данные:

```php
Array
(
    [users] => Array
        (
            [0] => Alex
            [1] => William
            [2] => Daniel
        )
)
```

Шаблон:

```markup
<h1>Пользователи</h1>
<ul data-sim="ignore(); class('myclass');">
     <li data-sim="repeat($data.users as $user); content($user.item);">
          Пользователь 1
     </li>
</ul>
```

Результат выполнения:

```markup
<h1>Пользователи</h1>
```

{% hint style="info" %}
В это примере операторы `class`, `repeat` и `content` не выполняются, а элемент DOM не отображается в скомпилированной версии шаблона.
{% endhint %}

**Пример \#2** Использования `ignore` для удаления демонстрационного контента:

Входные данные:

```php
Array
(
    [users] => Array
        (
            [0] => Alex
            [1] => William
            [2] => Daniel
        )
)
```

Шаблон:

```markup
<h1>Пользователи</h1>
<ul>
     <li data-sim="repeat($data.users as $user); content($user.item);">
          Пользователь 1
     </li>
     <li data-sim="ignore();">Пользователь 2</li>
     <li data-sim="ignore();">Пользователь 3</li>
     <li data-sim="ignore();">Пользователь 4</li>
</ul>
```

Результат выполнения:

```markup
<h1>Пользователи</h1>
<ul>
    <li>Alex</li>
    <li>William</li>
    <li>Daniel</li>
</ul>
```

