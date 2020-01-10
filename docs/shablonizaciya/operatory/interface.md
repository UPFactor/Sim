# interface

Оператор позволяет установить ожидаемую шаблоном структуру данных. Интерфейс может быть определен в любом месте шаблона.

## **Синтаксис**

```text
interface(array $var [~exception command]);
```

`$var` — многомерный массив определяющий структуру ожидаемых данных $data

`~exception` — оператор, который должен быть выполнен в случае исключения

{% hint style="info" %}
Если структура входных данных не соответствует интерфейсу, то будет вызвано исключение `~exception` \(если оно указано\) или возвращена ошибка.
{% endhint %}

## Примеры использования

**Пример \#1** Вызов интерфейса для блока реализующий список пользователей:

Входные данные:

```php
Array
(
    [headline] => mypage headline
    [user] => Array
        (
            [name] => Alex
            [status] => admin
            [phone] => +71234567890
            [mail] => alex@mail.com
        )
)
```

Шаблон:

```markup
<div data-sim="
  interface(['user'=>['name','status','phone','mail'], 'headline']);"> 
    ...
</div>​
```

В данном примере ожидается что массив входных данных `$data`, должен иметь следующую структуру:

```php
array(
     'headline' => …    
     'user'=>array(
          'name' => …
          'status' => …
          'phone' => …
          'mail' => …
     )
);
```

Стоит обратить внимание, что порядок элементов во входном массиве не имеет значения при сопоставлении с интерфейсом шаблона.

**Пример \#2** Вызов интерфейса для страницы, с обработкой исключения:

Входные данные:

```php
Array
(
    [title] => mypage
    [description] => mypage description
)
```

Шаблон:

```markup
<div data-sim="
   interface(['title','description','headline'] 
   ~exception content('data incorrect'));"> 
    ...
</div>​
```

Результат выполнения:

```markup
<div>data incorrect</div>
```

