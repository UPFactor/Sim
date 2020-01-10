# Коллекция блоков

### block

Возвращает объект блока из коллекции по ID. Если блок с заданным ID отсутствует и флаг `check_name = false`, то он будет создан и добавлен в коллекцию. Если флаг `check_name = true`, то при попытке получить несуществующий блок, будет возвращена ошибка.

**Синтаксис:**

```text
block(string $id, boolean $check_name) DataBlock
```

`$id` — ID блока данных

`$check_id` — флаг указывающий на необходимость проверки существования блока с указанным ID \(по умолчанию **false**\)

**Пример \#1** Без обработки ошибки:

```php
$Sim->data->block('my_block');
```

**Пример \#2** С обработкой ошибки:

```php
try {
     $Sim->data->block('my_block', true);
} catch (Exception $e) {
     … 
}
```

####  <a id="headline-98"></a>

### blocks

Возвращает массив имен всех зарегистрированных блоков в коллекции.

**Синтаксис:**

```text
blocks() array
```

**Пример использования:**

```php
$blocks = $Sim->data->blocks();
foreach($blocks as $item){
     … 
}
```

####  <a id="headline-99"></a>

### removeBlock

Удаляет объект блока из коллекции по ID. При попытке удалить несуществующий блок, будет возвращена ошибка.

**Синтаксис:**

```text
removeBlock(string $id) Data
```

`$id` — ID блока данных

**Пример использования:**

```php
try {
     $Sim->data->removeBlock('my_block');
} catch (Exception $e) {
     … 
}
```

