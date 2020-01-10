# Расширения

Архитектура Sim позволяет расширять набор операторов и модификаторов шаблонизатора.

Расширения представляет собой файлы с классом php расположенный в в каталоге «sim-extension».

При присвоении имен файлам и классам плагинов, необходимо придерживаться определенных правил, чтобы шаблонизатор корректно определял и мог использовать эти расширения.

Имена файлов расширения должны формироваться по следующей схеме: type\_name.sim.php

**type —** это один из следующих типов расширений:

* execute — операторы
* filter — модификаторы

**name** — наименование оператора/модификатора.

Классы, должны наследовать абстрактный класс «Code» для операторов и «Filter» для модификаторов.

Имена классов должны формироваться по следующей схеме:

**Расширение для операторов:**

```php
namespace Sim;
class type_name extends Code { … }
```

**Расширение для модификатора:**

```php
namespace Sim;
class type_name extends Filter { … }
```

## Расширение операторов

**Доступные свойства родительского объекта:**

* `$_index_controller` \(class Index\) — объект индекса шаблона
* `$_index_item` \(class IndexItem\) — объект элемента индекса шаблона
* `$_node` \(class Node\) — сегмент индекса
* `$_condition` \(string\) — условие выполнения команды
* `$_command` \(string\) — текущая выполняемая команда

Дополнительную информацию о свойствах и методах представленных классов, вы можете получить в соответствующем разделе документации — «API Sim»

**Минимальная структура для класса описывающего оператор:**

```php
namespace Sim;
class execute_myoperator extends Code {
    protected function initialize($command){
        ...    
        return true;
    }
    protected function commandExecution(){
       ...   
    }
    protected function conditionExecution($condition){
        ...   
    }
}
```

`initialize` — Инициализация оператора. В параметре `command`, будет передана строка с командой для дальнейшего валидации и парсинга. Функция должна вернуть булевое значение, где «**true**» — валидная команда и можно продолжать обработку, «**false**» — невалидная команда, обработка должна быть остановлена и возвращена ошибка.

`commandExecution` — Метод для формирования php-кода, если оператор выполняется без предстоящего условия.

`conditionExecution` — Метод для формирования php-кода, если оператор выполняется с предстоящим условием. Например: `if($myvar ? content($myvar));` В параметре `condition`, будет передан строка с валидным php-выражением условия.

**Пример реализации**

Для примера создадим новый оператор `print`, принцип работы которого, будет аналогичен php-функции `print_r`.

Утверждаем синтаксис оператора: `print(expression)`. Где `expression` может быть любым валидным выражением Sim.

```php
namespace Sim;
class execute_print extends Code {

    //Переменная будет содержать исходное
    //представление переданного выражения
    protected $_source_varible;

    //Переменная будет содержать скомпилированное представление
    //переданного выражения
    protected $_variable;

    //Инициализируем обработчик
    protected function initialize($command){
        //Валидируем строку с командой
        if (preg_match('/\s*(print)\s*\((.*?)\)\s*$/is',$command,$command_content)){
            //Записываем исходное представление выражениея
            $this->_source_varible = trim($command_content['2']);
            //Выполняем компиляцию выражения и записываем в переменную
            $this->_variable = $this->concat($this->_source_varible);
        } else return false; //Если строка команды не валидна
        return true; //Если строка команды валидна
    }

    //Обработчик, для формирования модели вывода
    protected function codeGenerate(){
        $code = '
            echo \''.str_replace('\'','\\\'',$this->_source_varible).' : \' ;
            echo \'<pre>\';
            print_r('.$this->_variable.');
            echo \'</pre>\';
        ';
        return $code;
    }

    //Обработчик команды без предстоящего условия
    protected function commandExecution(){
        //Вставка php-кода, перед DOM-элементом, в котором была вызвана команда
        $this->_node->before('<?'.$this->codeGenerate().'?>');
    }

    //Обработчик команды с предстоящим условием
    protected function conditionExecution($condition){
        //Вставка php-кода, перед DOM-элементом, в котором была вызвана команда
        $this->_node->before('<? if ('.$condition.'): '.$this->codeGenerate().' endif; ?>');
    }
}
```

**Тестируем работу оператора в шаблоне:**

Входные данные:

```php
Array
(
    ['myvar'] => Array
        (
            [0] => 1
            [1] => 2
            [2] => 3
            [3] => 4
            [4] => 5
        )

)
```

Шаблон:

```markup
<p data-sim="print($data.myvar);"> … </p>
```

Результат выполнения:

```markup
$data.myvar : 
<pre>
Array
(
    [0] => 1
    [1] => 2
    [2] => 3
    [3] => 4
    [4] => 5
)
</pre>
<p> … </p>
```

{% hint style="info" %}
Другие примеры реализации операторов вы можете найти в  
исходном коде — Sim.php
{% endhint %}

## Расширение модификаторов

Минимальная структура для класса описывающего модификатор:

```php
namespace Sim;

class filter_mymodifier extends Filter {
    public function initialize($var, $params){
       …
       return 'php string';
    }
}
```

`initialize` — Инициализация фильтра. Метод принимает два параметра:

1. `var` — php-представление переменной для которой необходимо применить фильтр
2. `params` — массив параметров фильтра. Каждый элемент массива является валидным php-выражением.

**Пример реализации**

Для примера создадим новый модификатор `replace`, принцип работы которого, будет аналогичен php-функции `str_replace`.

Утверждаем синтаксис модификатора: `$var:replace{needle|replace}`. Где параметры модификатора `needle` и `replace` могут быть любыми валидными выражениями Sim.

```php
namespace Sim;

class filter_replace extends Filter{

    //Формируем функцию—обработчик для фильтра
    public static function replace($needle, $replace, $subject){
        if (empty($subject) or empty($needle) or !is_string($subject)) return $subject;
        if (!is_array($needle) and !is_string($needle)) $needle = (string) $needle;
        if (!is_array($replace) and !is_string($replace)) $replace = (string) $replace;
        return str_replace($needle, $replace, $subject);
    }

    //Инициализируем обработчик
    public function initialize($var, array $params){
        //Возвращаем php-код с вызовом функции обработчика
        return '\\'.__NAMESPACE__.'\filter_replace::replace('.$params[0].', '.$params[1].', '.$var.')';
    }
}
```

**Тестируем работу модификатора в шаблоне:**

Входные данные:

```php
Array
(
    ['needle'] => Array
        (
            [0] => 1
            [1] => 2
            [2] => 3
            [3] => 4
            [4] => 5
            [5] => 6
            [6] => 7
            [7] => 8
            [8] => 9
        )

    ['subject'] => 'My phone number — +79634548888'
)
```

Шаблон:

```markup
<p data-sim="content($data.subject:replace{$data.needle|'*'});">
    … 
</p>
```

Результат выполнения:

```markup
<p>
    My phone number — +***********
</p>
```

{% hint style="info" %}
Другие примеры реализации модификаторов вы можете найти в исходном коде — Sim.php
{% endhint %}

