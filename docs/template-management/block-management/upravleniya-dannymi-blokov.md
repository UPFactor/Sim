# Управления данными блоков

## add

Объединяет текущие данные блока с переданным массивом.

**Синтаксис:**

```text
add(string $data) DataBlock
```

`$data` — массив данных

**Пример использования:**

```php
$Sim = new \SimTemplate\Sim();

$Sim->data->block('my_block')->add(array(
    'title' => 'Employee phone numbers',
));

$Sim->data->block('my_block')->add(array(
     'people' => array(
          array('name' => 'Alex', 'phone' => '+12132775504'),
          array('name' => 'William', 'phone' => '+12132102192'),
          array('name' => 'Daniel', 'phone' => '+12136402431')
     )
));

$Sim->execute('my_template_file.html');
```

## set

Устанавливает массив данных для блока. Если блок уже содержал какие-либо данные, то они будут заменены.

**Синтаксис:**

```text
set(string $data) DataBlock
```

`$data` — массив данных

**Пример использования:**

```php
$Sim = new \SimTemplate\Sim();

$Sim->data->block('my_block')->set(array(
     'people' => array(
          array('name' => 'Alex', 'phone' => '+12132775504'),
          array('name' => 'William', 'phone' => '+12132102192'),
          array('name' => 'Daniel', 'phone' => '+12136402431')
     )
));

$Sim->execute('my_template_file.html');
```

## get

Возвращает установленный массив данных блока.

**Синтаксис:**

```text
get() array
```

**Пример использования:**

```php
$Sim = new \SimTemplate\Sim();

$Sim->data->block('my_block')->set(array(
     'people' => array(
          array('name' => 'Alex', 'phone' => '+12132775504'),
          array('name' => 'William', 'phone' => '+12132102192'),
          array('name' => 'Daniel', 'phone' => '+12136402431')
     )
));

print_r($Sim->data->block('my_block')->get());
```

