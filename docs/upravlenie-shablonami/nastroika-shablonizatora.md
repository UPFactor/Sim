# Настройка шаблонизатора

Для коллекции макросов, так же как и для базового класса шаблонизатора, есть возможность определить параметры обработки шаблона. Они будут применены по умолчанию для каждого элемента коллекции. Если параметры не указаны, то используются параметры шаблонизатора.

### onDebug

Включает/отключает сервисный режим для отладки.

**Синтаксис:**

```text
onDebug(boolean $debug)
```

`$debag` — включить/выключить сервисный режим \(по умолчанию **true**\)

**Пример использования:**

```php
$Sim->onDebug(true);
```

####  <a id="headline-65"></a>

### getDebugStatus

Возвращает текущее состояние \(включен/выключен\) сервисного режима отладки.

**Синтаксис:**

```text
getDebugStatus() boolean
```

**Пример использования:**

```php
$Sim->getDebugStatus();
```

####  <a id="headline-66"></a>

### resetCache

Удаляет все файлы кеша из соответствующей директории. Директория файла кеша задается настройками шаблонизатора `setCachePath`.

Так же метод имеет возможность удалить кеш-файлы из любой другой директории. Для этого необходимо передать ее полный путь в параметре `$cache_path`.

При очистки директории, будут удалены только файлы с расширением «.sim\*»

**Синтаксис:**

```text
resetCache(string $cache_path)
```

`$cache_path` — директория для поиска и удаления кеш-файлов шаблонизатора

**Пример использования:**

```php
$Sim->resetCache('/cache/');
```

####  <a id="headline-67"></a>

### setRootURL

Задает корень для создания абсолютных ссылок внутри шаблона. Используется для преобразование всех ссылок внутри шаблона по принципу:

`<img src="img.jpg" />` **→** `<img src="http://domen.com/subdir/]img.jpg" />`

Где `http://domen.com/subdir/` является заданным корневым URL

**Синтаксис:**

```text
setRootURL(string $url)
```

`$url` — корень для абсолютной ссылки внутри шаблона

**Пример использования:**

```php
$Sim->setRootURL('/templates/');
```

####  <a id="headline-68"></a>

### getRootURL

Возвращает корень для создания абсолютных ссылок внутри шаблона.

**Синтаксис:**

```text
getRootURL() string
```

**Пример использования:**

```php
$RootURL = $Sim->getRootURL();
```

####  <a id="headline-69"></a>

### setRootPath

Устанавливает путь до корневой директории с шаблонами. Если указан данные параметр то путь к шаблону необходимо задавать относительно него.

**Синтаксис:**

```text
setRootPath(string $path)
```

`$path` — абсолютный путь до корневой директории с файлами шаблонов

**Пример использования:**

```php
$Sim->setRootPath('/home/public_html/templates/');
```

####  <a id="headline-70"></a>

### getRootPath

Возвращает путь до корневой директории с шаблонами.

**Синтаксис:**

```text
getRootPath() string
```

**Пример использования:**

```php
$RootPath = $Sim->getRootPath();
```

####  <a id="headline-71"></a>

### setCachePath

Устанавливает путь до директории хранения кеш-файлов.

**Синтаксис:**

```text
setCachePath(string $path)
```

`$path` — абсолютный путь до директории хранения кеш-файлов

**Пример использования:**

```php
$Sim->setCachePath('/home/public_html/cache/');
```

####  <a id="headline-72"></a>

### getCachePath

Возвращает путь до директории хранения кеш-файлов.

**Синтаксис:**

```text
getCachePath() string
```

**Пример использования:**

```php
$CachePath = $Sim->getCachePath();
```

####  <a id="headline-73"></a>

### setConfiguration

Установка всех параметров шаблонизации

**Синтаксис:**

```text
setConfiguration(array $configurations)
```

Массив `$configurations` может содержать следующие значения:

* `RootURL` — Корень для автозамены относительных ссылок используемых в шаблоне
* `RootPath` — Полный путь до директории с шаблонами
* `CachePath` — Полный путь до директории для хранения кеш-файлов

**Пример использования:**

```php
$Sim->setConfiguration(array(
     RootURL => '/templates/',
     RootPath => '/home/public_html/templates/',
     CachePath => '/home/public_html/cache/'
));
```

####  <a id="headline-74"></a>

### getConfiguration

Возвращает массив параметров шаблонизации.

**Синтаксис:**

```text
getConfiguration() array
```

**Пример использования:**

```php
$Configuration = $Sim->getConfiguration();
```
