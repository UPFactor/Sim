# Конфигурация по умолчанию для коллекции макросов

Для коллекции макросов, так же как и для базового класса шаблонизатора, есть возможность определить параметры обработки шаблона. Они будут применены по умолчанию для каждого элемента коллекции. Если параметры не указаны, то используются параметры шаблонизатора.

### onDebug

Включает/отключает сервисный режим для отладки.

**Синтаксис:**

```text
onDebug(boolean $debug, boolean $reset)
```

`$debag` — включить/выключить сервисный режим \(по умолчанию true\)

`$reset` — если **false**, параметр будет применен по умолчанию к последующим макросам добавленным в коллекцию. **true** — значение будет переустановлено для всех элементов коллекции. \(по умолчанию **true**\)

**Пример использования:**

```php
$Sim->macros->onDebug(true);
```

####  <a id="headline-87"></a>

### setDefaultRootURL

Задает корень для создания абсолютных ссылок внутри шаблона. Используется для преобразование всех ссылок внутри шаблона по принципу:

`<img src="img.jpg" />` **→** `<img src="[http://domen.com/subdir/]img.jpg" />`

Где `http://domen.com/subdir/` является заданным корневым URL

**Синтаксис:**

```text
setDefaultRootURL(string $url, boolean $reset) Macros
```

`$url` — корень ссылки

`$reset` — если **false**, параметр будет применен по умолчанию к последующим макросам добавленным в коллекцию. **true** — значение будет переустановлено для всех элементов коллекции. \(по умолчанию **true**\)

**Пример использования:**

```php
$Sim->macros->setDefaultRootURL('/templates/');
```

####  <a id="headline-88"></a>

### getDefaultRootURL

Возвращает корень для создания абсолютных ссылок внутри.

**Синтаксис:**

```text
getDefaultRootURL() string
```

**Пример использования:**

```php
$RootURL = $Sim->macros->getDefaultRootURL();
```

####  <a id="headline-89"></a>

### setDefaultRootPath

Устанавливает путь до корневой директории с шаблонами. Если указан данные параметр то путь к шаблону необходимо задавать относительно него.

**Синтаксис:**

```text
setDefaultRootPath(string $path, boolean $reset) Macros
```

`$path` — абсолютный путь до корневой директории с шаблонами

`$reset` — если **false**, параметр будет применен по умолчанию к последующим макросам добавленным в коллекцию. **true** — значение будет переустановлено для всех элементов коллекции. \(по умолчанию **true**\)

**Пример использования:**

```php
$Sim->macros->setDefaultRootPath('/home/public_html/templates/');
```

####  <a id="headline-90"></a>

### getDefaultRootPath

Возвращает путь до корневой директории с шаблонами.

**Синтаксис:**

```text
getDefaultRootPath() string
```

**Пример использования:**

```php
$RootPath = $Sim->macros->getDefaultRootPath();
```

####  <a id="headline-91"></a>

### setDefaultCachePath

Устанавливает путь до директории хранения кеш-файлов

**Синтаксис:**

```text
setDefaultCachePath(string $path, boolean $reset) Macros
```

`$path` — абсолютный путь до корневой директории с шаблонами

`$reset` — если **false**, параметр будет применен по умолчанию к последующим макросам добавленным в коллекцию. **true** — значение будет переустановлено для всех элементов коллекции. \(по умолчанию **true**\)

**Пример использования:**

```php
$Sim->macros->setDefaultCachePath('/home/public_html/cache/');
```

####  <a id="headline-92"></a>

### getDefaultCachePath

Возвращает путь до директории хранения кеш-файлов.

**Синтаксис:**

```text
getDefaultCachePath() string
```

**Пример использования:**

```php
$CachePath = $Sim->macros->getDefaultCachePath();
```

####  <a id="headline-93"></a>

### setDefaultConfiguration

Установка всех параметров шаблонизации.

**Синтаксис:**

```text
setDefaultConfiguration(array $configurations, boolean $reset) Macros
```

`$configurations` — массив с конфигурациями макроса:

* `RootURL` — Корень для автозамены относительных ссылок используемых в шаблоне
* `RootPath` — Полный путь до директории с шаблонами
* `CachePath` — Полный путь до директории для хранения кеш-файлов

`$reset` — если **false**, параметр будет применен по умолчанию к последующим макросам добавленным в коллекцию. **true** — значение будет переустановлено для всех элементов коллекции. \(по умолчанию **true**\)

**Пример использования:**

```php
$Sim->macros->setDefaultConfiguration(array(
     'RootURL' => '/templates/',
     'RootPath' => '/home/public_html/templates/',
     'CachePath' => '/home/public_html/cache/'
));
```

####  <a id="headline-94"></a>

### getDefaultConfiguration

Возвращает массив параметров шаблонизации.

**Синтаксис:**

```text
getDefaultConfiguration() string
```

**Пример использования:**

```php
$Configuration = $Sim->macros->getDefaultConfiguration();
```



