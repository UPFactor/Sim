# if

Выполняет переданную команду если выполнено условие

### **Синтаксис**

```text
if (condition ? command);
```

`condition` — логическое выражение

`command` — оператор, который необходимо выполнить если `condition = true`

**Операторы сравнения:**

* &lt;       : `LT` \(less than\)
* &gt;       : `GT` \(greater than\)
* &lt;=    : `LE` \(less or equal\)
* &gt;=    : `GE` \(greater or equal\)
* ==    : `EQ` \(equal\)
* !=     : `NE` \(not equal\)
* &&   : `AND`
* \|\|       : `OR`
* !        : `NOT`

{% hint style="danger" %}
В блоке `command` недопустимо использование команды `if`
{% endhint %}



### Примеры использования

**Пример \#1** Проверка выражения:

Входные данные:

```php
Array
(
    [online] => true
)
```

Шаблон:

```markup
<p data-sim="if ($data.online EQ true ? content('online'));">
     offline
</p>
```

Результат выполнения:

```markup
<p>online</p>
```



**Пример \#2** Проверка на пустоту массива:

Входные данные:

```php
Array
(
    [online] => array()
)
```

Шаблон:

```markup
<div data-sim="if (NOT $data.users ? content('user not found'));">
    <ul>
        <li data-sim="repeat($data.users as $user); content($user.item);">
             Пользователь 1
        </li>
    </ul>
</div>
```

Результат выполнения:

```markup
<div>user not found</div>​
```

