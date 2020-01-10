# import

Выполняет импорт всех макросов из указанного шаблона в текущую область имен.

### **Синтаксис**

```text
import($template [~prefix $var]);
```

`$template` — путь до шаблона или исходный код шаблона

`~prefix` — параметр устанавливает префикс для имен всех импортируемых макросов из шаблона \(необязательный\).



### Примеры использования

**Пример \#1** Импорт полей формы из шаблона с соответствующей коллекцией макросов:

Шаблон с коррекцией макросов «include\_macro.html»:

```markup
<!--macro:input-->
<input type="text" name="name" value="value" data-sim="
    attr('name', $data.name); 
    attr('type', $data.type); 
    attr('value', $data.value);
" />
<!--end:input-->

<!--macro:textarea-->
<textarea name="name" data-sim="attr('name', $data.name); content($data.value);">
   …
</textarea>
<!--end:textarea-->
```

Шаблон:

```markup
<section data-sim="import(/templates/include_macro.html');">
    <h1>This is form</h1>
    <div data-sim="usemacro('input', ['name' => 'username', 'type' => 'text']);">
        ...
    </div>
    <div data-sim="usemacro('input', ['name' => 'pass', 'type' => 'password']);">
        ...
    </div>
    <div data-sim="usemacro('textarea', ['name' => 'comment']);">
        ...
    </div>
</section>
```

Результат выполнения:

```markup
<section>
    <h1>This is form</h1>
    <div>
        <input name='username' type='text' value='' />
    </div>
    <div>
        <input name='password' type='password' value='' />
    </div>
    <div>
        <textarea name='comment'></textarea>
    </div>
</section>
```



**Пример \#2** Импорт полей формы из шаблона с соответствующей коллекцией макросов с использованием префикса:

Шаблон:

```markup
<section data-sim="import(/templates/include_macro.html' ~prefix 'form-');">
    <h1>This is form</h1>
    <div data-sim="usemacro('form-input', ['name' => 'username', 'type' => 'text']);">
        ...
    </div>
    <div data-sim="usemacro('form-input', ['name' => 'pass', 'type' => 'password']);">
        ...
    </div>
    <div data-sim="usemacro('form-textarea', ['name' => 'comment']);">
        ...
    </div>
</section>
```

