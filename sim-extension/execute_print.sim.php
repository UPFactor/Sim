<?php
namespace SimTemplate;

class execute_print extends Code{

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
        //Вставляем перед DOM-элементом, в котором была вызвана команда, код вывода
        $this->_node->before('<?'.$this->codeGenerate().'?>');
    }

    //Обработчик команды с предстоящим условием
    protected function conditionExecution($condition){
        //Вставляем перед DOM-элементом, в котором была вызвана команда, код вывода
        $this->_node->before('<? if ('.$condition.'): '.$this->codeGenerate().' endif; ?>');
    }
}