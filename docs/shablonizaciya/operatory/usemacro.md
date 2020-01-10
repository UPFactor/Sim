# usemacro

Этот оператор вызывает макрос и включает результат его выполнения в текущий элемент DOM. Макросы применимы для многократно HTML фрагментов с целью избежать постоянных повторений.

## **Синтаксис**

```text
usemacro($name, $data);
```

`$name` — Имя макроса

`$data` — Массив данных для передачи в макрос

{% hint style="info" %}
Макросы задаются до выполнения процесса шаблонизации и доступны для любого блока шаблона.  
Макросы не имеют доступа к глобальным переменным, но могут принимать значения из шаблона.
{% endhint %}

## Примеры использования

**Пример \#1** Вывод блока «Информация не найдена»:

Шаблон макроса:

```markup
<div data-sim="interface(['title','description']);">
    <p data-sim="content($data.title)">
        ...
    </p>
    <p data-sim="content($data.description)">
        ...
    </p>
</div>
```

Входные данные:

```markup
Array
(
    [users] => Array()
)
```

Шаблон:

```markup
<section data-sim="if($data.users:empty ? usemacro('nocontent',[title=>'Error!', description=>'Users not found']))">
     <div data-sim="repeat($users as $user); content($user.item);">
          ...
     </div>
</section>
```

Результат выполнения:

```markup
<section>
    <div >
        <p >Error!</p>
        <p >Users not found</p>
    </div>
</section>​
```

При использовании оператора `usemacro`, название макроса и его значения можно передать через переменные.

Шаблон с использованием переменных \(`$data.mname` — содержит имя макроса, `$data.mdata` — содержит массив со значениями\):

```text
<section data-sim="if($data.users:empty ? usemacro($data.mname, $data.mdata)">
     <div data-sim="repeat($users as $user); content($user.item);">
          ...
     </div>
</section>​
```

