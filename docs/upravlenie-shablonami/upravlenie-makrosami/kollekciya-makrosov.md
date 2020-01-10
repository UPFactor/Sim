# Коллекция макросов

### add

Добавляет макрос в коллекцию.

**Синтаксис:**

```text
add(string $name, string $template, array $configuration) Macros
```

`$name` — имя/идентификатор макроса

`$template_source` — шаблон или путь до шаблона

`$configuration` — параметры шаблонизации \(необязательный\)

**Пример использования:**

```php
$Sim->macros->add('nocontent','/home/public_html/templates/nocontent.html');
$Sim->macros->add('error','<div>Error name</div>', array(
     'RootURL' => '/templates_nocontent/',
     'RootPath' => '/home/public_html/templates_nocontent/',
     'CachePath' => '/home/public_html/cache/'
));
```

####  <a id="headline-81"></a>

### get

Возвращает из коллекции объект макроса по имени/идентификатору.

**Синтаксис:**

```text
get(string $name) Macro
```

`$name` — имя/идентификатор макроса

**Пример использования:**

```php
$Sim->macros->add('nocontent','/home/public_html/templates/nocontent.html');
$nocontent_macro = $Sim->macros->get('nocontent');
```

####  <a id="headline-82"></a>

### remove

Удаляет из коллекции объект макроса с заданным именем/идентификатором.

**Синтаксис:**

```text
remove(string $name) Macros
```

`$name` — имя/идентификатор макроса

**Пример использования:**

```php
$Sim->macros->remove('nocontent');
```

####  <a id="headline-83"></a>

### getList

Возвращает массив объектов из коллекции макросов.

**Синтаксис:**

```text
getList() array
```

**Пример использования:**

```php
$Sim->macros->getList();
```

####  <a id="headline-84"></a>

### isEmpty

Возвращает `true`, если коллекция макросов пуста, иначе `false`.

**Синтаксис:**

```text
isEmpty() boolean
```

**Пример использования:**

```php
$Sim->macros->isEmpty();
```



