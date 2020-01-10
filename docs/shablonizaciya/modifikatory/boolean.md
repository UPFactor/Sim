# boolean

Преобразует тип переменной в boolean.

### **Синтаксис**

```text
mixed $var:boolean;
```

\*\*\*\*

### **Пример использования**

Входные данные:

```php
Array
(
    ['online'] => 'yes'
)
```

Шаблон:

```markup
<p data-sim="if($data.online:boolean ? content('online'));"> 
  offline 
</p>
```

Результат выполнения:

```markup
<p>online</p>
```

