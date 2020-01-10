# empty

Проверяет переменную на пустоту. Вернет `true`, если переменная пустая и `false`если переменная не пустая

### **Синтаксис**

```text
boolean $var:empty;
```

\*\*\*\*

### **Пример использования**

Входные данные:

```php
Array
(
    ['users'] => Array
        (
            [0] => 'Alex'
            [1] => 'William'
            [2] => 'Daniel'
        )
    ['moderators'] => Array()
)
```

Шаблон:

```markup
<h2>Пользователи</h2>
<div data-sim="if($data.users:empty ? content('User not found'));">
     <p data-sim="repeat($data.users as $user); content($user.item);"> ... </p>
</div>
    
<h2>Модераторы</h2>
<div data-sim="if($data.moderators:empty ? content('User not found'));">
     <p data-sim="
         repeat($data.moderators as $moderator); 
         content($moderator.item);"> 
         ... 
     </p>
</div>
```

Результат выполнения:

```markup
<h2>Пользователи</h2>
<div>         
    <p>Alex</p>
    <p>William</p>
    <p>Daniel</p>    
</div>
    
<h2>Модераторы</h2>
<div>User not found</div>​
```

