# Управления данными шаблонизации

## add

Объединяет текущие данные шаблона с переданным массивом

**Синтаксис:**

```text
add(string $data) SimData
```

`$data` — массив данных для шаблонизации

**Пример использования:**

```php
$Sim = new \SimTemplate\Sim();

$Sim->data->add(array(
    'title' => 'Employee phone numbers',
));

$Sim->data->add(array(
     'people' => array(
          array('name' => 'Alex', 'phone' => '+12132775504'),
          array('name' => 'William', 'phone' => '+12132102192'),
          array('name' => 'Daniel', 'phone' => '+12136402431')
     )
));

$Sim->execute('my_template_file.html');
```

## set

Устанавливает массив данных. Если объект уже содержал какие-либо данные, то они будут заменены.

**Синтаксис:**

```text
set(string $data) SimData
```

`$data` — массив данных для шаблонизации

**Пример использования:**

```php
$Sim = new \SimTemplate\Sim();

$Sim->data->set(array(
     'people' => array(
          array('name' => 'Alex', 'phone' => '+12132775504'),
          array('name' => 'William', 'phone' => '+12132102192'),
          array('name' => 'Daniel', 'phone' => '+12136402431')
     )
));

$Sim->execute('my_template_file.html');
```

## get

Возвращает установленный массив данных шаблонизатора.

**Синтаксис:**

```text
get() array
```

**Пример использования:**

```php
$Sim = new \SimTemplate\Sim();

$Sim->data->set(array(
     'people' => array(
          array('name' => 'Alex', 'phone' => '+12132775504'),
          array('name' => 'William', 'phone' => '+12132102192'),
          array('name' => 'Daniel', 'phone' => '+12136402431')
     )
));

print_r($Sim->data->get());
```

