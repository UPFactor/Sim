<?php
namespace Sim;

class Filter_replace extends Filter{

    //Формируем функцию—обработчик для фильтра
    public static function replace($needle, $replace, $subject){
        if (empty($subject) or empty($needle) or !is_string($subject)) return $subject;
        if (!is_array($needle) and !is_string($needle)) $needle = (string) $needle;
        if (!is_array($replace) and !is_string($replace)) $replace = (string) $replace;
        return str_replace($needle, $replace, $subject);
    }

    //Инициализируем обработчик
    public function initialize($var = '', array $params){
        //Возвращаем php-код с вызовом функции обработчика
        return '\\'.__NAMESPACE__.'\Filter_replace::replace('.$params[0].', '.$params[1].', '.$var.')';
    }
}