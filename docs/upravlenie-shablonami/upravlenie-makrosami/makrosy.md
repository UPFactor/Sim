# Определение макроса

По принципу действия «Макрос» сравним с функцией в регулярных языках программирования и применим для многократного использования HTML фрагментов с целью избежать повторений кода.

Как и в любой функции область видимости переменных внутри макроса, ограничена его контекстом.

Макросы могут быть объявлены двумя способами:

1. При инициализации объекта шаблонизатора с помощью свойства «macros»
2. В шаблоне с помощью специальной разметки

## Пример инициализации макроса с помощью свойства «macros» <a id="headline-60"></a>

```php
$Sim = new \SimTemplate\Sim();
$Sim->macros->add('nocontent', '/public/templates/nocontent.html');
$Sim->execute('/public/templates/mypage.html', $array);
```

Теперь макрос «nocontent», доступен для вызова из шаблона с помощью оператора `usemacro`

```markup
<div data-sim="usemacro('nocontent',['title'=>'Error!', 'description'=>'Users not found'])">
     ...
</div>
```

## Пример инициализации макроса в шаблоне с помощью специальной разметки <a id="headline-61"></a>

Шаблон:

```markup
<html>
...

<!--macro:nocontent-->
<div>
     <b data-sim="content($data.title)">header</b>
     <p data-sim="content($data.description)">message</p>
</div>
<!--end:nocontent-->

<div data-sim="usemacro('nocontent',[title=>'Error!', description=>'Users not found'])">
     ...
</div>

…
</html>
```

Результат выполнения:

```markup
<html>
...
<div>
     <div>
          <b>Error!</b>
          <p>Users not found</p>
     </div>    
</div>
…
</html>
```

