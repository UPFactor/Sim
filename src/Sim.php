<?php
/**
 * @author Ivan Miroshin <ivanmiroshin@gmail.com>
 * @copyright 2017-2018 Ivan Miroshin
 */

namespace Sim;

define('SIM_VERSION', '3.0.0');

Procedure::autoload_register();

/**
 * Class Environment
 * @package Sim
 */
class Environment {

    /**
     * @var string Корневая директория среды исполнения скрипта (по умолчанию $_SERVER['DOCUMENT_ROOT'])
     */
    protected $_document_root;

    /**
     * @var string Корневой URL модификации относительных ссылок в обсолютные внутри шаблона
     */
    protected $_root_url;

    /**
     * @var string Путь до корневой дирректории с шаблонами
     */
    protected $_root_path;

    /**
     * @var string Путь до директории размещения файлов кеша
     */
    protected $_cache_path;

    /**
     * @var Macros коллекция макросов
     */
    public $macros;
    /**
     * @var Data объект управления данными
     */
    public $data;

    /**
     * @var Metrics объект для формирования метрики выполнения скрипта
     */
    public $metrics;

    /**
     * @var bool переключатель сервисного режима для отладки
     */
    private $_debug=false;

    /**
     * Environment constructor.
     *
     * Устанавливает параметры шаблонизатора. Принимает массив значений
     *  • RootURL — Корень для автозамены относительных ссылок используемых в шаблоне
     *  • RootPath — Полный путь до дирректории с шаблонами
     *  • CachePath — Полный путь до дирректории для хранения кеш-файлов
     *
     * @param array $configuration
     */
    function __construct(array $configuration = array()){

        //Запуск метрики для отслеживания процесса выполнения шаблонизации
        $this->metrics = (new Metrics())->start();

        //Установка значений по умолчанию
        $this->_document_root = Procedure::get_document_root();
        $this->_root_url = '';
        $this->_root_path = $this->_document_root;
        $this->_cache_path = __DIR__.DIRECTORY_SEPARATOR.'cache';

        //Создания объекта управления данными
        $this->data = new Data();

        //Создания объекта управления макросами
        $this->macros = new Macros(array(), $this->metrics);

        //Установка конфигураций шаблонизации
        $this->setConfiguration($configuration);
    }

    /**
     * Устанавливает параметры шаблонизатора. Принимает массив значений
     *  • RootURL — Корень для автозамены относительных ссылок используемых в шаблоне
     *  • RootPath — Полный путь до дирректории с шаблонами
     *  • CachePath — Полный путь до дирректории для хранения кеш-файлов
     *
     * @param array $configuration
     * @return $this
     * @throws Exception
     */
    public function setConfiguration(array $configuration = array()){
        if (empty($configuration)) return $this;

        if (!empty($configuration['RootURL'])) $this->setRootURL($configuration['RootURL']);
        if (!empty($configuration['RootPath'])) $this->setRootPath($configuration['RootPath']);
        if (!empty($configuration['CachePath']))$this->setCachePath($configuration['CachePath']);

        return $this;
    }

    /**
     * Возвращает массив конфигураций шаблонизатора
     *  • RootURL — Корень для автозамены относительных ссылок используемых в шаблоне
     *  • RootPath — Полный путь до дирректории с шаблонами
     *  • CachePath — Полный путь до дирректории для хранения кеш-файлов
     *
     * @return array
     */
    public function getConfiguration(){
        $arResult = array();
        $arResult['RootURL'] = $this->getRootURL();
        $arResult['RootPath'] = $this->getRootPath();
        $arResult['CachePath'] = $this->getCachePath();
        return $arResult;
    }

    /**
     * Задает корень для модификации относительных ссылок в обсолютные внутри шаблона
     * Выполняет преобразование для всей ссылок внутри шаблона по принципу:
     *  • <img src="img.jpg" /> → <img src="[http://domen.com/subdir/]img.jpg" />
     *  • <img src="img.jpg" /> → <img src="[/domen/subdir/]img.jpg" />
     *
     * Где [http://domen.com/subdir/] или [/domen/subdir/] является заданным корнем.
     *
     * @param string $url
     * @return Environment
     * @throws Exception
     */
    public function setRootURL($url){
        $url = trim($url);
        try{
            if (!preg_match('/^(?:(?:http|https):\/\/|\.?\/|\/\/)(?:[A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/$/is', $url)) {
                throw new Exception("Invalid root URL — $url ");
            }
        } catch (Exception $e) {
            $e->showError();
        }
        $this->_root_url = $url;
        $this->macros->setDefaultRootURL($url);
        return $this;
    }

    /**
     * Возвращает текущее значение RootURL
     *
     * @return string
     */
    public function getRootURL(){
        return $this->_root_url;
    }

    /**
     * Устанавливает путь до корнивой директории с шаблонами.
     * Если указан данные параметр то путь к шаблону необходимо задавать относительно него.
     *
     * @param string $path
     * @return $this
     * @throws Exception
     */
    public function setRootPath($path){
        try{
            $path = File::realpath($path);
        } catch (Exception $e) {
            $e->showError();
        }
        $this->_root_path = $path;
        $this->macros->setDefaultRootPath($path);
        return $this;
    }

    /**
     * Возвращает текущее значение RootPath
     *
     * @return string
     */
    public function getRootPath(){
        return $this->_root_path;
    }

    /**
     * Устанавливает путь до дирректории хранения кеш файлов
     *
     * @param string $cache_path Полный путь до дирректории хранения кеш файлов
     */
    public function setCachePath($cache_path){
        try{
            //Валидация пути
            if (!preg_match('/^(?:(?:[a-z]{1}\:\\'.DIRECTORY_SEPARATOR.')|\\'.DIRECTORY_SEPARATOR.')(?:[a-z0-9_\-\\'.DIRECTORY_SEPARATOR.'\. ]{0,220})$/is', $cache_path)) {
                throw new Exception("Invalid cache path — $cache_path ");
            }

            //Удаление повторяющихся разделительных символов директории
            $cache_path = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR,$cache_path);

            //Исключения разделительного символа директорий с конца строки
            if (substr($cache_path, -1) == DIRECTORY_SEPARATOR){
                $cache_path = substr($cache_path, 0, -1);
            }
        } catch (Exception $e) {
            $e->showError();
        }
        $this->_cache_path = $cache_path;
        $this->macros->setDefaultCachePath($cache_path);
    }

    /**
     * Возвращает дирректорию хранения кеш-файлов
     *
     * @return string
     */
    public function getCachePath(){
        return $this->_cache_path;
    }

    /**
     * Проверяет наличие на сервере дирректории для хранния кеша.
     * По умолчанию используется директория в которой расположен шаблонизатор, если иная не заданна
     * пользовательскими настройками
     */
    protected function checkCachePath(){
        try{
            if (empty($this->_cache_path)){
                $this->_cache_path = $this->_cache_path = __DIR__.DIRECTORY_SEPARATOR.'Cache';
            }
            if (!file_exists($this->_cache_path)){
                if(!mkdir($this->_cache_path, 0777, true)) throw new Exception("Error creating cache path — $this->_cache_path ");
            }
        } catch (Exception $e) {
            $e->showError();
        }
    }

    /**
     * Удаляет все файлы кеша из соответствующей дирректории. Дирректория файла кеша задается
     * настройками шаблонизатора.
     *
     * Так же метод имеет возможность удалить файлы кеша из любой другой дирректории.
     * Для этого необходимо передать ее полный путь в параметре $cache_path.
     *
     * При отчистики дирректории, будут удалены только файлы с расширением «.sim*»
     *
     * @param string $cache_path Дирректория из которой требуется удалить файлы кеша
     */
    public function resetCache($cache_path=''){
        $cache_path = (empty($cache_path)) ? $this->_cache_path : $cache_path;
        try{
            if (file_exists($cache_path)){
                $files = glob($cache_path.DIRECTORY_SEPARATOR."*.sim*", GLOB_BRACE);
                foreach($files as $file) unlink($file);
            }
        } catch (Exception $e) {
            $e->showError();
        }
    }

    /**
     * Включает режим отладки
     *
     * @param bool $debug
     */
    public function onDebug($debug=true){
        if ($debug === true){
            $this->_debug = true;
            $this->macros->onDebug(true);
        } else {
            $this->_debug = false;
            $this->macros->onDebug(false);
        }
    }

    /**
     * Возвращает текущий статус режима отладки (включен/выключен)
     *
     * @return bool
     */
    public function getDebugStatus(){
        return $this->_debug;
    }

    /**
     * Нормализует все ссылки шаблона (link, script, img, style url) отсносительно корневого URL
     *
     * @param string $template HTML код шаблона
     * @return string Шаблон с нормализованными ссылками
     */
    protected function linksNormaliz($template){
        if (empty($this->_root_url)) return $template;
        $template = preg_replace(array(
            '/(< *?link[^>]*href *?= *?[\"\'])((?!http)(?!ftp)(?!\/\/).*?)([\"\'][^<]*>)/is',
            '/(< *?script[^>]*src *?= *?[\"\'])((?!http)(?!ftp)(?!\/\/).*?)([\"\'][^<]*>)/is',
            '/(< *?img[^>]*src *?= *?[\"\'])((?!http)(?!ftp)(?!\/\/).*?)([\"\'][^<]*>)/is',
            '/(< *?[^>]*style *?= ?[\"\'].*?url\([\'\"]?)((?!http)(?!ftp)(?!\/\/)[0-9a-z\.\-\_\\\?\=\[\]\&]+)([\'\"]?\).*?[\"\'][^<]*>)/is',
        ), '${1}' . $this->_root_url . '${2}${3}', $template);

        return $template;
    }

    /**
     * Возвращает хеш шаблона.
     * Формирование хеша зависит от способа как был задан шаблон (путь до файла или исходник шаблона).
     * Возвращает массив:
     *  • hash — Хеш шаблона
     *  • path — Путь до шаблона. Если, шаблон передан в виде исходного кода, то вернет FALSE
     *
     * @param string $template Адрес или исходных код шаблона, для которого необходимо сформировать хеш
     * @return array
     */
    protected function getTemplateHash($template){
        if (preg_match('/^(?:[a-z0-9_\-\\'.DIRECTORY_SEPARATOR.'\. ]{0,220})(?:\.[a-z0-9_\-]{1,8})$/is', $template)) {
            if (empty($this->_root_path) or preg_match('/^' . preg_quote($this->_root_path, '/') . '/is', $template)){
                $template_file = File::realpath($template);
            } else {
                $template_file = File::realpath($this->_root_path.DIRECTORY_SEPARATOR.$template);
            }
            $template_hash = sha1_file($template_file);
        } else {
            $template_file = false;
            $template_hash = sha1($template);
        }
        return array('hash'=>$template_hash.SIM_VERSION, 'file'=>$template_file);
    }

    /**
     * Выполняет компиляцию шаблона. Возвращает массив:
     *  • path — путь к файлу с шаблоном. Может принимать значние false, если шаблон передан в виде исходника
     *  • cache — true, если шаблон востановлен из кеша, иначе false
     *  • cache_file — путь до кеш-файла шаблона
     *  • template — Скомпилированный шаблон в php-код
     *
     * @param $template
     * @return array
     */
    public function compile($template){

        //Старт метрики выполнения
        if ($this->_debug === true) $metric_id = $this->metrics->begin('compile');

        $result = array(
            'path' => false,
            'cache' => false,
            'cache_file' => '',
            'template' => ''
        );

        try{

            if (empty($template)) throw new Exception("No incoming template source");

            //Проверяем существует ли дирректория кеширования, указанная в параметрах шаблонизатора.
            //Если дирректории нет и ее невозможно создать вернет критическую ошибку
            $this->checkCachePath();

            //Формируем хеш шаблона и путь до шаблона
            $_template_hash = $this->getTemplateHash($template);
            $template_hash = $_template_hash['hash'];
            $template_file = $_template_hash['file']; //может принимать значние false, если шаблон передан в виде исходника
            unset($_template_hash);

            if ($template_file) {
                //Сохраняем информацию о шаблоне для вывода при формировании ошибки
                $result['path'] = $template_file;
                //Генерируем ошибку если файл шаблона не найден
                if (!file_exists($template_file)) throw new Exception("Unable to find real path of template '$template_file'");
            }

            //Устанавливаем root_url относительно пути к шаблону, если не задан иной
            if (!empty($template_file) and empty($this->_root_url)){
                $this->_root_url = str_replace($this->_document_root, '', dirname($template_file)).'/';
                $this->macros->setDefaultRootURL($this->_root_url, false);
            }

            //Проверяем существование файла кеша по $template_hash
            $template_cache = File::exists($this->_cache_path.DIRECTORY_SEPARATOR.$template_hash . '.simcache');

            //Если кеша нет, то выполняем индексацию и генерацию шаблона
            if ($template_cache !== false){

                //Метка — страница возвращена из кеша
                $result['cache'] = true;
                if ($this->_debug === true) $this->metrics->params('Cache', 'yes');

                //Получаем содержание кеша (скомпилированный шаблон)
                $result['template'] = $template_cache->content();

            } else {

                if ($this->_debug === true) $this->metrics->params('Cache', 'no');

                //Получаем содержание шаблона
                $template_source = ($template_file) ? (new File($template_file))->content() : $template;

                //Переопределяем все ссылки шаблона с учетом корневой дирректории
                $template_source = $this->linksNormaliz($template_source);

                //Выполняем компиляцию шаблона в php
                $index = ($this->_debug === true) ? new Index($this->metrics) : new Index();
                $result['template'] = $index->compile($template_source, $this->data->blocks());

                //Сохранить в конечный документ в файл для дальнейшего вывода
                $template_cache = File::create($this->_cache_path, $template_hash . '.simcache', $result['template']);
            }

            //Получаем путь до файла кеша
            $result['cache_file'] = $template_cache->path();
            if ($this->_debug === true) $this->metrics->params('Cache file', $result['cache_file']);

        } catch (Exception $e) {
            if ($result['path'] !== false) {
                $e->set('Template file', $result['path']);
            }
            if (!empty($result['cache_file'])) {
                $e->set('Cache file',$result['cache_file']);
            }
            if ($result['path'] === false) {
                $e->set('Template source', $template, 'html');
            }

            $e->showError();
        }

        //Фиксируем метрику выполнения
        if ($this->_debug === true) $this->metrics->end($metric_id);

        return $result;
    }

    /**
     * Выполняет компиляцию и рендеринг шаблона с учетом установленных настроек шаблонизатора.
     *
     * @param string $template — Исходный код шаблона или путь до файла шаблона относительно директории заданной в параметре «setRootPath»
     * @param array $data — Данные шаблонизации (необязательный). Переданный массив данных будет объединен с текущими данными шаблона.
     * @param bool $revert — Если установлено «true», то вывод шаблона на клиенте будет заблокирован, а метод execute вернет строку (string) с скомпилированным кодом шаблона
     * @return Environment|string
     */
    public function execute($template, array $data = array(), $revert = false){

        //Выполняем компиляцию шаблона
        $compile = $this->compile($template);
        unset($compile['template']);

        //Стартуем метрику выполнения
        if ($this->_debug === true) $metric_id = $this->metrics->begin('execute');

        $template_result = '';

        try {

            $this->data->add($data);

            //Включаем отображение всех ошибок и указываем обработчик ошибок
            $old_error_reporting = error_reporting(-1);
            set_error_handler(function ($errno, $errmsg, $filename, $linenum, $vars){
                unset($filename, $vars);
                if ($errno !== E_NOTICE){
                    throw (new Exception('Template cache file'))
                        ->set('Cause', $errmsg)
                        ->set('Line', $linenum);
                }
            });

            //callback функция для выполнения кеша шаблона
            $template_execute_code = \Closure::bind(function (array $template) {

                /**
                 * @var array $template — массив параметров шаблона:
                 *  • path — путь к файлу с шаблоном. Может принимать значние false, если шаблон передан в виде исходника
                 *  • cache — true, если шаблон востановлен из кеша, иначе false
                 *  • cache_file — путь до кеш-файла шаблона
                 *  • object — объект шаблонизатора (class Environment)
                 */

                $template['root_url'] = $template['object']->getRootURL();
                $template['root_path'] = $template['object']->getRootPath();

                /** @var Data $this */
                $data = &$this->_data;
                $block=array();
                if (!empty($this->_block)){
                    foreach ($this->_block as $k=>$data_block) {
                        /** @var DataBlock $data_block */
                        $block[$k] = &$data_block->get();
                    }
                }

                $root['data'] = &$data;
                $root['block'] = &$block;

                ob_start();
                require $template['cache_file'];
                unset (
                    $data,
                    $root['data'],
                    $root['block'],
                    $root,
                    $block,
                    $template
                );
                $result = ob_get_contents();
                ob_end_clean();

                return $result;

            }, $this->data, $this->data);

            //выполнение кеша шаблона
            $template_result = $template_execute_code(array_merge($compile, array('object' => $this)));

            //востанавливаем параметры отображения ошибок удаляет обработчик ошибок
            restore_error_handler();
            error_reporting($old_error_reporting);
            unset($old_error_reporting);

        } catch (Exception $e) {
            if ($compile['path'] !== false) {
                $e->set('Template file', $compile['path']);
            }
            if (!empty($compile['cache_file'])) {
                $e->set('Cache file',$compile['cache_file']);
            }
            if ($compile['path'] === false) {
                $e->set('Template source', $template, 'html');
            }

            $e->showError();
        }

        //Отображение информации о компиляции для редима отладки
        if ($this->_debug === true) $this->metrics->end($metric_id);

        //Определяем способ выполнения и вывода конечного документа
        if ($revert) {
            return $template_result;
        } else {
            echo $template_result;
            if ($this->_debug === true) $this->metrics->showLink();
            return $this;
        }
    }

    /**
     * Упращенная конструкция функции «execute».
     * Выполняет компиляцию и рендеринг шаблона с учетом установленных настроек шаблонизатора
     *
     * @param string $template Исходный код шаблона или путь до файла шаблона относительно директории заданной в параметре «setRootPath»
     * @return Environment
     */
    public function render($template){
        $this->execute($template);
        return $this;
    }
}

/**
 * Управление метрикой выполнения скрипта
 *
 * Class Metrics
 * @package Sim
 */
class Metrics {

    protected $_time = 0;
    protected $_memory = 0;
    protected $_params = array();
    protected $_log = array();

    /**
     * Фиксирует значения начала отсчета
     *
     * @return Metrics
     */
    public function start(){
        $this->_time = microtime(true);
        $this->_memory = memory_get_usage();
        $this->_log = array();
        return $this;
    }

    /**
     * Добавляет произвольные параметры в объект метрики.
     * Параметры несут, только информационное назначения.
     *
     * @param string $name Названи/Описание для пераметра
     * @param mixed $params Значения параметра
     * @param string $section Секция для группировки параметров
     * @return Metrics
     */
    public function params($name, $params, $section = null){
        if ($section === null or !is_string($section)){
            $this->_params['main'][] = array('name' => $name, 'params' => $params);
        } else {
            $this->_params[$section][] = array('name' => $name, 'params' => $params);
        }
        return $this;
    }

    /**
     * Вовзращает разницу текущийх значения времени и используемой памяти
     * относительно начальных значений
     *
     * @return array
     */
    protected function point(){
        $time = microtime(true) - $this->_time;
        $memory = (memory_get_usage() - $this->_memory)/1024;
        return array('time' => $time, 'memory' => $memory, 'memory_peak' => (memory_get_peak_usage(true)/1024));
    }

    /**
     * Открывает новую секцию метрики, для отдельного учета
     * времени и используемой памяти.
     *
     * @param string $description Описание секции
     * @return int — ID секции метрики
     */
    public function begin($description){

        $this->_log[] = array(
            'description' => $description,
            'begin' => array(
                'time' => microtime(true),
                'memory' => memory_get_usage()
            )
        );

        return count($this->_log)-1;
    }

    /**
     * Завершает учет секции с заданным ID.
     *
     * @param string $metric_id ID секции метрики
     * @return Metrics
     */
    public function end($metric_id){
        if (!isset($this->_log[$metric_id])) return $this;

        $this->_log[$metric_id]['end'] = array(
            'time' => microtime(true),
            'memory' => memory_get_usage()
        );
        $this->_log[$metric_id]['result'] = array(
            'time' => $this->_log[$metric_id]['end']['time'] - $this->_log[$metric_id]['begin']['time'],
            'memory' => ($this->_log[$metric_id]['end']['memory'] - $this->_log[$metric_id]['begin']['memory'])/1024
        );

        return $this;
    }

    /**
     * Возвращает текущие результаты метрики
     *
     * @return array
     */
    public function get(){
        $metrics = $this->point();
        if (!empty($this->_params)) $metrics['params'] = $this->_params;
        if (!empty($this->_params)) $metrics['log'] = $this->_log;
        return $metrics;
    }

    public function showLink(){
        $data['metric'] = $this->get();
        $data['sim'] = __DIR__.'/Sim.php';
        $data = json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
        $link = str_replace(Procedure::get_document_root(), '',__DIR__).'/Tools/metric.php';
        echo '<form action="'.$link.'" method="post" target="_blank"><button type="submit" name="data" value=\''.$data.'\'>Open metric</button> </form>';
    }
}

/**
 * Class DataTrait
 * @package Sim
 */
trait DataTrait {
    protected $_data = array();

    /**
     * DataTrait constructor.
     * @param array $data
     */
    function __construct(array $data = array()){
        $this->set($data);
    }

    /**
     * Устанавливает массив данных
     *
     * @param array $data
     * @return $this
     */
    public function set(array $data = array()){
        $this->_data = $data;
        return $this;
    }

    /**
     * Объединят данные шаблона с переданным массивом.
     *
     * @param array $data
     * @return $this
     */
    public function add(array $data = array()){
        $this->_data = array_merge($this->_data, $data);
        return $this;
    }

    /**
     * Возвращает массив данных шаблона
     *
     * @return array
     */
    public function &get(){
        return $this->_data;
    }
}

/**
 * Управление данными шаблоизации
 *
 * Class Data
 * @property array $_block — Коллекция блоков
 * @package Sim
 */
class Data{
    use DataTrait;

    protected $_block = array();

    /**
     * Возвращает объект блока из коллекции по ID.
     * Если блок с заданным ID отсутствует и флаг check_name = false, то он будет создан и добавлен в коллекцию.
     * Если флаг check_name =  true, то при попытке получить несуществующий блок, будет возвращена ошибка.
     *
     * @param $id
     * @param $check_id
     * @return DataBlock
     * @throws Exception
     */
    public function block($id, $check_id = false){
        if (!is_string($id)){
            throw new Exception("Wrong name of the block. The expected values are a string");
        }

        if (!key_exists($id, $this->_block)){
            if ($check_id) throw new Exception("Block — '$id' not found");
            $this->_block[$id] = new DataBlock();
        }

        return $this->_block[$id];
    }

    /**
     * Возвращает массив имен всех зарегистрированных блоков в коллекции
     * @return array
     */
    public function blocks(){
        return array_keys($this->_block);
    }

    /**
     * Удаляет объект блока из коллекции по ID.
     * При попытке удалить несуществующий блок, будет возвращена ошибка.
     *
     * @param $id
     * @return Data
     * @throws Exception
     */
    public function removeBlock($id){
        if (gettype($id) != 'string'){
            throw new Exception("Wrong name of the block. The expected values are a string or a number");
        }

        if (!key_exists($id, $this->_block)){
            throw new Exception("Block — '$id' not found");
        }

        unset($this->_block[$id]);

        return $this;
    }
}

/**
 * Управление данными блока
 *
 * Class DataBlock
 * @package Sim
 */
class DataBlock{
    use DataTrait;
}

/**
 * Управление коллекцией макросов
 *
 * Class Macros
 * @package Sim
 */
class Macros{

    /**
     * @var array коллекция макросов
     */
    private $_macros=array();

    /**
     * @var array конфигурация макросов в коллекции по умолчанию
     */
    private $_default_configuration = array(
        'RootURL'=>'',
        'RootPath'=>'',
        'CachePath'=>''
    );

    /**
     * @var Metrics Объект для формирования метрики выполнения скрипта
     */
    private $_metrics = null;

    /**
     * @var bool переключатель сервисного режима для отладки
     */
    private $_debug = false;

    /**
     * Macros constructor.
     * @param array $configuration
     * @param Metrics|null $metrics
     */
    function __construct(array $configuration=array(), Metrics $metrics = null){
        $this->_metrics = $metrics;
        $this->setDefaultConfiguration($configuration);
    }

    /**
     * Включает режим отладки
     * Если $reset = true, устанавливает указанные параметр для всех элементов коллекции
     *
     * @param bool $debug
     * @param bool $reset
     */
    public function onDebug($debug=true, $reset=true){
        if (!is_bool($debug)) $debug = false;
        if (!is_bool($reset)) $reset = false;
        $this->_debug = $debug;

        if (!empty($this->_macros) and $reset){
            foreach ($this->_macros as $macro){
                $macro->debug = $debug;
            }
        }
    }

    /**
     * Задает значение по умолчанию кореня абсолютных ссылок внутри шаблонов макросов.
     * Выполняет преобразование для всей ссылок внутри шаблона по принципу
     * <img src="img.jpg" /> → <img src="[http://domen.com/subdir/]img.jpg" />, где
     * [http://domen.com/subdir/] является заданным корневым URL
     * Если $reset = true, устанавливает указанные параметр для всех элементов коллекции
     *
     * @param $url
     * @param bool $reset
     * @return $this
     */
    public function setDefaultRootURL($url, $reset=true){
        if (!is_bool($reset)) $reset = false;
        $this->_default_configuration['RootURL'] = $url;

        if (!empty($this->_macros) and $reset){
            foreach ($this->_macros as $macro){
                $macro->setRootURL($url);
            }
        }

        return $this;
    }

    /**
     * Возвращает текущее значение RootURL
     *
     * @return string
     */
    public function getDefaultRootURL(){
        return $this->_default_configuration['RootURL'];
    }

    /**
     * Устанавливает по умолчанию путь до корнивой директории с шаблонами.
     * Если указан данные параметр то путь к шаблону необходимо задавать относительно него.
     * Если $reset = true, устанавливает указанные параметр для всех элементов коллекции
     *
     * @param $path
     * @param bool $reset
     * @return $this
     */
    public function setDefaultRootPath($path, $reset=true){
        if (!is_bool($reset)) $reset = false;
        $this->_default_configuration['RootPath'] = $path;

        if (!empty($this->_macros) and $reset){
            foreach ($this->_macros as $macro){
                $macro->setRootPath($path);
            }
        }

        return $this;
    }

    /**
     * Возвращает текущее значение RootPath
     *
     * @return string
     */
    public function getDefaultRootPath(){
        return $this->_default_configuration['RootPath'];
    }

    /**
     * Устанавливает по умолчанию путь до дирректории хранения кеш-файлов
     * Если $reset = true, устанавливает указанные параметр для всех элементов коллекции
     *
     * @param $cache_path
     * @param bool $reset
     * @return $this
     */
    public function setDefaultCachePath($cache_path, $reset=true){
        if (!is_bool($reset)) $reset = false;
        $this->_default_configuration['CachePath'] = $cache_path;

        if (!empty($this->_macros) and $reset){
            foreach ($this->_macros as $macro){
                $macro->setCachePath($cache_path);
            }
        }

        return $this;
    }

    /**
     * Возвращает дирректорию хранения кеш-файлов, заданную по умолчанию
     *
     * @return string
     */
    public function getDefaultCachePath(){
        return $this->_default_configuration['CachePath'];
    }

    /**
     * Устанавливает параметры коллекции макросов по умолчанию. Принимает массив значений
     *  • RootURL — Корень для автозамены относительных ссылок используемых в шаблоне
     *  • RootPath — Полный путь до дирректории с шаблонами
     *  • CachePath — Полный путь до дирректории для хранения кеш-файлов
     * Если $reset = true, устанавливает указанные параметр для всех элементов коллекции
     *
     * @param array $configuration
     * @param bool $reset
     * @return $this
     */
    public function setDefaultConfiguration(array $configuration=array(), $reset=true){
        if (empty($configuration)) return $this;
        if (!is_bool($reset)) $reset = false;

        $this->_default_configuration = array_merge($this->_default_configuration, Procedure::array_find_keys(array('RootURL','RootPath','CachePath'), $configuration, true));

        if (!empty($this->_macros) and $reset){
            foreach ($this->_macros as $macro){
                $macro->setConfiguration($this->_default_configuration);
            }
        }

        return $this;
    }

    /**
     * Возвращает массив конфигурация коллекции макросов по умолчанию
     *  • RootURL — Корень для автозамены относительных ссылок используемых в шаблоне
     *  • RootPath — Полный путь до дирректории с шаблонами
     *  • CachePath — Полный путь до дирректории для хранения кеш-файлов
     *
     * @return array
     */
    public function getDefaultConfiguration(){
        return $this->_default_configuration;
    }

    /**
     * Добавляет новый макрос в коллекцию.
     *
     * @param string $name имя макроса (ID)
     * @param string $template_source путь к шаблону или исходный код шаблона
     * @param array $configuration массив макроса
     * @return $this
     */
    public function add($name, $template_source, array $configuration=array()){

        try{
            if (empty($name)) throw new Exception("Impossible add macro without a name");
            if (!preg_match('/^[a-z0-9-_]*$/is',$name)) throw new Exception("Incorrect macro name");

            //Создаем объект макроса и добавляем в коллекцию
            $this->_macros[$name] = new Macro(
                $name,
                $template_source,
                array_merge($this->_default_configuration, $configuration)
            );

            //Передаем метрику
            $this->_macros[$name]->metrics = $this->_metrics;

            //Устанавливаем флаг режима отладки
            if ($this->_debug === true) {
                $this->_macros[$name]->debug = true;
            }

        } catch (Exception $e) {
            $e->showError();
        }

        return $this;
    }

    /**
     * Возвращает макрос из коллекции по имени
     *
     * @param $macro_name
     * @return Macro
     * @throws Exception
     */
    public function get($macro_name){

        try{
            if (empty($macro_name) or gettype($macro_name) != 'string' or !preg_match('/^[a-z0-9-_]*$/is',$macro_name)) throw new Exception("Incorrect macro name '$macro_name'");
            if (!key_exists($macro_name, $this->_macros)) throw new Exception("Macro with name '$macro_name' missing");
        } catch (Exception $e) {
            $e->showError();
        }

        return $this->_macros[$macro_name];
    }

    /**
     * Удаляет макрос из коллекции по имени
     *
     * @param $macro_name
     * @return $this
     */
    public function remove($macro_name){

        try{
            if (!key_exists($macro_name,$this->_macros)) throw new Exception("Macro with name '$macro_name' missing");
            unset($this->_macros[$macro_name]);
        } catch (Exception $e) {
            $e->showError();
        }

        return $this;
    }

    /**
     * Вернет true, если коллекция макросов пуста, инач false
     *
     * @return bool
     */
    public function isEmpty(){
        if (empty($this->_macros)) return true;
        return false;
    }

    /**
     * Массив массив макросов коллекции
     *
     * @return array
     */
    public function getList(){
        return $this->_macros;
    }
}

/**
 * Макрос
 *
 * Class Macro
 * @package Sim
 */
class Macro{

    /**
     * @var string имя макроса
     */
    private $_name = '';

    /**
     * @var string путь к шаблону или исходный код шаблона
     */
    private $_template_source;

    /**
     * @var array конфигурация макросов в коллекции по умолчанию
     */
    private $_configuration = array(
        'RootURL'=>'',
        'RootPath'=>'',
        'CachePath'=>''
    );

    /**
     * @var Metrics Объект для формирования метрики выполнения скрипта
     */
    public $metrics = null;

    /**
     * @var bool переключатель сервисного режима для отладки
     */
    public $debug = false;

    /**
     * Macro constructor.
     * @param string $name имя макроса
     * @param string $template_source путь к шаблону или исходный код шаблона
     * @param array $configuration конфигурации макроса
     * @throws Exception
     */
    final function __construct($name, $template_source, array $configuration=array()){
        if (empty($name)) throw new Exception("Impossible create macro without a name");
        if (!is_string($name)) throw new Exception("Incorrect macro name");
        if (empty($template_source)) throw new Exception("No incoming template source for create macro");
        $this->_name = $name;
        $this->_template_source = $template_source;
        $this->setConfiguration($configuration);
    }

    /**
     * Задает корень для создания обсолютных ссылок внутри шаблона.
     * Выполняет преобразование для всей ссылок внутри шаблона по принципу <img src="img.jpg" />, <img src="[http://domen.com/subdir/]img.jpg" />.
     * Где [http://domen.com/subdir/] является заданным корневым URL
     *
     * @param $url
     * @return $this
     */
    public function setRootURL($url){
        $this->_configuration['RootURL'] = $url;
        return $this;
    }

    /**
     * Возвращает текущее значение RootURL
     *
     * @return string
     */
    public function getRootURL(){
        return $this->_configuration['RootURL'];
    }

    /**
     * Устанавливает путь до корнивой директории с шаблонами.
     * Если указан данные параметр то путь к шаблону необходимо задавать относительно него.
     *
     * @param $path
     * @return $this
     */
    public function setRootPath($path){
        $this->_configuration['RootPath'] = $path;
        return $this;
    }

    /**
     * Возвращает текущее значение RootPath
     *
     * @return string
     */
    public function getRootPath(){
        return $this->_configuration['RootPath'];
    }

    /**
     * Устанавливает путь до дирректории хранения кеш файлов
     *
     * @param $path
     * @return $this
     */
    public function setCachePath($path){
        $this->_configuration['CachePath'] = $path;
        return $this;
    }

    /**
     * Возвращает дирректорию хранения кеш-файлов
     *
     * @return string
     */
    public function getCachePath(){
        return $this->_configuration['CachePath'];
    }

    /**
     * Устанавливает параметры макроса. Принимает массив значений
     *  • RootURL — Корень для автозамены относительных ссылок используемых в шаблоне
     *  • RootPath — Полный путь до дирректории с шаблонами
     *  • CachePath — Полный путь до дирректории для хранения кеш-файлов
     *
     * @param array $configuration
     * @return $this
     */
    public function setConfiguration(array $configuration=array()){
        if (empty($configuration)) return $this;

        $this->_configuration = array_merge($this->_configuration, Procedure::array_find_keys(array('RootURL','RootPath','CachePath'), $configuration, true));
        return $this;
    }

    /**
     * Возвращает массив конфигураций макроса
     *  • RootURL — Корень для автозамены относительных ссылок используемых в шаблоне
     *  • RootPath — Полный путь до дирректории с шаблонами
     *  • CachePath — Полный путь до дирректории для хранения кеш-файлов
     *
     * @return array
     */
    public function getConfiguration(){
        return $this->_configuration;
    }

    /**
     * Выполняет компиляцию макроса. Возвращает скомпилированный шаблон.
     *
     * @param array $data
     * @return string
     */
    public function execute(array $data=array()){

        //Создаем объект шаблонизатора
        $sim = new Environment($this->_configuration);
        $sim->macros->add($this->_name, $this->_template_source);
        if ($this->debug === true) $sim->onDebug(true);

        //Выполняем шаблонизацию макроса
        $result = $sim->execute($this->_template_source, $data, true);

        //Объединяем метрики
        if ($this->debug === true and $this->metrics !== null){
            $this->metrics->params($this->_name, $sim->metrics->get(), 'macros');
        }

        return $result;
    }
}

/**
 * Управление индексацией шаблона
 *
 * Class Index
 * @package Sim
 */
class Index{

    /**
     * @var array Содержание идекса
     */
    private $_indexes_collection = array();

    /**
     * @var int Счетчик количества элементов в индексе
     */
    private $_index_counter = 0;

    /**
     * @var Metrics Объект для формирования метрики выполнения скрипта
     */
    private $_metrics = null;

    /**
     * Index constructor.
     * @param Metrics|null $metrics
     */
    function __construct(Metrics $metrics = null){
        $this->_metrics = $metrics;
    }

    /**
     * Резервирует последующий ключ индекса для записи
     *
     * @return int
     */
    protected function indexRegistration(){
        $i = ++$this->_index_counter;
        $this->_indexes_collection[$i]=null;
        return $i;
    }

    /**
     * Добавляет новый индекс в коллекцию
     *
     * @param array $source
     * @return IndexItem
     */
    public function add(array $source=array()){
        $this->_indexes_collection[$source['index_id']] = new IndexItem($source);
        return $this->_indexes_collection[$source['index_id']];
    }

    /**
     * Удаляет индекс из коллекции
     *
     * @param null $index_id
     * @return Index
     * @throws Exception
     */
    public function remove($index_id=null){
        if (!empty($index_id)){
            if (!$this->exists($index_id, false)) return $this;
            $nested = $this->_indexes_collection[$index_id]->get('nested'); //Получаем все зависимости
            if(!empty($nested)){ //Если зависимости есть, удаляем соответствующие им элементы индекса
                foreach ($nested as $nested_key){
                    $this->remove($nested_key);
                }
            }
            unset($this->_indexes_collection[$index_id]); //Удаляем текущий элемент индекса
        } else {
            throw new Exception("Not enough '$index_id' attribute for remove an index ");
        }

        return $this;
    }

    /**
     * Проверяет есть ли в колекции индекса указанный ключ
     *
     * @param string|integer $index_id
     * @param bool $show_error
     * @return bool
     * @throws Exception
     */
    public function exists($index_id, $show_error = false){
        if (empty($index_id) or !array_key_exists($index_id, $this->_indexes_collection)) {
            if ($show_error) {
                throw new Exception("Index '$index_id' does not exist in the collection");
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * Получает объект элемента коллекции индекса по ID
     *
     * @param $index_id
     * @return IndexItem
     * @throws Exception
     */
    public function getByID($index_id){
        if (!empty($index_id)){
            $this->exists($index_id, true);
            return $this->_indexes_collection[$index_id];
        } else {
            throw new Exception("Not enough '$index_id' attribute for return of the index ");
        }
    }

    /**
     * Получает объект элемента коллекции индекса по ключу
     *
     * @param $key
     * @return IndexItem
     * @throws Exception
     */
    public function getByKey($key){
        if (!empty($key)){
            if (preg_match('/<\!--data-sim-index\:(\d*)-->/is',$key,$index_id)){
                $index_id = $index_id[1];
                $this->exists($index_id, true);
                return $this->getByID($index_id);
            } else {
                throw new Exception("Invalid index key format '$key' ");
            }
        } else {
            throw new Exception("Not enough 'key' attribute for return of the index ");
        }
    }

    /**
     * Возвращает коллекцию индекса
     *
     * @return array
     */
    public function getList(){
        return $this->_indexes_collection;
    }

    /**
     * Возвращает массив макросов декларированных в заданном шаблоне.
     * Каждый элемент масива содержит следующие параметры:
     *   • name — Имя макроса
     *   • content — Содержание (шаблон) макроса
     *
     * @param $template
     * @return array
     */
    public function getMacros($template){
        $arMacros = array();
        if (empty($template)) return $arMacros;

        if (preg_match_all('/\<\!--\s*macro\:([\w-]+)\s*--\>(.*?)\<\!--\s*end\:\1\s*--\>/is', $template, $match_macros)) {
            foreach ($match_macros[0] as $k => $macro_content){
                $arMacros[$k] = array(
                    'name' => trim($match_macros[1][$k]),
                    'content' => $match_macros[2][$k],
                    'macro' => $macro_content
                );
            }
        }

        return $arMacros;
    }


    /**
     * Выполняет парсинг шаблона, заполняет коллекцию индекса
     *
     * @param $template
     * @param bool $main_iteration
     * @return array
     */
    private function parsing($template, $main_iteration=true){

        //Поиск DOM-элементов с установленной семантикой шаблонизатора
        $template_ = $template;
        $blocks = array();

        //Предварительный поиск DOM-элементов, отвечающих как однострочному, так и блочному представлению
        while (preg_match('/<\s*(?!\/)([a-z]+)[^>]*data-sim\s*=\s*\"(.*?)\"[^>]*>/is', $template_, $dom_string, PREG_OFFSET_CAPTURE)){

            //Обработка блочных DOM-элементов
            if (preg_match('/<\s*([a-z0-9]+)\b[^\>]*\bdata-sim\s*=\s*\"([^\"]+)\"[^\>]*>(?>([^\<]+|<(?!\/?\s*\1\b))|(<\s*\1[^\>]*>(?:(?3)|(?4)|)+?<\/\s*\1\s*>))*<\/\s*\1\s*>/ix', $template_, $dom_block, PREG_OFFSET_CAPTURE, $dom_string[0][1])){

                $blocks[] = array(
                    'node' => $dom_block[0][0],
                    'tag' => $dom_block[1][0],
                    'commands' => $dom_block[2][0],
                );

                $template_ = Procedure::str_replace_once($dom_block[0][0],'', $template_);

            } else {

                //Если найденный DOM-элемент не отвечает блочному предоставлению, то он считается элементом
                //с однострочным представлением
                $blocks[] = array(
                    'node' => $dom_string[0][0],
                    'tag' => $dom_string[1][0],
                    'commands' => $dom_string[2][0],
                );

                $template_ = Procedure::str_replace_once($dom_string[0][0],'', $template_);

            }
        }
        unset($template_);

        $arIndexID = array();

        //Создание/компиляция индекса для найденных элементов DOM с симантикой шаблонизатора
        if (!empty($blocks)){
            foreach($blocks as $block){

                //Устанавливаем параметры элемента индекса
                $arItemParameters = array(
                    'index_id' => $this->indexRegistration(),
                    'tag' => $block['tag'],
                    'commands' => $block['commands']
                );

                //Собираем в массив все элементы индекса текущей интерации. (Будут использованны
                //как список зависимости в рекурсии метода)
                $arIndexID[] = $arItemParameters['index_id'];

                //Удаляем из DOM-элемента симантику шаблонизатора
                $block_node = preg_replace('/^\s*(<[^>]*)(data-sim\s*=\s*\"[^\"]+\"\s*)([^>]*>)/is','$1$3',$block['node']);

                //Выполняем парсинг дочерних DOM элементов
                $arParseResult = $this->parsing($block_node, false);
                $arItemParameters['node'] = $arParseResult['template'];
                $arItemParameters['nested'] = $arParseResult['index'];

                //Создаем элемент индекса
                $index_item = $this->add($arItemParameters);
                unset($arItemParameters,$block_node);

                //Если это первая итерация рекурсивной функции, то выполняем компиляцию
                //В последующих рекурсиях элементы будут зависимы от родительских, поэтому их компиляция не возможна
                if ($main_iteration){
                    $this->compileElementIndex($index_item);
                    $template = Procedure::str_replace_once($block['node'], $index_item->get('node')->getNode(), $template);
                } else {
                    //Иначе заменяем заменяем используемый сигмент на метку (ключ) индекса
                    $template = Procedure::str_replace_once($block['node'], $index_item->get('index_key'), $template);
                }
            }
        }

        return array ('template' => $template, 'index' => $arIndexID);
    }
    /**
     * Выполняеет индексацию и компиляцию шаблона в php-код
     *
     * @param $template
     * @param array $blocks
     * @return string
     * @throws Exception
     */
    public function compile($template, array $blocks = array()){
        if (empty($template)) throw new Exception("No template to merge with index");

        //Удаляем специальные комментарии
        $template = preg_replace('/<\!--#.*?#-->/is', '', $template);

        //Инициализация php-кода заданного в шаблоне
        $arPHP = array();
        if (preg_match_all('/\<\!--\?\s*(.*?)\s*\?-->/is', $template, $match_php)) {
            foreach ($match_php[0] as $k => $php_content){
                $php_replace_i = '{php'.$k.'}';
                $arPHP[$php_replace_i] = '<?'.$match_php[1][$k].'?>';
                $template = Procedure::str_replace_once($php_content, $php_replace_i, $template);
            }
        }

        //Инициализируем макросы заданные в шаблоне
        $arMacros = $this->getMacros($template);
        if (!empty($arMacros)){
            foreach ($arMacros as $macro_item){
                $template = Procedure::str_replace_once($macro_item['macro'], '', $template);
            }
        }

        //Выполняем парсинг и компиляцию семантики шалона
        $template = $this->parsing($template)['template'];

        //Выводим ошибку если после индексации в шаблоне остались операторы data-sim,
        //что является причиной некоорректной индексации
        if (preg_match('/<[^\!\/]*data\-sim[^>]+>/is', $template, $error_line)) {
            throw new Exception("Template Error indexation in line \"".htmlspecialchars(preg_replace('/<(?:php)?\?.*?\?>/is','',$error_line[0]))."\"");
        }

        //Компиляция блоков
        $template = $this->compileBlocks($template, $blocks);

        //Установка кода для инициализации макроса из шаблона (кеша)
        $macro_code = '';
        foreach ($arMacros as $macro_item){
            $macro_code.= '$template[\'object\']->macros->add(\''.$macro_item['name'].'\', \''.str_replace('\'','\\\'',$macro_item['content']).'\', array(\'RootURL\'=>\'\', \'RootPath\'=>\'\'));';
        }
        if (!empty($macro_code)) $macro_code = '<? '.$macro_code.' ?>';

        //Устновка php-кода
        foreach ($arPHP as $php_replace_i => $php_item){
            $template = str_replace($php_replace_i, $php_item, $template);
        }

        return $macro_code.$template;
    }

    /**
     * Компиляция блоков
     *
     * @param $template
     * @param array $blocks
     * @return string
     * @throws Exception
     */
    private function compileBlocks($template, array $blocks = array()){
        if (empty($template)) throw new Exception("No template to merge with blocks");
        if (empty($blocks)) return $template;

        $block_replace = array();

        foreach ($blocks as $block_name){
            if (gettype($block_name) != 'string') continue;
            if (preg_match_all('/(\<\!--\s*block\:('.$block_name.')\s*--\>)(.*?)(\<\!--\s*end\:\2\s*--\>)/is', $template, $block)){
                $block_count = count($block[2])-1; //Количество одноименных блоков на странице
                for ($i=0; $i <= $block_count; $i++){
                    $block_alias = str_replace('-', '_', $block[2][$i]);
                    $block_content = $block[1][$i].$this->compileBlocks($block[3][$i], $blocks).$block[4][$i];
                    $replace_i = $block_alias.$i;
                    $block_replace[$replace_i] = '
                        <?
                        $sim_function_block_'.$block_alias.' = function (&$data) use ($root, $block, $template) { 
                        ?>
                            '.$block_content.'
                        <?
                            unset($data);
                        };
                        $sim_function_block_'.$block_alias.'($block[\''.$block[2][$i].'\']);
                        unset($sim_function_block_'.$block_alias.');
                        ?>
                    ';
                    //Заменяем блок в шаблоне на его индекс
                    $template = Procedure::str_replace_once($block[0][$i], '{'.$replace_i.'}', $template);
                }
            }
        }

        //Восстанавливаем блоки шаблона по индексу
        foreach ($block_replace as $k => $replace){
            $template = Procedure::str_replace_once('{'.$k.'}', $replace, $template);
        }

        return $template;
    }

    /**
     * Компиляция элементов коллекции индекса
     *
     * @param IndexItem $index_item — Элемент индекса
     */
    public function compileElementIndex(IndexItem $index_item){

        //Логируем информацию о выполняемом элементе индекса
        if ($this->_metrics !== null){
            $this->_metrics->params('index item: '.$index_item->get('index_id'),'execute','command');
        }

        //Обработка команд элемента индекса
        $commands = $index_item->get('commands');
        foreach ($commands as $k=>$command){
            if ($index_item->getMark('break_execution_commands')) break;
            $index_item->setMark('current_command', $k);
            $this->compileCommandIndex($command, $index_item);
        }

        //Получаем список зависимостей
        $nested_list = $index_item->get('nested');

        //Обработка зависимых элементов индекса
        if (!empty($nested_list)){
            $nested_node = array(); //Массив куда будем складывать node зависимостей
            foreach ($nested_list as $nested_id){
                $nested_index_item = $this->getByID($nested_id); //Получаем объекта индекса для зависимости
                $this->compileElementIndex($nested_index_item); //Выполняем команды зависимости
                $nested_index_key = $nested_index_item->get('index_key');
                if (key_exists($nested_index_key,$nested_node)){ ///Получаем результирующий node зависимости и сохраняем за ее index_key
                    $nested_node[$nested_index_key].= $nested_index_item->get('node')->getNode();
                } else {
                    $nested_node[$nested_index_key] = $nested_index_item->get('node')->getNode();
                }
                //Удалаем более не нужные переменные
                unset($nested_index_item, $nested_index_key); //Удаляем копию объекта зависимости
            }
            //Заменяем в текущем объекте индекса index_key зависимостей на полученных после их выполнения node
            $index_item->get('node')->content(str_replace(array_keys($nested_node),array_values($nested_node), $index_item->get('node')->getContent()));
        }

        //Удаляем отработанный элемент индекса
        unset($this->_indexes_collection[$index_item->get('index_id')]);

        //Логируем информацию о завершении выполнения элемента индекса
        if ($this->_metrics !== null){
            $this->_metrics->params('index item: '.$index_item->get('index_id'),'done','command');
        }
    }

    /**
     * Компиляция комманд элемента индекса
     *
     * @param string $command Команда
     * @param IndexItem $index_item — Объект элемента индекса к которому принадлежит команда
     * @param bool $condition — Условие выполнения команды
     * @throws Exception
     */
    public function compileCommandIndex($command, IndexItem $index_item, $condition=false){

        //Логируем информацию о запуске выполнения команды
        if ($this->_metrics !== null){
            $this->_metrics->params('command: '.$command,'execute','command');
        }

        //Проверка общего синтаксиса команды, парсинг
        if (preg_match('/^\s*([\w]+)\s*\(.*?\)\s*$/is', $command, $matches)){
            $command_name = $matches[1];

            //Проверка наличия обработчика для команды
            $class_name = '\\'.__NAMESPACE__.'\Execute_'.$command_name;
            if (!class_exists($class_name)) throw new Exception("Method '$command_name' is not found ");

            //Логируем информацию о процессе выполнения команды
            if ($this->_metrics !== null){
                $this->_metrics->params('command: '.$command,'processing','command');
            }

            //Выполняем команду
            /**@var Code $controller**/
            $controller = new $class_name($command, $index_item, $this);
            if ($condition){
                $controller->setCondition($condition);
            }
            $controller->execute();
            unset($controller);

            //Логируем информацию о завершении выполнения команды
            if ($this->_metrics !== null){
                $this->_metrics->params('command: '.$command,'done','command');
            }

        } else {
            throw new Exception("Syntax error in data-sim '$command' ");
        }
    }
}

/**
 * Элемент индекса
 *
 * Class IndexItem
 * @package Sim
 */
class IndexItem {

    /**
     * @var array Содержание идекса
     */
    private $_index_item=array();

    /**
     * @var array Пользовательские метки для элемента индекса
     */
    private $_mark=array();

    /**
     * @var array Ожидаемые параметры для замены содержания индекса на входе
     */
    private $_expected_parameters = array('index_id','tag','node','commands','nested');
    private $_required_parameters = array('index_id','tag','node','commands');

    /**
     * IndexItem constructor.
     *
     * @param array $source
     * @throws Exception
     */
    function __construct(array $source=array()){
        //Проверяем чтобы входящий массив содержал все обязательные параметры
        if (Procedure::array_keys_exists($this->_required_parameters, $source, true)){
            //Устанавливаем массив атрибутов
            $this->set($source);
        } else {
            throw new Exception("Not enough attributes to create the index ");
        }
    }

    /**
     * Устанавливает атрибуты элемента индекса:
     *   • index_id — идентификатор
     *   • tag — HTML тег DOM элемента с которым необходимо ассоциировать элемент индекса
     *   • node — HTML строка/class Node с которым необходимо ассоцировать элемент индекса
     *   • nested — массив идентификаторов зависимых (вложеных) индексов
     *   • commands — строка с перечнем команд
     *
     * @param array $source Атрибуты для перезаписи параметров индекса
     * @return $this
     * @throws Exception
     */
    public function set(array $source=array()){
        //Фильтруем входящий массив от лишних значений
        $source = Procedure::array_find_keys($this->_expected_parameters,$source,true);

        if (array_key_exists('index_id',$source)) $source['index_key'] = '<!--data-sim-index:'.$source['index_id'].'-->';

        if (array_key_exists('node',$source)) {
            if (gettype($source['node'])=='string'){
                $source['node'] = new Node($source['node']);
            } else {
                if (gettype($source['node'])!='object' or strripos(get_class($source['node']), 'Node')===false){
                    throw new Exception("Incorrect format of the node");
                }
            }
        }

        if (!isset($source['nested'])){
            $source['nested'] = array();
        }

        if (array_key_exists('commands',$source) and !is_array($source['commands'])) {
            $commands = array();

            //Замена экранированных символов
            $source['commands'] = str_replace(
                array('\;','\~','\:','\|','\.','\$','\+','\?','\\','\{','\}','\[','\]','\(','\)'),
                array('&#059;','&#126;','&#058;','&#124;','&#046;','&#036;','&#043;','&#063;','&#092;','&#123;','&#125;','&#091;','&#093;','&#040;','&#041;'),
                $source['commands']
            );

            //Разделение списка команд на отдельные команды
            foreach (preg_split('/(?<!\\d);/',$source['commands'],NULL,PREG_SPLIT_NO_EMPTY) as $command_item){
                $command_item = trim($command_item);
                if (empty($command_item)) continue;
                $commands[] = trim($command_item);
            }
            $source['commands'] = $commands;
        }

        $this->_index_item = array_merge($this->_index_item, $source);
        return $this;
    }

    /**
     * Получает все атрибуты (в случае если $index_name не задан), или указанный в переменной $index_name атрибут
     *
     * @param $index_name string Названия атрибута, который необходимо вернуть (по умолчанию — null)
     * @return array|mixed
     * @throws Exception
     */
    public function get($index_name = null){
        if (isset($index_name)){
            if (array_key_exists($index_name,$this->_index_item)){
                return $this->_index_item[$index_name];
            } else {
                throw new Exception("With the name of the index '$index_name' can not be found ");
            }
        }
        return $this->_index_item;
    }

    /**
     * Отчищает указанный атрибут элемента индекса
     *
     * @param $index_name
     * @throws Exception
     */
    public function clear($index_name){
        if (!empty($index_name)){
            if (array_key_exists($index_name,$this->_index_item)){
                switch ($index_name) {
                    case 'node':
                        $this->_index_item[$index_name] = new Node();
                        break;
                    case 'commands':
                    case 'nested':
                        $this->_index_item[$index_name] = array();
                        break;
                    default:
                        $this->_index_item[$index_name] = '';
                }
            } else {
                throw new Exception("With the name of the index '$index_name' can not be found ");
            }
        } else {
            throw new Exception("Specify the key of the property to clean it ");
        }
    }

    /**
     * Устанавливает метку элемента индекса
     *
     * @param $key
     * @param $value
     */
    public function setMark($key,$value){
        $this->_mark[$key] = $value;
    }

    /**
     * Возвращает метку элемента индекса по ключу
     *
     * @param $key
     * @return mixed|string
     */
    public function getMark($key){
        if (key_exists($key,$this->_mark))
            return $this->_mark[$key];
        else
            return '';
    }

    /**
     * Возвращает массив всех меток элемента индекса
     *
     * @return array
     */
    public function getMarkList(){
        return $this->_mark;
    }
}

/**
 * Сегмент DOM-элемента
 *
 * Class Node
 * @package Sim
 */
class Node {

    /**
     * @var array массив открывающего и закрывающего тега DOM-элемента
     */
    private $_wrapper=array('','');

    /**
     * @var string текстовый блок для вставки перед DOM-элементов
     */
    private $_before='';

    /**
     * @var string текстовый блок для вставки после DOM-элемента
     */
    private $_after='';

    /**
     * @var string текстовый блок для вставки перед содержимым DOM-элемента
     */
    private $_prepend='';

    /**
     * @var string текстовый блок для вставки после содержимого DOM-элемента
     */
    private $_append='';

    /**
     * @var string содержимое DOM-элемента
     */
    private $_content='';

    /**
     * @var NodeClassesManager объект управления классами предустановленными класами DOM-элемента
     */
    private $native_classes = null;

    /**
     * @var NodeClassesManager объект управления установленными классами DOM-элемента
     */
    private $custom_classes = null;

    /**
     * @var string значения для вставки в атрибут «class» DOM-элемента
     */
    private $content_classes = '';

    /**
     * @var string содержит предустанвленное шаблоном значения атрибута «class», для его последующей замены
     */
    private $_mark_classes = '';

    /**
     * @var NodeAttributesManager объект управления предустановенными атрибутами DOM-элемента
     */
    private $native_attributes = null;

    /**
     * @var NodeAttributesManager объект управления установленными атрибутами DOM-элемента
     */
    private $custom_attributes = null;

    /**
     * Node constructor.
     * @param string $node_element — строка с валидным HTML кодом
     */
    function __construct($node_element=''){
        $this->setNode($node_element);
    }

    /**
     * Установка DOM-элемента в объект.
     *
     * @param string $node_element — строка с валидным HTML кодом
     * @return $this
     */
    public function setNode($node_element=''){
        if(preg_match('/([^<]*<\s*([a-z0-9]*)[^>]*>)(.*?)(<\s*\/\s*\2\s*>)$/is', $node_element, $matches)) {
            $this->_wrapper = array(trim($matches[1]), trim($matches[4]));
            $this->_content = $matches[3];
        } elseif(preg_match('/[^<]*<\s*[a-z0-9]*[^>]*\s*\/>$/is', $node_element, $matches)){
            $this->_wrapper = array($matches[0],'');
            $this->_content = '';
        } else {
            $this->_wrapper = array('', '');
            $this->_content = $node_element;
        }
        return $this;
    }

    /**
     * Отчищает все свойства объекта
     *
     * @return $this
     */
    public function clearNode(){
        $this->native_classes = null;
        $this->custom_classes = null;
        $this->content_classes = '';
        $this->_mark_classes = '';
        $this->native_attributes = null;
        $this->custom_attributes = null;
        $this->_wrapper = array('', '');
        $this->_before = '';
        $this->_after = '';
        $this->_prepend = '';
        $this->_append = '';
        $this->_content = '';
        return $this;
    }

    /**
     * Возвращает строку с DOM-элементом
     *
     * @return string
     */
    public function getNode(){
        $node_wrapper = $this->mergeAttributesWrapper();
        return $this->_before.
            $node_wrapper[0].
            $this->_prepend.
            $this->_content.
            $this->_append.
            $node_wrapper[1].
            $this->_after;
    }

    /**
     * Возвращает массив из открывающего и закрывающего тега DOM-элемента.
     *
     * @return array
     */
    public function getWrapper(){
        return $this->_wrapper;
    }

    /**
     * Присоединяет заданную строку к блоку, который будет выведен
     * перед DOM-элементом
     *
     * @param string $content
     * @return $this
     */
    public function before($content){
        $this->_before.= $content;
        return $this;
    }

    /**
     * Заменяет текстовый блок вывода перед DOM-элементом на заданную строку
     *
     * @param string $content
     * @return $this
     */
    public function setBeforeContent($content){
        $this->_before = $content;
        return $this;
    }

    /**
     * Возвращает содержимое блока вывода перед DOM-элементом
     *
     * @return string
     */
    public function getBeforeContent(){
        return $this->_before;
    }

    /**
     * Присоединяет заданную строку к блоку, который будет выведен
     * перед содержимым DOM-элемента
     *
     * @param string $content
     * @return $this
     */
    public function prepend($content){
        $this->_prepend.= $content;
        return $this;
    }

    /**
     * Заменяет текстовый блок вывода перед содержимым
     * DOM-элемента на заданную строку
     *
     * @param string $content
     * @return $this
     */
    public function setPrependContent($content){
        $this->_prepend = $content;
        return $this;
    }

    /**
     * Возвращает строку блока вывода перед содержимым DOM-элементом
     *
     * @return string
     */
    public function getPrependContent(){
        return $this->_prepend;
    }

    /**
     * Возвращает содержимое DOM-элемента
     *
     * @return string
     */
    public function getContent(){
        return $this->_content;
    }

    /**
     * Заменяет содержимое DOM-элемента на заданную строку
     *
     * @param string $content
     * @return $this
     */
    public function content($content){
        $this->_content = $content;
        return $this;
    }

    /**
     * Присоединяет заданную строку к блоку, который будет выведен
     * после содержимого DOM-элементом
     *
     * @param string $content
     * @return $this
     */
    public function append($content){
        $this->_append = $content.$this->_append;
        return $this;
    }

    /**
     * Заменяет текстовый блок вывода после содержимого
     * DOM-элемента на заданную строку
     *
     * @param string $content
     * @return $this
     */
    public function setAppendContent($content){
        $this->_append = $content;
        return $this;
    }

    /**
     * Возвращает строку блока вывода после содержимого DOM-элементом
     *
     * @return string
     */
    public function getAppendContent(){
        return $this->_append;
    }

    /**
     * Присоединяет заданную строку к блоку, который будет выведен
     * после DOM-элемента
     *
     * @param string $content
     * @return $this
     */
    public function after($content){
        $this->_after = $content.$this->_after;
        return $this;
    }

    /**
     * Заменяет текстовый блок вывода после DOM-элемента на заданную строку
     *
     * @param string $content
     * @return $this
     */
    public function setAfterContent($content){
        $this->_after = $content;
        return $this;
    }

    /**
     * Возвращает содержимое блока вывода после DOM-элемента
     *
     * @return string
     */
    public function getAfterContent(){
        return $this->_after;
    }

    /**
     * Выполняет парсинг атрибутов DOM-элемента и их распределение
     * по соответствующим свойствам объекта Node
     *
     * @return $this
     * @throws Exception
     */
    private function parseAttributes() {

        $this->native_attributes = new NodeAttributesManager();
        $this->native_classes = new NodeClassesManager();

        if (empty($this->_wrapper[0])) return $this;

        //Если атрибут DOM содержит JSON
        if (preg_match_all('/\s([a-z0-9-_]+)\s*=\s*([\'\"])(\s*[\[\{].*?[\]\}]\s*)(?<!\\\)\2/is', $this->_wrapper[0], $matches)){
            for ($i = 0; $i <= count($matches[0])-1; $i++) {
                if (!is_array(json_decode($matches[3][$i], true))) throw new Exception("Template error indexation in line (Incorrect JSON string)\"".htmlspecialchars($this->_wrapper[0])."\"");
                $attributes[$matches[1][$i]] = array(
                    'name' => $matches[1][$i],
                    'value'=>$matches[3][$i],
                    'source'=>$matches[0][$i],
                    'wrapper'=>array('',''),
                    'removed'=>false
                );
            }
        }

        //Если атрибут DOM не содержит JSON
        if (preg_match_all('/\s([a-z0-9-_]+)\s*=\s*([\'\"])((?!\s*[\[\{]).*?)(?<!\\\)\2/is', $this->_wrapper[0], $matches)){
            for ($i = 0; $i <= count($matches[0])-1; $i++) {
                $matches[1][$i] = trim($matches[1][$i]);
                if ($matches[1][$i]=='class'){
                    $matches[3][$i] = trim(preg_replace('/ {2,}/',' ',$matches[3][$i]));
                    if (!empty($matches[3][$i])){
                        $this->native_classes->reset(explode(' ',$matches[3][$i]));
                        $this->content_classes = '';
                    }
                    $this->_mark_classes = $matches[0][$i];
                } else {
                    $attributes[$matches[1][$i]] = array(
                        'name' => $matches[1][$i],
                        'value' => $matches[3][$i],
                        'source' => $matches[0][$i],
                        'wrapper'=>array('',''),
                        'removed'=>false
                    );
                }
            }
        }

        if (!empty($attributes)){
            $this->native_attributes->reset($attributes);
        }

        return $this;
    }

    /**
     * Возвращает объект-коллекцию дополнительно устаноленных атриутов
     * DOM-элемента.
     *
     * @return NodeAttributesManager
     */
    public function customAttributes(){
        if (empty($this->custom_attributes)) {
            $this->custom_attributes = new NodeAttributesManager();
        }
        return $this->custom_attributes;
    }

    /**
     * Возвращает объект-коллекцию заданных в теге атрибутов
     * DOM-элемента
     *
     * @return NodeAttributesManager
     */
    public function nativeAttributes(){
        if (empty($this->native_attributes)) {
            $this->parseAttributes();
        }
        return $this->native_attributes;
    }

    /**
     * Возвращает объект управления пользовательскими классами
     *
     * @return NodeClassesManager
     */
    public function customClasses(){
        if (empty($this->custom_classes)) {
            $this->custom_classes = new NodeClassesManager();
        }
        return $this->custom_classes;
    }

    /**
     * Возвращает объект управления пердустановленными а DOM-элементе
     * классами
     *
     * @return NodeClassesManager
     */
    public function nativeClasses(){
        if (empty($this->native_classes)) {
            $this->parseAttributes();
        }
        return $this->native_classes;
    }

    /**
     * Устанавливает значение в атрибут «class» DOM-элемента
     *
     * @param string $content
     * @return $this
     */
    public function setClassContent($content=''){
        $this->content_classes = $content;
        return $this;
    }

    /**
     * Возвращает установленное значение для атрибута «class» DOM-элемента
     *
     * @return string
     */
    public function getClassContent(){
        return $this->content_classes;
    }

    /**
     * Объединение атрибутов DOM-элемента с его открывающим тегом
     *
     * @return array
     */
    private function mergeAttributesWrapper(){

        if (empty($this->_wrapper[0])) return array('', $this->_wrapper[1]);


        if (isset($this->native_attributes) and isset($this->custom_attributes)){
            //Удаляем из исходной верстки те атрибуты которые были обработанны
            $stack_attributes = '';
            $native_attributes = $this->native_attributes->getRegisteredList();
            if (!empty($native_attributes)){
                foreach ($native_attributes as $attribute){
                    if ($attribute['removed'] === false) continue;
                    $this->_wrapper[0] = str_replace($attribute['source'], '', $this->_wrapper[0]);
                }
            }
            unset($native_attributes);

            //Добавляем обработанные атрибуты
            if (!$this->custom_attributes->isEmpty()){
                foreach ($this->custom_attributes->getList() as $attribute){
                    $stack_attributes.= ' '.$attribute['wrapper'][0].$attribute['value'].$attribute['wrapper'][1];
                }
                $this->_wrapper[0] = preg_replace('/(\s*\/?\s*>)\s*$/is', $stack_attributes.'${1}', $this->_wrapper[0]);
            }
            unset($stack_attributes);

        }

        if ((isset($this->native_classes) and isset($this->custom_classes)) or !empty($this->content_classes)){
            //Устанавливаем значения существующих классов (которые изначально были в шаблоне)
            $stack_classes = '';
            if (!empty($this->content_classes)){
                $stack_classes = $this->content_classes;
            } elseif (!$this->native_classes->isEmpty()) {
                foreach ($this->native_classes->getList() as $class){
                    $stack_classes.= ' '.$class['wrapper'][0].$class['value'].$class['wrapper'][1];
                }
                $stack_classes = trim($stack_classes);
            }

            //Устанавливаем значения новых классов (добавленных в процессе выполнения команд шаблонизатора)
            if (!$this->custom_classes->isEmpty()){
                foreach ($this->custom_classes->getList() as $class){
                    $stack_classes.= ' '.$class['wrapper'][0].$class['value'].$class['wrapper'][1];
                }
                $stack_classes = trim($stack_classes);
            }

            //Выполняем подмену конструкции class
            if (!empty($stack_classes)){
                if (empty($this->_mark_classes)){
                    $this->_wrapper[0] = preg_replace('/(\s*\/?\s*>)\s*$/is',' class="'.$stack_classes.'"${1}', $this->_wrapper[0]);
                } else {
                    $this->_wrapper[0] = str_replace($this->_mark_classes,' class="'.$stack_classes.'"', $this->_wrapper[0]);
                }
            }

        }

        return array($this->_wrapper[0], $this->_wrapper[1]);
    }
}

/**
 * Class NodeAttributesTrait
 * @package Sim
 */
trait NodeAttributesTrait {

    /**
     * @var array коллекция элементов
     */
    protected $_elements = array();

    /**
     * @var string псевдоним
     */
    protected $_alias = 'element';

    /**
     * Проверяет наличие элементов в коллекции.
     * Если элементов нет, возвращает — true, иначе false
     *
     * @return bool
     */
    function isEmpty(){
        $elements = $this->getList();
        return (empty($elements));
    }

    /**
     * Проверяет существование элемента в коллекции по ID
     *
     * @param string|integer $element_id
     * @return bool
     * @throws Exception
     */
    function exist($element_id){
        if (empty($element_id)) throw new Exception("Incorrect $this->_alias id");
        return (!empty($this->_elements[$element_id]) and $this->_elements[$element_id]['removed'] === false);
    }

    /**
     * Возвращает массив свойств элемента из коллекции по ID
     *
     * @param string|integer $element_id
     * @return array
     * @throws Exception
     */
    function get($element_id){
        if (empty($element_id)) throw new Exception("Incorrect $this->_alias id");
        if (!$this->exist($element_id)) throw new Exception("Not found $this->_alias for id — $element_id");
        return $this->_elements[$element_id];
    }

    /**
     * Возврщает массив актуальных элементов коллекции
     *
     * @return array
     */
    function getList(){
        $arResult = array();
        if (empty($this->_elements)) {
            return $arResult;
        }
        foreach ($this->_elements as $k => $v){
            if ($v['removed'] === true) continue;
            $arResult[$k] = $v;
        }

        return $arResult;
    }

    /**
     * Возвращает массив всех зарегистрированных элементов коллекции
     *
     * @return array
     */
    function getRegisteredList(){
        return $this->_elements;
    }

    /**
     * Удаляет элемент из коллекции по ID. Если параметр full = false (по умолчанию), то элемент будет помечен как
     * удаленный и останется в числе зарегистрированных элементов. В противном случае, будет выполненно полное
     * удаление элемента из коллекции
     *
     * @param string|integer $element_id
     * @param bool $full
     * @return $this
     * @throws Exception
     */
    function remove($element_id, $full = false){
        if (empty($element_id)) throw new Exception("Incorrect $this->_alias id");
        if ($full === true){
            unset($this->_elements[$element_id]);
        } else {
            if (!empty($this->_elements[$element_id])){
                $this->_elements[$element_id]['value'] = '';
                $this->_elements[$element_id]['removed'] = true;
            }
        }
        return $this;
    }

    /**
     * Объединяет обертку элемнта коллекции с переданными значениями
     *
     * @param string|integer $element_id
     * @param string $top сторока для вставки перед элементом коллекции
     * @param string $bottom строка для вставки после элемента коллекции
     * @return $this
     * @throws Exception
     */
    function addWrapper($element_id, $top = '', $bottom = ''){
        if (empty($element_id)) throw new Exception("Incorrect $this->_alias id");
        if (!$this->exist($element_id)) throw new Exception("Not found $this->_alias for id — $element_id");
        if (gettype($top) != 'string') throw new Exception("Incorrect content wrapper — $top");
        if (gettype($bottom) != 'string') throw new Exception("Incorrect content wrapper — $bottom");

        $this->_elements[$element_id]['wrapper'] = array(
            $top.' '. $this->_elements[$element_id]['wrapper'][0],
            $bottom.' '. $this->_elements[$element_id]['wrapper'][1]
        );

        return $this;
    }

    /**
     * Заменяет обертку для элемента коллекции по ID
     *
     * @param string|integer $element_id
     * @param string $top сторока для вставки перед элементом коллекции
     * @param string $bottom строка для вставки после элемента коллекции
     * @return $this
     * @throws Exception
     */
    function setWrapper($element_id, $top = '', $bottom = ''){
        if (empty($element_id)) throw new Exception("Incorrect $this->_alias id");
        if (!$this->exist($element_id)) throw new Exception("Not found $this->_alias for id — $element_id");
        if (gettype($top) != 'string') throw new Exception("Incorrect content wrapper — $top");
        if (gettype($bottom) != 'string') throw new Exception("Incorrect content wrapper — $bottom");

        $this->_elements[$element_id]['wrapper'] = array($top, $bottom);

        return $this;
    }
}

/**
 * Управление классами в DOM-элементе
 *
 * Class NodeClassesManager
 * @package Sim
 */
class NodeClassesManager {

    use NodeAttributesTrait;

    /**
     * NodeClassesManager constructor.
     * @param array $elements
     */
    function __construct(array $elements = array()){
        $this->_alias = 'class';
        if (!empty($elements)) $this->reset($elements);
    }

    /**
     * Добавляет класс в колекцию.
     *
     * @param string|integer $element_id
     * @param string $value — Значение которое должно быть вставленно в DOM-элемент
     * @return $this
     * @throws Exception
     */
    function set($element_id, $value){
        if (empty($element_id)) throw new Exception("Incorrect $this->_alias id");
        if (!empty($this->_elements[$element_id])){
            $this->_elements[$element_id]['value'] = $value;
            $this->_elements[$element_id]['removed'] = false;
        } else {
            $this->_elements[$element_id] = array(
                'value' => $value,
                'removed' => false,
                'wrapper'=> array('','')
            );
        }
        return $this;
    }

    /**
     * Сбрасывает метку «Удален» и значения обертки класса для указанных ID элементов коллекции
     *
     * @param array $elements массив ID's
     * @return $this
     */
    function reset(array $elements = array()){
        $this->_elements = array();
        foreach($elements as $class){
            $this->_elements[$class] = array(
                'value' => $class,
                'removed' => false,
                'wrapper'=> array('','')
            );
        }
        return $this;
    }

}

/**
 * Управление атрибутонами в DOM-элементе
 *
 * Class NodeAttributesManager
 * @package Sim
 */
class NodeAttributesManager {

    use NodeAttributesTrait;

    /**
     * NodeAttributesManager constructor.
     * @param array $elements
     */
    function __construct(array $elements = array()){
        $this->_alias = 'attribute';
        if (!empty($elements)) $this->reset($elements);
    }

    /**
     * Добавляет атрибут в коллекцию
     *
     * @param string $element_id название атрибута
     * @param string $value значение атрибута
     * @return $this
     * @throws Exception
     */
    function set($element_id, $value){
        if (empty($element_id)) throw new Exception("Incorrect $this->_alias id");
        if (!empty($this->_elements[$element_id])){
            $this->_elements[$element_id]['value'] = $value;
            $this->_elements[$element_id]['removed'] = false;
        } else {
            $this->_elements[$element_id] = array(
                'name' => $element_id,
                'value' => $value,
                'source' => false,
                'removed' => false,
                'wrapper'=> array('','')
            );
        }
        return $this;
    }

    /**
     * Переустанавливает значения и свойства атрибутов. На входе принимает массив атрибутов,
     * ключи элементов которого должны соответствовать ID этих атрибутов в коллекции.
     * Если атрибут с указанным ID отсутствует, то он будет добавлен в коллекцию.
     *
     * Каждый атрибут в переданном массиве, так же является массивом со следующими значениями:
     *  • name — название атрибута
     *  • value — значение атрибута
     *  • source — исходный код атрибута (если есть)
     *
     * Для каждого переданного атрибута в массиве будет снята метка «Удален» и удалены
     * установленные значения обертки.
     *
     * @param array $elements
     * @return $this
     */
    function reset(array $elements = array()){
        $this->_elements = array();
        foreach($elements as $attribute){
            if (empty($attribute['name'])) continue;
            $this->_elements[$attribute['name']] = array(
                'name' => $attribute['name'],
                'value' => $attribute['value'],
                'source' => $attribute['source'],
                'removed' => false,
                'wrapper'=> array('','')
            );
        }
        return $this;
    }
}

/**
 * Управление модификаторами (фильтрами)
 *
 * Class FilterManager
 * @package Sim
 */
class FilterManager{
    static final function getFilter($filter){
        $filter_name = '\\'.__NAMESPACE__.'\Filter_'.$filter;
        if (!class_exists($filter_name)) throw new Exception("Filter '$filter' is not found ");
        return new $filter_name();
    }
}

/**
 * Проттотип модификатора (фильтра)
 *
 * Class Filter
 * @package Sim
 */
abstract class Filter{
    abstract public function initialize($var, array $params);
}

/**
 * Class Filter_escape
 * @package Sim
 */
class Filter_escape extends Filter {

    public function initialize($var, array $params = array()){
        $var = '(string) '.$var;
        return 'htmlentities('.$var.', ENT_QUOTES)';
    }

}

/**
 * Class Filter_e
 * @package Sim
 */
class Filter_e extends Filter_escape {}

/**
 * Class Filter_json_encode
 * @package Sim
 */
class Filter_json_encode extends Filter {
    public function initialize($var, array $params = array()){
        return 'json_encode('.$var.', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE)';
    }
}

/**
 * Class Filter_json
 * @package Sim
 */
class Filter_json extends Filter_json_encode {}

/**
 * Class Filter_json_decode
 * @package Sim
 */
class Filter_json_decode extends Filter {
    public function initialize($var, array $params = array()){
        $var = '(string) '.$var;
        return 'json_decode('.$var.', true)';
    }
}

/**
 * Class Filter_round
 * @package Sim
 */
class Filter_round extends Filter {
    public function initialize($var, array $params = array()){
        $var = '(float) '.$var;
        $params[0] = (empty($params[0])) ? '0' : '(int) '.$params[0];
        if (empty($params[1]) or $params[1]=='up'){
            $params[1] = 'PHP_ROUND_HALF_UP';
        } elseif($params[1] == 'down') {
            $params[1] = 'PHP_ROUND_HALF_DOWN';
        } else {
            $params[1] = '';
        }
        return 'round('.$var.', '.$params[0].', '.$params[1].')';
    }
}

/**
 * Class Filter_number_format
 * @package Sim
 */
class Filter_number_format extends Filter {
    public function initialize($var, array $params = array()){
        $var = '(float) '.$var;
        $params[0] = (empty($params[0])) ? '0' : '(int) '.$params[0];
        $params[1] = (empty($params[1])) ? '\'.\'' : '(string) '.$params[1];
        $params[2] = (empty($params[2])) ? '\' \'' : '(string) '.$params[2];
        return 'number_format('.$var.', '.$params[0].', '.$params[1].','.$params[2].')';
    }
}

/**
 * Class Filter_trim
 * @package Sim
 */
class Filter_trim extends Filter {
    public function initialize($var, array $params = array()){
        $var = '(string) '.$var;
        return 'trim('.$var.')';
    }
}

/**
 * Class Filter_serialize
 * @package Sim
 */
class Filter_serialize extends Filter {
    public function initialize($var, array $params = array()){
        $var = '(array) '.$var;
        return 'serialize('.$var.')';
    }
}

/**
 * Class Filter_unserialize
 * @package Sim
 */
class Filter_unserialize extends Filter {
    public function initialize($var, array $params = array()){
        $var = '(string) '.$var;
        return 'unserialize('.$var.')';
    }
}

/**
 * Class Filter_join
 * @package Sim
 */
class Filter_join extends Filter {
    public function initialize($var, array $params = array()){
        $var = '(array) '.$var;
        $params[0] = ((!empty($params[0])) ? '(string) '.$params[0] : '\',\'');
        return 'implode('.$params[0].', '.$var.')';
    }
}

/**
 * Class Filter_split
 * @package Sim
 */
class Filter_split extends Filter {
    public function initialize($var, array $params = array()){
        $var = '(string) '.$var;
        $params[0] = ((!empty($params[0])) ? '(string) '.$params[0] : '\',\'');
        return 'explode('.$params[0].', '.$var.')';
    }
}

/**
 * Class Filter_batch
 * @package Sim
 */
class Filter_batch extends Filter {

    public function initialize($var, array $params = array()){
        $var = '(array) '.$var;
        $params[0] = ((!empty($params[0])) ? '(int) '.$params[0] : '1');
        $params[1] = ((!empty($params[1])) ? '(string) '.$params[1] : '\'\'');
        return '\\'.__NAMESPACE__.'\Filter_batch::get('.$var.', '.$params[0].', '.$params[1].')';
    }

    public static function get(array $array=array(), $count=0, $default=''){
        $count = (int) $count;
        $count = ($count<=0) ? 1 : $count;
        $arResult = array_chunk($array, $count, true);

        if (empty($default)) return $arResult;

        $last = array_pop($arResult);

        $count_ = $count - count($last);
        if ($count_ == 0) {
            array_push($arResult,$last);
            return $arResult;
        }

        for ($i=1; $i <= $count_; ++$i){
            array_push($last, $default);
        }

        array_push($arResult,$last);

        return $arResult;
    }
}

/**
 * Class Filter_keys
 * @package Sim
 */
class Filter_keys extends Filter {
    public function initialize($var, array $params = array()){
        $var = '(array) '.$var;
        return 'array_keys('.$var.')';
    }
}

/**
 * Class Filter_sort
 * @package Sim
 */
class Filter_sort extends Filter {

    public function initialize($var, array $params = array()){
        $var = '(array) '.$var;
        $params[0] = (empty($params[0]) or $params[0] == 'asc' or $params[0] != 'desc') ? '\'asc\'' : '\'desc\'';
        return '\\'.__NAMESPACE__.'\Filter_sort::get('.$var.', '.$params[0].')';
    }

    public static function get(array $array = array(), $direction = 'asc'){
        if (empty($array)) return $array;
        if ($direction == 'asc'){
            asort($array);
        } elseif ($direction == 'desc'){

            arsort($array);
        }
        return $array;
    }
}

/**
 * Class Filter_date
 * @package Sim
 */
class Filter_date extends Filter {

    public function initialize($var, array $params = array()){
        $var = '(string) '.$var;
        $params[0] = ((!empty($params[0])) ? '(string) '.$params[0] : '\'d.m.Y\'');

        return '\\'.__NAMESPACE__.'\Filter_date::get('.$var.', '.$params[0].')';

    }

    public static function get($date='', $format = 'd.m.Y'){
        if (empty($date)){
            $date = 0;
        } else {
            $date = strtotime($date);
            if ($date === false or $date == -1){
                $date = 0;
            }
        }
        return date($format, $date);
    }
}

/**
 * Class Filter_empty
 * @package Sim
 */
class Filter_empty extends Filter {
    public function initialize($var, array $params = array()){
        return 'empty('.$var.')';
    }
}

/**
 * Class Filter_count
 * @package Sim
 */
class Filter_count extends Filter {
    public function initialize($var, array $params = array()){
        return 'count((array) '.$var.')';
    }
}

/**
 * Class Filter_in_array
 * @package Sim
 */
class Filter_in_array extends Filter {
    public function initialize($var, array $params = array()){
        $params[0] = (empty($params[0])) ? '' : $params[0];
        return 'in_array('.$params[0].', (array) '.$var.')';
    }
}

/**
 * Class Filter_is_array
 * @package Sim
 */
class Filter_is_array extends Filter {
    public function initialize($var, array $params = array()){
        return 'is_array('.$var.')';
    }
}

/**
 * Class Filter_is_string
 * @package Sim
 */
class Filter_is_string extends Filter {
    public function initialize($var, array $params = array()){
        return 'is_string('.$var.')';
    }
}

/**
 * Class Filter_is_numeric
 * @package Sim
 */
class Filter_is_numeric extends Filter {
    public function initialize($var, array $params = array()){
        return 'is_numeric('.$var.')';
    }
}

/**
 * Class Filter_is_bool
 * @package Sim
 */
class Filter_is_bool extends Filter {
    public function initialize($var, array $params = array()){
        return 'is_bool('.$var.')';
    }
}

/**
 * Class Filter_is_float
 * @package Sim
 */
class Filter_is_float extends Filter {
    public function initialize($var, array $params = array()){
        return 'is_float('.$var.')';
    }
}

/**
 * Class Filter_is_integer
 * @package Sim
 */
class Filter_is_integer extends Filter {
    public function initialize($var, array $params = array()){
        return 'is_integer('.$var.')';
    }
}

/**
 * Class Filter_is_int
 * @package Sim
 */
class Filter_is_int extends Filter_is_integer {}

/**
 * Class Filter_integer
 * @package Sim
 */
class Filter_integer extends Filter {
    public function initialize($var, array $params = array()){
        return '(int) '.$var;
    }
}

/**
 * Class Filter_float
 * @package Sim
 */
class Filter_float extends Filter {
    public function initialize($var, array $params = array()){
        return '(float) '.$var;
    }
}

/**
 * Class Filter_boolean
 * @package Sim
 */
class Filter_boolean extends Filter {
    public function initialize($var, array $params = array()){
        return '(bool) '.$var;
    }
}

/**
 * Class Filter_string
 * @package Sim
 */
class Filter_string extends Filter {
    public function initialize($var, array $params = array()){
        return '(string) '.$var;
    }
}

/**
 * Class Filter_array
 * @package Sim
 */
class Filter_array extends Filter {
    public function initialize($var, array $params = array()){
        return '(array) '.$var;
    }
}

/**
 * Class Filter_abs
 * @package Sim
 */
class Filter_abs extends Filter {
    public function initialize($var, array $params = array()){
        return 'abs('.$var.')';
    }
}

/**
 * Class Filter_capitalize
 * @package Sim
 */
class Filter_capitalize extends Filter {
    public function initialize($var, array $params = array()){
        return '\\'.__NAMESPACE__.'\Filter_capitalize::get('.$var.')';
    }

    public static function get($string){
        return mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr(mb_strtolower($string), 1, mb_strlen($string) - 1);
    }
}

/**
 * Class Filter_lower
 * @package Sim
 */
class Filter_lower extends Filter {
    public function initialize($var, array $params = array()){
        return 'mb_strtolower('.$var.')';
    }
}

/**
 * Class Filter_upper
 * @package Sim
 */
class Filter_upper extends Filter {
    public function initialize($var, array $params = array()){
        return 'mb_strtoupper('.$var.')';
    }
}

/**
 * Class Filter_striptags
 * @package Sim
 */
class Filter_striptags extends Filter {
    public function initialize($var, array $params = array()){
        return 'strip_tags('.$var.')';
    }
}

/**
 * Class Filter_url_encode
 * @package Sim
 */
class Filter_url_encode extends Filter {
    public function initialize($var, array $params = array()){
        return 'urlencode('.$var.')';
    }
}

/**
 * Class Filter_slashes
 * @package Sim
 */
class Filter_slashes extends Filter {
    public function initialize($var, array $params = array()){
        return 'addslashes('.$var.')';
    }
}

/**
 * Class Filter_br
 * @package Sim
 */
class Filter_br extends Filter {
    public function initialize($var, array $params = array()){
        return 'str_replace("\n", \'<br>\', '.$var.')';

    }
}

/**
 * Компиляция команд (операторов) или отдельных сигментов кода.
 *
 * Class Code
 * @package Sim
 */
abstract class Code {

    /**
     * @var Index объект индекса шаблона
     */
    protected $_index_controller;

    /**
     * @var IndexItem объект элемента индекса шаблона
     */
    protected $_index_item;

    /**
     * @var Node объект DOM-элемента индекса
     */
    protected $_node;

    /**
     * @var string условие выполнения команды
     */
    private $_condition;

    /**
     * @var string команда
     */
    protected $_command;

    /**
     * Code constructor.
     *
     * @param string $command — Команда (оператор) для компиляции
     * @param IndexItem $index_item — объект элемент индекса, который необходимо скомпилировать
     * @param Index $index_controller — коллекция элементов индекса
     * @throws Exception
     */
    final function __construct($command, IndexItem $index_item, Index $index_controller){
        if (empty($command)) throw new Exception("Run command not found");
        if (empty($index_item)) throw  new Exception("Item index for command execution not found");
        if (empty($index_item)) throw  new Exception("Index controller for command execution not found");

        $this->_index_controller = $index_controller;
        $this->_index_item = $index_item;
        $this->_node = $index_item->get('node');
        $this->_command = $command;
        if ($this->initialize($command) === false) throw new Exception("Syntax error in data-sim '$command' ");

        return $this;
    }

    /**
     * Инициализация команды
     *
     * @param $command
     * @return mixed
     */
    abstract protected function initialize($command);

    /**
     * Выполнение команды без условия
     */
    abstract protected function commandExecution();

    /**
     * Выполнение команды с уловием
     *
     * @param string $condition условие для выполнение команды
     */
    abstract protected function conditionExecution($condition);

    /**
     * Запуск выполнения команды (оператора)
     */
    final public function execute(){
        if ($this->isCondition()){
            $this->conditionExecution($this->getCondition());
        } else {
            $this->commandExecution();
        }
    }

    /**
     * Мeтод выполняет преобразование названия переменной из формата шаблонизатора в формат php.
     * Например: если в функцию передана строка "$data.box.test+",
     * то функция вернет строку "$this->_data['box']['test']."
     *
     * @param string $variable Переменная в формате шаблонизатора
     * @param string $filter Имя фильтра для переменной
     * @param array $filter_option Опции фильтра для переменной
     *
     * @return string
     * @throws Exception
     */
    final protected function constructVariable($variable, $filter='', array $filter_option=array()){
        if (gettype($variable) != 'string') throw new Exception('Wrong format of the variable "'.$variable.'" to command '.$this->_command);

        $variable = str_replace(array('$','+'), '' , $variable);
        $_variable = explode('.', $variable, 2);
        $space_variable = $_variable[0];
        $address_variable = (!empty($_variable[1])) ? '["'.str_replace('.','"]["',$_variable[1]).'"]' : '';

        if ($space_variable == 'data'){
            $variable = '$data'.$address_variable;
        } elseif ($space_variable == '_root'){
            $variable = '$root'.$address_variable;
        } elseif ($space_variable == '_root') {
            $variable = '$block' . $address_variable;
        } elseif(in_array($space_variable, array('_server','_get','_post','_request','_cookie','_session','_env'))){
            $variable = '$'.strtoupper($space_variable).$address_variable;
        } else {
            //$variable = '$data["custom__'.$space_variable.'"]'.$address_variable;
            $variable = '$'.$space_variable.$address_variable;
        }

        if (!empty($filter)){
            if (!empty($filter_option)){
                foreach ($filter_option as $option_k=>$option_v){
                    $filter_option[$option_k] = $this->concat($option_v);
                }
            }
            $variable = FilterManager::getFilter($filter)->initialize($variable, $filter_option);
        }
        return $variable;
    }

    /**
     * Возвращает значение указаной переменной
     *
     * @param string $variable Название переменной (Например: $data.box.test)
     *
     * @return string
     * @throws Exception
     */
    final public function getVariable($variable){
        if (gettype($variable) != 'string') throw new Exception('Wrong format of the variable "'.$variable.'" to command '.$this->_command);
        if (!preg_match('/^\+?\$[a-z0-9-_\.]*\+?$/is',$variable)) throw new Exception('Syntax error in data-sim "'.$variable.'" to command '.$this->_command);

        $source_variable = $variable;
        $variable = $this->constructVariable($variable);
        $value = NULL;
        if (@eval('$value='.$variable.';')===false) {
            throw new Exception('Syntax error in data-sim "'.$source_variable.'" to command '.$this->_command);
        }

        return $variable;
    }

    /**
     * Устанавливает условие для выполнения команды
     *
     * @param string $condition — Условие в рамках синтаксиса шаблонизатора
     * @return $this
     * @throws Exception
     */
    final public function setCondition($condition){
        if (empty($condition) or gettype($condition) != 'string') throw new Exception('Wrong format of the variable "'.$condition.'" to command '.$this->_command);

        $condition_source = $condition;
        $operators_patterns = array(
            '/\s+LT\s+/is'=>' < ',
            '/\s+GT\s+/'=>' > ',
            '/\s+LE\s+/'=>' <= ',
            '/\s+GE\s+/'=>' >= ',
            '/\s+EQ\s+/'=>' == ',
            '/\s+NE\s+/'=>' != ',
            '/\s+AND\s+/'=>' && ',
            '/\s+OR\s+/'=>' || ',
            '/(^|[\(|\s])(NOT\s*)/'=>'${1}!'
        );

        $operators = array(
            '\LT'=>'LT',
            '\GT'=>'GT',
            '\LE'=>'LE',
            '\GE'=>'GE',
            '\EQ'=>'EQ',
            '\NE'=>'NE',
            '\AND'=>'AND',
            '\OR'=>'OR',
            '\NOT'=>'NOT'
        );

        $condition = $this->constructConcat($condition);

        //Устанавливаем операторы сравнения
        $condition = preg_replace(array_keys($operators_patterns),array_values($operators_patterns),$condition);

        //Преобразовываем экранированные оператораторы сравнения в строке
        $condition = str_replace(array_keys($operators),array_values($operators),$condition);

        //Выполняем проверку сгенерированного сравнения
        $value=NULL;
        if (@eval('$value=(('.$condition.')?true:false);')===false) {
            throw new Exception('Syntax error in data-sim "'.$condition_source.'" to command '.$this->_command);
        }

        $this->_condition = $condition;
        return $this;
    }

    /**
     * Возвращает условие выполнения команды
     *
     * @return string
     */
    final public function getCondition(){
        return $this->_condition;
    }

    /**
     * Проверяет заданно ли условие для текущей команды
     *
     * @return bool
     */
    final public function isCondition(){
        return (!empty($this->_condition));
    }

    /**
     * Конструктор конкатинации строки
     *
     * @param string $string — Строка для конкатинации в рамках синтаксиса шаблонизатора
     * @return string
     * @throws Exception
     */
    final protected function constructConcat($string){
        if (gettype($string) != 'string') throw new Exception('Wrong format of the variable "'.$string.'" to command '.$this->_command);

        $replace = array(); //Содержит найденные в строке мессивы и переменные
        $replace_i = 0; //Индекс замены

        //Находим переменные в строке сохраняем $replace, проставляем идексы $replace_i
        if (preg_match_all('/(\$[a-z0-9-_\.]+)(?:\:([a-z_]+)(?:\{(.*?)\})?)?/is', $string, $vars)){
            foreach ($vars[0] as $k=>$var){

                $variable = $vars[0][$k];
                $var = $vars[1][$k]; //Пееменная
                $filter = $vars[2][$k]; //Фильтр переменной
                $filter_option = (empty($vars[3][$k])) ? array() : explode('|', $vars[3][$k]); //Параметры фильтра для переменной

                $replace['var'][$replace_i] = $this->constructVariable($var, $filter, $filter_option);
                $string = Procedure::str_replace_once($variable, '{'.$replace_i.'}', $string);

                $replace_i ++;
            }
        }

        //Находим массивы в строке сохраняем $replace, проставляем идексы $replace_i
        if (preg_match_all('/(?(R)\[|\[)((?:[^\[\]]+|(?R))*)(?(R)\]|\])/is', $string, $arrays)){
            foreach($arrays[0] as $k=>$array){
                $replace['array'][$replace_i] = $array;
                $string = Procedure::str_replace_once($arrays[0][$k], '{'.$replace_i.'}', $string);
                $replace_i ++;
            }
        }

        //Заменяем знак конкатинации «+», на «.» между логическими блоками строки (}+{, '+', '+{, }+')
        //Во всех остальных ситуация знак «+», будет интерпритироваться как оператор сложения,
        //за исклбченим ситуации когда он находится внутри одинарных кавычек.
        $string = preg_replace('/([\\\'\}])\s*\+\s*([\\\'\{])/is','${1}.${2}',$string);

        //Возвращаем в строку массивы
        if (!empty($replace['array'])){
            foreach ($replace['array'] as $k => $v){
                $string = str_replace('{'.$k.'}', $v, $string);
            }
        }

        //Возвращаем в строку переменные
        if (!empty($replace['var'])){
            foreach ($replace['var'] as $k => $v){
                $string = str_replace('{'.$k.'}', $v, $string);
            }
        }

        return trim($string);
    }

    /**
     * Выполняет конкатинацию строки, и преобразование названия переменных из формата шаблонизатора в формат php.
     *
     * @param string $string исходная строка для конкатинации
     *
     * @return string
     * @throws Exception
     */
    final public function concat($string){
        $source_string = $string;
        $string = $this->constructConcat($string);
        $value = NULL;

        //При возникновении фатальной ошибки скрипт прекратит свою работу и будет отображена стандартная ошибка
        if (@eval('$value='.$string.';')===false) {
            throw new Exception('Syntax error in data-sim "'.$source_string.'" to command '.$this->_command);
        }

        return $string;
    }

}

/**
 * Class Execute_foreach
 * @package Sim
 */
class Execute_foreach extends Code {

    protected $_source;
    protected $_block;
    protected $_nested_content;
    protected $_foreach_code=array();
    protected $_exception;
    protected $_exception_content;

    protected function initialize($command){
        if (preg_match('/\s*(foreach)\s*\((.*?)\s+as\s+(\$[a-z0-9-_\.]+)(?:\s+~exception +(clear|none))?\s*\)\s*$/is',$command,$command_content)){
            $this->_source = $command_content[2];
            $this->_block = $command_content[3];
            $this->_exception = (empty($command_content[4])) ? '' : trim($command_content[4]);
        } else return false;

        $this->nestedGenerate();

        if (empty($this->_exception) or $this->_exception == 'clear'){
            $this->_exception_content = '';
        } else {
            $this->_exception_content = $this->_nested_content;
        }

        $this->codeGenerate();

        return true;
    }

    protected function nestedGenerate(){
        //Массив скомпилированных сегментов зависимостей
        $execute_result = array();

        //Получаем и обходим все зависимости текущего элемента индекса
        foreach ($this->_index_item->get('nested') as $nested_id){

            //Проверяем наличие индекса для зависимости
            if (!$this->_index_controller->exists($nested_id)) continue;

            //Получаем элемент индекса зависимости по ID
            $nested_index_item = $this->_index_controller->getByID($nested_id);

            //Получаем ключ зависимости (по ключу в шаблоне родительского элемента индекса выполняется
            //подмена на скомпилированное содержания дочерней зависимости)
            $nested_index_key = $nested_index_item->get('index_key'); //Получаем ключ зависимости

            //Компилируем полученную зависимость (будут выполненны все ее команды включая команды
            //дочерних зависимостей)
            $this->_index_controller->compileElementIndex($nested_index_item); //Выполняем зависимость

            //Получаем скомпилированное содердание зависимости
            if (key_exists($nested_index_key,$execute_result)){
                $execute_result[$nested_index_key].= $nested_index_item->get('node')->getNode();
            } else {
                $execute_result[$nested_index_key] = $nested_index_item->get('node')->getNode();
            }

        }

        //Удалаяем записи о наличии зависимостей для текущего элемента индекса
        $this->_index_item->clear('nested');

        //Выставляем в шаблон текущего элемента индекса скомпилированное содердание зависимостей по ключу,
        //который мы получили ранее.
        $this->_nested_content = str_replace(array_keys($execute_result),array_values($execute_result), $this->_node->getContent());

    }

    protected function codeGenerate(){
        $mask_variable = str_replace(array('$','-','.','+'),array('$var_','_dash_','_point_','_plus_'),$this->_block);
        $variable=array(
            'source'=>$this->concat($this->_source),
            'block'=>$this->getVariable($this->_block),
            'step_key'=>$mask_variable.'_k',
            'step_value'=>$mask_variable.'_v'
        );

        $this->_foreach_code = array('
            <? 
                try{
                    '.$variable['block'].'["index"] = 0;
                    foreach('.$variable['source'].' as '.$variable['step_key'].' => '.$variable['step_value'].'):
                        '.$variable['block'].'["item"] = '.$variable['step_value'].';
                        '.$variable['block'].'["key"] = '.$variable['step_key'].';
                        '.$variable['block'].'["number"] = '.$variable['block'].'["index"]+1'.'; 
            ?>',
            '<? 
                        '.$variable['block'].'["index"]++;
                    endforeach; 
                    unset('.$variable['block'].','.$variable['step_key'].','.$variable['step_value'].');
                } catch (\Exception $e){?>
                    '.$this->_exception_content.'
                <?}
            ?>'
        );

    }

    protected function commandExecution(){
        $this->_node->content($this->_foreach_code[0].$this->_nested_content.$this->_foreach_code[1]);
    }

    protected function conditionExecution($condition) {
        $this->_node->content('<? if ('.$condition.'): ?>'.$this->_foreach_code[0].$this->_nested_content.$this->_foreach_code[1].'<? else: ?>'.$this->_nested_content.'<? endif; ?>');
    }


}

/**
 * Class Execute_repeat
 * @package Sim
 */
class Execute_repeat extends Execute_foreach {

    protected function initialize($command){
        if (preg_match('/\s*(repeat)\s*\((.*?)\s+as\s+(\$[a-z0-9-_\.]+)(?:\s+~exception +(clear|none))?\s*\)\s*$/is',$command,$command_content)){
            $this->_source = $command_content[2];
            $this->_block = $command_content[3];
            $this->_exception = (empty($command_content[4])) ? '' : trim($command_content[4]);
        } else return false;

        $this->nestedGenerate();

        if (empty($this->_exception) or $this->_exception == 'clear'){
            $this->_exception_content = '';
        } else {
            $this->_exception_content = $this->_nested_content;
        }

        $this->codeGenerate();

        return true;
    }

    protected function nestedGenerate(){

        $commands = $this->_index_item->get('commands');
        $current_command_id = (int) $this->_index_item->getMark('current_command');

        $before_content = $this->_node->getBeforeContent();
        $this->_node->setBeforeContent('');
        $after_content = $this->_node->getAfterContent();
        $this->_node->setAfterContent('');

        if ($commands>0 and count($commands)-1 != $current_command_id){
            for ($i = $current_command_id+1; $i <= count($commands)-1; $i++) {
                if ($this->_index_item->getMark('break_execution_commands')) break;
                $this->_index_item->setMark('current_command', $i);
                $this->_index_controller->compileCommandIndex($commands[$i], $this->_index_item);
            }
        }

        $this->_index_item->setMark('break_execution_commands', true);

        parent::nestedGenerate();

        $this->_nested_content = $this->_node->content($this->_nested_content)->getNode();
        $this->_node->clearNode();
        $this->_node->setBeforeContent($before_content);
        $this->_node->setAfterContent($after_content);
    }

    protected function commandExecution(){

        $this->_node->before($this->_foreach_code[0]);
        $this->_node->content($this->_nested_content);
        $this->_node->after($this->_foreach_code[1]);

    }

    protected function conditionExecution($condition) {

        $this->_node->before('<? if ('.$condition.'): ?>'.$this->_foreach_code[0]);
        $this->_node->content($this->_nested_content);
        $this->_node->after($this->_foreach_code[1].'<? else: ?>'.$this->_nested_content.'<? endif; ?>');

    }

}

/**
 * Class Execute_content
 * @package Sim
 */
class Execute_content extends Code{

    protected $_modifier;
    protected $_value;

    protected function initialize($command){
        if (preg_match('/\s*(content)\s*\((.*?)(?:\s+~default +(.*?))?\s*\)\s*$/is',$command,$command_content)){
            if (empty($command_content['3'])){
                $this->_value = $this->concat($command_content['2']);
            } else {
                $value = $this->concat($command_content['2']);
                $default_value = $this->concat($command_content['3']);
                $this->_value = '\\'.__NAMESPACE__.'\Execute_content::isEmptyThen('.$value.', '.$default_value.')';
            }
        } else return false;
        return true;
    }

    public static function isEmptyThen($value, $default = ''){
        if (empty($value)) return $default;
        return $value;
    }

    protected function commandExecution(){

        /*
         * Выполнение команды подрозумевает замену содердимого (content) сегмента.
         * Соответственно, если команда объявляется ьез условия, то наличие зависимостей (вложенных сегментов),
         * теряет смысл.
         */

        //Получаем списко зависимостей (вложенных сегментов)
        $nested_list = $this->_index_item->get('nested');
        if (!empty($nested_list)){

            //Объодим зависимости и удаляем связанные с ними элементы индекса
            foreach ($nested_list as $nested_id) {
                $this->_index_controller->remove($nested_id);
            }

            //Отчищаем перечень зависимостей в текущем элементе индекса
            $this->_index_item->clear('nested');
        }

        //Заменяем содержание текущего сегмента
        $this->_node->content('<?='.$this->_value.';?>');

    }

    protected function conditionExecution($condition){
        $this->_node->content('<? if ('.$condition.'): echo '.$this->_value.'; else: ?>'.$this->_node->getContent().'<? endif; ?>');
    }

}

/**
 * Class Execute_if
 * @package Sim
 */
class Execute_if extends Code{

    protected $_condition_content;
    protected $_condition_command;

    protected function initialize($command){
        if (preg_match('/^\s*(if)\s*\((.*?)\?(.*?)\)\s*$/is',$command,$command_content)){
            $this->_condition_content = $command_content['2'];
            $this->_condition_command = $command_content['3'];
        } else return false;
        if ($this->isCondition()){
            throw new Exception('Syntax error in data-sim "'.$command.'" — use nested conditions');
        }
        return true;
    }

    protected function commandExecution(){
        $this->_index_controller->compileCommandIndex($this->_condition_command, $this->_index_item, $this->_condition_content);
    }

    protected function conditionExecution($condition){
        return null;
    }

}

/**
 * Class Execute_attributes
 * @package Sim
 */
class Execute_attributes extends Code{

    protected $_attribute;
    protected $_value;
    protected $_remove=false;

    protected function initialize($command){
        if (preg_match('/\s*(attributes|attr)\s*\(\s*\'?\s*([a-z0-9-_]+)\s*\'?\s*,\s*(.*?)\s*\)\s*$/is',$command,$command_content)){
            $this->_attribute = $command_content['2'];
            $this->_value = '\''.$this->_attribute.'=\\\'\'.'.$this->concat($command_content['3']).'.\'\\\'\'';
            $this->_remove = false;
        } elseif(preg_match('/\s*(attributes|attr)\s*\(\s*remove\s*\'?\s*([a-z0-9-_]+)\s*\'?\s*\)\s*$/is',$command,$command_content)) {
            $this->_attribute = $command_content['2'];
            $this->_remove = true;
        } else return false;

        return true;
    }

    public function commandExecution(){

        $custom_attributes = $this->_node->customAttributes();
        $native_attributes = $this->_node->nativeAttributes();

        if ($this->_remove){

            $custom_attributes->remove($this->_attribute);
            $native_attributes->remove($this->_attribute);

        } else {

            $value = 'echo '.$this->_value.';';
            $custom_attributes->set($this->_attribute, $value);
            $custom_attributes->setWrapper($this->_attribute, '<? ', ' ?>');
            $native_attributes->remove($this->_attribute);

        }

        return null;
    }

    public function conditionExecution($condition){

        $custom_attributes = $this->_node->customAttributes();
        $native_attributes = $this->_node->nativeAttributes();

        if ($this->_remove){

            $value = 'if (!('.$condition.')): ';

            if ($custom_attributes->exist($this->_attribute)){
                $value.= $custom_attributes->get($this->_attribute)['value'];
                if ($native_attributes->exist($this->_attribute)){
                    $value.= ' else: echo \''.str_replace('\'','\\\'',$native_attributes->get($this->_attribute)['source']).'\';';
                }
            } elseif ($native_attributes->exist($this->_attribute)){
                $value.= 'echo \''.str_replace('\'','\\\'',$native_attributes->get($this->_attribute)['source']).'\';';
            } else {
                return null;
            }

            $value.= ' endif;';
            $custom_attributes->set($this->_attribute, $value);
            $native_attributes->remove($this->_attribute);

        } else {

            $value = 'if ('.$condition.'): echo '.$this->_value.';';
            if ($custom_attributes->exist($this->_attribute)){
                $value.= ' else: '.$custom_attributes->get($this->_attribute)['value'];
            } elseif ($native_attributes->exist($this->_attribute)){
                $value.= ' else: echo \''.str_replace('\'','\\\'',$native_attributes->get($this->_attribute)['source']).'\';';
            }
            $value.= ' endif;';
            $custom_attributes->set($this->_attribute, $value);
            $native_attributes->remove($this->_attribute);

        }

        $custom_attributes->setWrapper($this->_attribute, '<? ', ' ?>');
        return null;
    }

}

/**
 * Class Execute_attr
 * @package Sim
 */
class Execute_attr extends Execute_attributes{}

/**
 * Class Execute_class
 * @package Sim
 */
class Execute_class extends Code{

    protected $_class;
    protected $_remove=false;

    protected function initialize($command){
        if (preg_match('/\s*(class)\s*\(\s*remove\s+(.*?)\s*\)\s*$/is',$command,$command_content)){
            $this->_class = $this->concat($command_content['2']);
            $this->_remove = true;
        } elseif(preg_match('/\s*(class)\s*\(\s*(.*?)\s*\)\s*$/is',$command,$command_content)) {
            $this->_class = $this->concat($command_content['2']);
            $this->_remove = false;
        } else return false;

        return true;
    }

    protected function commandExecution(){
        $custom_classes = $this->_node->customClasses();

        if ($this->_remove){
            if (!$custom_classes->exist($this->_class)){
                $custom_classes->set($this->_class, '');
            }
            $custom_classes->remove($this->_class);
        } else {
            $value = 'echo '.$this->_class.';';
            $custom_classes->set($this->_class, $value);
            $custom_classes->setWrapper($this->_class, '<? ', ' ?>');
        }

        $this->getControllerDuplicateClasses();
    }

    protected function conditionExecution($condition){
        $custom_classes = $this->_node->customClasses();

        if ($this->_remove){
            $value = 'if (!('.$condition.')): ';
            if ($custom_classes->exist($this->_class)){
                $value.= $custom_classes->get($this->_class)['value'];
            } else {
                $value.= 'echo '.$this->_class.';';
            }
            $value.= ' endif;';
            $custom_classes->set($this->_class, $value);
        } else {
            $value = 'if ('.$condition.'): echo '.$this->_class.';';
            if ($custom_classes->exist($this->_class)){
                $value.= ' else: '.$custom_classes->get($this->_class)['value'];
            }
            $value.= ' endif;';
            $custom_classes->set($this->_class, $value);
        }

        $custom_classes->setWrapper($this->_class, '<? ', ' ?>');
        $this->getControllerDuplicateClasses();
    }

    final protected function getControllerDuplicateClasses(){
        $native_classes = $this->_node->nativeClasses()->getRegisteredList();
        $custom_classes = $this->_node->customClasses()->getRegisteredList();

        if (empty($custom_classes)){
            $this->_node->setClassContent('');
            return null;
        }

        if (empty($native_classes)){
            return null;
        }

        $this->_node->setClassContent('<? \\'.__NAMESPACE__.'\Execute_class::removeDuplicateClasses(
                    array(\''.implode('\',\'', array_keys($native_classes)).'\'), 
                    array('.implode(',', array_keys($custom_classes)).')
                 ); ?>');

    }

    final public static function removeDuplicateClasses(array $native_classes, array $custom_classes){
        if (empty($native_classes)){
            return null;
        }
        if (empty($custom_classes)) {
            echo implode(' ', $native_classes);
            return null;
        }
        foreach($native_classes as $k=> $v){
            if (in_array($v,$custom_classes)){
                unset($native_classes[$k]);
            }
        }
        echo implode(' ', $native_classes);
    }
}

/**
 * Class Execute_set
 * @package Sim
 */
class Execute_set extends Code{

    protected $_variable;
    protected $_value;

    protected function initialize($command){
        if (preg_match('/\s*(set)\s*\(\s*(\$[a-z0-9-_\.]+)\s*,(.*?)\)\s*$/is',$command,$command_content)){
            $this->_variable = $this->getVariable(trim($command_content['2']));
            $this->_value = $this->concat($command_content['3']);
        } else return false;
        return true;
    }

    protected function commandExecution(){
        $this->_node->before('<? '.$this->_variable.' = '.$this->_value.'; ?>');
    }

    protected function conditionExecution($condition){
        $this->_node->before('<? if ('.$condition.'): '.$this->_variable.' = '.$this->_value.'; endif; ?>');
    }
}

/**
 * Class Execute_import
 * @package Sim
 */
class Execute_import extends Code {
    protected $_resource;
    protected $_prefix;

    protected function initialize($command){
        if (preg_match('/\s*(import)\s*\(\s*(.*?)(?:\s+~prefix\s+(.*?))?\s*\)\s*$/is',$command,$command_content)){
            $this->_resource = $this->concat($command_content['2']);
            $this->_prefix = (empty($command_content['3'])) ? '\'\'' : $this->concat($command_content['3']);
        } else return false;
        return true;
    }

    protected function commandExecution(){
        $this->_node->before('<? \\'.__NAMESPACE__.'\Execute_import::get('.$this->_resource.', '.$this->_prefix.', $template[\'object\']); ?>');
    }

    protected function conditionExecution($condition){
        $this->_node->before('<? if ('.$condition.'): '.__NAMESPACE__.'\Execute_import::get('.$this->_resource.', '.$this->_prefix.', $template[\'object\']); endif; ?>');
    }

    public static function get($resource='', $prefix='', Environment $template_object){
        try {
            $arMacros = (new Index())->getMacros(File::get_content($resource));
            foreach ($arMacros as $item){
                $template_object->macros->add($prefix.$item['name'],$item['content']);
            }
        } catch (Exception $e) {
            throw new Exception('Template for import not found "'.$resource.'"');
        }
    }
}

/**
 * Class Execute_include
 * @package Sim
 */
class Execute_include extends Code {
    protected $_resource;
    protected $_data;
    protected $_load_type;
    protected $_load_to;

    protected function initialize($command){
        if (preg_match('/\s*(include)\s*\(\s*(.*?)\s*(?:,\s*(\[.*?\]|\$[a-z0-9-_\.]+))?(?:\s+~(set) +(\$[a-z0-9-_\.]+))?\s*\)\s*$/is',$command,$command_content)){
            $this->_resource = $this->concat($command_content['2']);
            $this->_data = (empty($command_content['3'])) ? 'array()' : $this->concat($command_content['3']);
            $this->_load_type = (empty($command_content['4'])) ? 'include' : $command_content['4'];
            $this->_load_to = (empty($command_content['5'])) ? '' : $this->getVariable($command_content['5']);
        } else return false;
        return true;
    }

    protected function commandExecution(){
        if ($this->_load_type == 'include') {

            /*
             * Выполнение команды подрозумевает замену содердимого (content) сегмента.
             * Соответственно, если команда объявляется ьез условия, то наличие зависимостей (вложенных сегментов),
             * теряет смысл.
             */

            //Получаем списко зависимостей (вложенных сегментов)
            $nested_list = $this->_index_item->get('nested');
            if (!empty($nested_list)) {

                //Объодим зависимости и удаляем связанные с ними элементы индекса
                foreach ($nested_list as $nested_id) {
                    $this->_index_controller->remove($nested_id);
                }

                //Отчищаем перечень зависимостей в текущем элементе индекса
                $this->_index_item->clear('nested');
            }
            $this->_node->content('<?= \\'.__NAMESPACE__.'\Execute_include::get('.$this->_resource.', '.$this->_data.', $template[\'object\']); ?>');
        } elseif ($this->_load_type == 'set'){
            $this->_node->before('<? '.$this->_load_to.' = \\'.__NAMESPACE__.'\Execute_include::get('.$this->_resource.', '.$this->_data.', $template[\'object\']); ?>');
        }

    }

    protected function conditionExecution($condition){
        if ($this->_load_type == 'include'){
            $this->_node->content('<? if ('.$condition.'):  echo \\'.__NAMESPACE__.'\Execute_include::get('.$this->_resource.', '.$this->_data.', $template[\'object\']); else: ?>'.$this->_node->getContent().'<? endif; ?>');
        } elseif ($this->_load_type == 'set'){
            $this->_node->before('<? if ('.$condition.'): '.$this->_load_to.' = \\'.__NAMESPACE__.'\Execute_include::get('.$this->_resource.', '.$this->_data.', $template[\'object\']); endif; ?>');
        }
    }

    public static function get($resource='', $data = null, Environment $template_object){

        $sim = new Environment(array('CachePath' => $template_object->getCachePath()));
        $sim->onDebug($template_object->getDebugStatus());
        $sim->macros = $template_object->macros;
        if ((empty($data))){
            $sim->data = $template_object->data;
        } elseif (is_array($data)){
            $sim->data->set($data);
        }
        $template = $sim->execute($resource, array(), true);
        $template_object->metrics->params($resource, $sim->metrics->get(), 'include');

        return $template;
    }
}

/**
 * Class Execute_resource
 * @package Sim
 */
class Execute_resource extends Code{
    protected $_resource;
    protected $_params;
    protected $_load_type;
    protected $_load_to;
    protected $_resource_cache_time;

    protected function initialize($command){
        if (preg_match('/\s*(resource)\s*\(\s*(.*?)\s*(?:,\s*(\[.*?\]|\$[a-z0-9-_\.]+))?(?:\s+~(set|import) +(\$[a-z0-9-_\.]+))?\s*(?:\s+~cache +(\$[a-z0-9-_\.]+|\d+))?\s*\)\s*$/is',$command,$command_content)){
            $this->_resource = $this->concat($command_content['2']);
            $this->_params = (empty($command_content['3'])) ? 'array()' : $this->concat($command_content['3']);
            $this->_load_type = (empty($command_content['4'])) ? 'include' : $command_content['4'];
            $this->_load_to = (empty($command_content['5'])) ? '' : $this->getVariable($command_content['5']);
            $this->_resource_cache_time = (empty($command_content['6'])) ? 0 : $this->concat($command_content['6']);
        } else return false;
        return true;
    }

    protected function commandExecution(){
        if ($this->_load_type == 'include'){

            /*
             * Выполнение команды подрозумевает замену содердимого (content) сегмента.
             * Соответственно, если команда объявляется ьез условия, то наличие зависимостей (вложенных сегментов),
             * теряет смысл.
             */

            //Получаем списко зависимостей (вложенных сегментов)
            $nested_list = $this->_index_item->get('nested');
            if (!empty($nested_list)){

                //Объодим зависимости и удаляем связанные с ними элементы индекса
                foreach ($nested_list as $nested_id) {
                    $this->_index_controller->remove($nested_id);
                }

                //Отчищаем перечень зависимостей в текущем элементе индекса
                $this->_index_item->clear('nested');
            }

            //Заменяем содержание текущего сегмента
            $this->_node->content('<?= \\'.__NAMESPACE__.'\Execute_resource::get('.$this->_resource.', '.$this->_params.', false, '.$this->_resource_cache_time.', $template[\'object\']->getCachePath()); ?>');

        } elseif ($this->_load_type == 'import'){
            $this->_node->before('<? '.$this->_load_to.' = \\'.__NAMESPACE__.'\Execute_resource::get('.$this->_resource.', '.$this->_params.', true, '.$this->_resource_cache_time.', $template[\'object\']->getCachePath()); ?>');
        } elseif ($this->_load_type == 'set'){
            $this->_node->before('<? '.$this->_load_to.' = \\'.__NAMESPACE__.'\Execute_resource::get('.$this->_resource.', '.$this->_params.', false, '.$this->_resource_cache_time.', $template[\'object\']->getCachePath()); ?>');
        }
    }

    protected function conditionExecution($condition){

        if ($this->_load_type == 'include'){
            $this->_node->content('<? if ('.$condition.'):  echo \\'.__NAMESPACE__.'\Execute_resource::get('.$this->_resource.', '.$this->_params.', false, '.$this->_resource_cache_time.', $template[\'object\']->getCachePath()); else: ?>'.$this->_node->getContent().'<? endif; ?>');
        } elseif ($this->_load_type == 'import'){
            $this->_node->before('<?  if ('.$condition.'): '.$this->_load_to.' = \\'.__NAMESPACE__.'\Execute_resource::get('.$this->_resource.', '.$this->_params.', true, '.$this->_resource_cache_time.', $template[\'object\']->getCachePath()); endif; ?>');
        } elseif ($this->_load_type == 'set'){
            $this->_node->before('<? if ('.$condition.'): '.$this->_load_to.' = \\'.__NAMESPACE__.'\Execute_resource::get('.$this->_resource.', '.$this->_params.', false, '.$this->_resource_cache_time.', $template[\'object\']->getCachePath()); endif; ?>');
        }

    }

    public static function get($resource='', $params = array(), $revert_vars = false, $cache_time=0, $cache_path=''){
        $result = NULL;
        $cache = false;
        $cache_id = false;
        $cache_time = intval($cache_time);

        if (empty($resource)) return NULL;
        if (gettype($params) != 'array') return NULL;
        if (empty($cache_path)) $cache_time = 0;

        if ($cache_time > 0) {
            $cache_id = sha1($resource.serialize($params).(($revert_vars) ? '_vars' : '')).SIM_VERSION;
            $cache = File::exists($cache_path . $cache_id . '.simdata');
        }

        if (!$cache or !$cache_id or ($cache->time(true) > $cache_time)){
            //Проверяем источник рессурса (удаленный/локальный)
            //Если источник удаленный
            if (preg_match('/^(?:http|https):\/\//',$resource)){

                if ($curl = curl_init()) {
                    try {
                        curl_setopt($curl, CURLOPT_URL, $resource);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        if (!empty($params)){
                            curl_setopt($curl, CURLOPT_POST, true);
                            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
                        }
                        $result = curl_exec($curl);
                        curl_close($curl);

                        //Если выполняется экспорт переменных, то пробуем декодировть JSON.
                        if ($revert_vars){
                            $result = json_decode($result, true);
                        }
                    } catch (\Exception $e) {
                        return NULL;
                    }

                } else {
                    return NULL;
                }

            //Если источник локальный
            } else {

                //Если выполняется экспорт переменных
                if ($revert_vars) {

                    $result = function ($resource, $simParams){

                        $parent_buffer = ob_get_contents();
                        ob_end_clean();

                        try {
                            ob_start();
                            include $resource;
                            unset ($resource, $simParams);
                            $vars = get_defined_vars();
                            unset($vars['parent_buffer']);
                            ob_end_clean();
                        } catch (\Exception $e) {
                            $vars = NULL;
                        }

                        ob_start();
                        echo $parent_buffer;

                        return $vars;
                    };
                } else {

                    $result = function($resource, $simParams){

                        $parent_buffer = ob_get_contents();
                        ob_end_clean();

                        try{
                            ob_start();
                            include $resource;
                            unset ($resource, $simParams);
                            $content = ob_get_contents();
                            ob_end_clean();
                        } catch (\Exception $e) {
                            $content = NULL;
                        }

                        ob_start();
                        echo $parent_buffer;

                        return $content;
                    };
                }
                $result = $result($resource, $params);
            }

            //Записываем результаты в кеш
            if ($cache_time>0 and !empty($result)) {
                File::create($cache_path, $cache_id . '.simdata', ($revert_vars) ? serialize($result) : $result);
            }

        } else {

            //Восстанавливаем данные из кеша
            $result = $cache->content();

            //Если выполняется экспорт переменных
            if ($revert_vars) {
                $result = unserialize($result);
            }

        }

        return (empty($result)) ? NULL : $result;
    }
}

/**
 * Class Execute_vardump
 * @package Sim
 */
class Execute_vardump extends Code{

    protected $_source_varible;
    protected $_variable;

    protected function initialize($command){
        if (preg_match('/\s*(vardump)\s*\((.*?)\)\s*$/is',$command,$command_content)){
            $this->_source_varible = trim($command_content['2']);
            $this->_variable = $this->concat($this->_source_varible);
        } else return false;
        return true;
    }

    protected function commandExecution(){
        $this->_node->before('<?'.$this->codeGenerate().'?>');
    }

    protected function conditionExecution($condition){
        $this->_node->before('<? if ('.$condition.'): '.$this->codeGenerate().' endif; ?>');
    }

    protected function codeGenerate(){
        $code = '
            echo \''.str_replace('\'','\\\'',$this->_source_varible).' : \' ;
            echo \'<pre>\';
            var_dump('.$this->_variable.');
            echo \'</pre>\';
        ';
        return $code;
    }
}

/**
 * Class Execute_ignore
 * @package Sim
 */
class Execute_ignore extends Code{

    protected $_variable;
    protected $_value;

    protected function initialize($command){
        if (preg_match('/\s*(ignore)\s*\(\)\s*$/is',$command,$command_content))
            return true;
        else
            return false;
    }

    protected function commandExecution(){
        $nested_list = $this->_index_item->get('nested');
        if (!empty($nested_list)){
            foreach ($nested_list as $nested_id) {
                $this->_index_controller->remove($nested_id);
            }
            $this->_index_item->clear('nested');
        }
        $this->_node->setNode('');
        $this->_index_item->setMark('break_execution_commands',true);
    }

    protected function conditionExecution($condition){
        $this->_node->before('<? if (!('.$condition.')): ?>');
        $this->_node->after('<? endif; ?>');
    }
}

/**
 * Class Execute_usemacro
 * @package Sim
 */
class Execute_usemacro extends Code{

    protected $_macro_name;
    protected $_macro_data;

    protected function initialize($command){
        if (preg_match('/\s*(usemacro)\s*\((.*?)(?:\,(.*?))?\)\s*$/is',$command,$command_content)) {
            $this->_macro_name = $this->concat($command_content['2']);
            $this->_macro_data = (empty($command_content['3'])) ? '[]' : $this->concat($command_content['3']);
        } else return false;
        return true;

    }

    protected function commandExecution(){

        /*
         * Выполнение команды подрозумевает замену содердимого (content) сегмента.
         * Соответственно, если команда объявляется ьез условия, то наличие зависимостей (вложенных сегментов),
         * теряет смысл.
         */

        //Получаем списко зависимостей (вложенных сегментов)
        $nested_list = $this->_index_item->get('nested');
        if (!empty($nested_list)){

            //Объодим зависимости и удаляем связанные с ними элементы индекса
            foreach ($nested_list as $nested_id) {
                $this->_index_controller->remove($nested_id);
            }

            //Отчищаем перечень зависимостей в текущем элементе индекса
            $this->_index_item->clear('nested');
        }

        $this->_node->content('<? echo $template[\'object\']->macros->get('.$this->_macro_name.')->execute((array) '.$this->_macro_data.'); ?>');
    }

    protected function conditionExecution($condition){
        $this->_node->content('<? if ('.$condition.'): echo $template[\'object\']->macros->get('.$this->_macro_name.')->execute((array) '.$this->_macro_data.'); else: ?>'.$this->_node->getContent().'<? endif; ?>');
    }
}

/**
 * Class Execute_interface
 * @package Sim
 */
class Execute_interface extends Code{

    protected $_interface_value;
    protected $_source_value;
    protected $_exception;

    protected function initialize($command){
        if (preg_match('/\s*(interface)\s*\(([a-z0-9_\%\-\=\>\[\],\\\'\s]+)(?:\s+~exception +(.*?))?\)\s*$/is',$command,$command_content)){
            $this->_interface_value = $this->concat(trim($command_content[2]));
            $this->_exception = (empty($command_content[3])) ? '' : trim($command_content[3]);
            $this->_source_value = $this->getVariable('$data');
        } else return false;
        return true;
    }

    protected function commandExecution(){
        if (empty($this->_exception)){
            $this->_node->before('<?'.$this->codeGenerate().';?>');
        } else {
            $this->_index_controller->compileCommandIndex($this->_exception, $this->_index_item, 'NOT '.$this->codeGenerate(false));
        }
    }

    protected function conditionExecution($condition){
        if (empty($this->_exception)) {
            $this->_node->before('<? if (' . $condition . '): ' . $this->codeGenerate() . '; endif; ?>');
        } else {
            $condition = '('.$condition.') AND (NOT '.$this->codeGenerate(false).')';
            $this->_index_controller->compileCommandIndex($this->_exception, $this->_index_item, $condition);
        }
    }

    protected function codeGenerate($show_error=true){
        $code = '\\'.__NAMESPACE__.'\Execute_interface::checkInterface('.$this->_interface_value.', '.$this->_source_value.', '.(($show_error) ? 'true' : 'false').')';
        return $code;
    }

    public static function checkDifferences ($interface, $source){
        $arDiff = array();
        foreach ($interface as $k => $v){
            if (is_array($v)){
                if ($k == '%i'){
                    $res = array();
                    foreach ($source as $iteration){
                        $res = self::checkDifferences($v, $iteration);
                        if (!empty($res)) break;
                    }
                    if (!empty($res)) {
                        $arDiff[$k] = $res;
                    }
                    continue;
                }

                if (!key_exists($k, $source)) {
                    $arDiff[$k] = $v;
                    continue;
                };

                $res = self::checkDifferences($v, $source[$k]);
                if (!empty($res)){
                    $arDiff[$k] = $res;
                }

                unset($res);
            } else {
                if (!key_exists($v, $source)) {
                    $arDiff[] = $v;
                    continue;
                };
            }
        }
        return $arDiff;
    }

    public static function checkInterface($interface, $source, $show_error=true){

        $arDiff = self::checkDifferences($interface, $source);

        if (!empty($arDiff)){
            if ($show_error){
                throw (new Exception('Data does not match the template interface. Below is an array of discrepancies.'))->set('Interface differences', $arDiff, 'array');
            } else {
                return false;
            }
        } else {
            return true;
        }

    }
}

/**
 * Управление файлами
 *
 * Class File
 * @package Sim
 */
class File {

    /**
     * @var string имя файла с которомы работает объект
     */
    private $_filename;

    /**
     * File constructor.
     * @param string $filename имя файла
     * @throws Exception
     */
    function __construct($filename){
        $this->_filename = self::realpath($filename);
    }

    /**
     * Возвращает канонизированный абсолютный путь к файлу
     *
     * @param string $filename проверяемый путь
     * @return string
     * @throws Exception
     */
    static public function realpath($filename){
        $path_result = realpath($filename);
        if ($path_result === false) throw new Exception("Path not found — '$filename'");
        return $path_result;
    }

    /**
     * Возвращает содержание файла
     *
     * @param string $filename Путь к файлу
     * @return string
     * @throws Exception
     */
    static public function get_content($filename){
        $content = file_get_contents($filename);
        if (false === $content) {
            throw new Exception("File not found — '$filename'");
        }
        return $content;
    }

    /**
     * @param string $filename имя файла
     * @return bool|File
     */
    static public function exists($filename){
        try {
            return new File($filename);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Создает файл. Если файл создан будет возвращен объект File,
     * иначе сгенерирована ошибка
     *
     * @param string $path путь к директории в которой необходимо создать файл
     * @param string $filename название создаваемого файла
     * @param string $data содержание файла
     * @return File
     * @throws Exception
     */
    static public function create($path='/', $filename, $data){
        if (!file_exists($path)){
            if (mkdir($path,0777,true) === false) throw new Exception("Error creating directory  '$path' ");
        }
        if (file_put_contents($path.DIRECTORY_SEPARATOR.$filename, $data) === false) throw new Exception("Error creating file '$filename' ");
        return new File($path.DIRECTORY_SEPARATOR.$filename);
    }

    /**
     * Удаляет фаил
     *
     * @throws Exception
     */
    public function remove(){
        if (unlink($this->_filename) === false) throw new Exception("Error deleting file  '$this->_filename' ");
    }

    /**
     * Возвращает путь к файлу
     *
     * @return string
     */
    public function path(){
        return $this->_filename;
    }

    /**
     * Возвращает содержание файла
     *
     * @return string
     * @throws Exception
     */
    public function content(){
        $content = file_get_contents($this->_filename);
        if (false === $content || ("" === $content && is_dir($this->_filename))) {
            throw new Exception("Unable to load file ".$this->_filename);
        }
        return $content;
    }

    /**
     * Возвращает время последнего изменения файла.
     * Время возвращается в формате временной метки Unix
     * Если указан параметр $from (true), то будет время с последнего изменения файла по текущее врямя
     *
     * @param bool $from
     * @return int
     */
    public function time($from = false){
        if (!is_bool($from)) $from = false;
        if ($from){
            return time()-filemtime($this->_filename);
        } else {
            return filemtime($this->_filename);
        }
    }

    /**
     * Сохраняет текущий файл (копирует). Возвращает объект File для новго файла
     * или false если сохранение невозмодно
     *
     * @param string $filename путь куда необходимо сохранить файл
     * @return bool|File
     * @throws Exception
     */
    public function save($filename){
        if ($filename = realpath($filename)){
            file_put_contents($filename,$this->content());
            if (file_put_contents($filename, $this->content()) === false) throw new Exception("Error creating file '$filename' ");
            return new File($filename);
        }
        return false;
    }
}

/**
 * Сервисные функции
 *
 * Class Procedure
 * @package Sim
 */
class Procedure {

    final static public function get_document_root(){
        $result = false;
        try {
            if (empty($_SERVER['DOCUMENT_ROOT'])) throw new Exception('Variable $_SERVER[\'DOCUMENT_ROOT\'] is empty');
            if (!($result = realpath($_SERVER['DOCUMENT_ROOT']))) throw new Exception('Directory from variable $_SERVER[\'DOCUMENT_ROOT\'] not found');
        } catch (Exception $e) {
            $e->showError();
        }
        return $result;
    }

    /**
     * Проверяет, содержатся ли все элементы из массива ключей в родительском массиве (в случае успеха вернет true)
     *
     * @param array $keys массив ключей
     * @param array $array родительский массив
     * @param bool $isset параметр $isset=true будет игнорировать пустые элементы родительского массива, даже если его ключ указан в массиве ключей
     * @return bool
     */
    final static public function array_keys_exists(array $keys, array $array, $isset=false){
        if (empty($keys)) return false;
        if (empty($array)) return false;
        foreach ($keys as $k){
            if (!array_key_exists($k,$array)) return false;
            if ($isset and (!isset($array[$k]) or empty($array[$k]))) return false;
        }
        return true;
    }

    /**
     * Возвращет элементы массива ключи которых содержатся в массиве ключей
     *
     * @param array $keys массив ключей
     * @param array $array родительский массив
     * @param bool $isset параметр $isset=true будет игнорировать пустые элементы родительского массива, даже если его ключ указан в массиве ключей
     * @return array
     */
    final static public function array_find_keys(array $keys, array $array, $isset=false){
        $arResult=array();
        if (empty($keys)) return $arResult;
        if (empty($array)) return $arResult;
        foreach($keys as $k){
            if (!array_key_exists($k,$array)) continue;
            if ($isset and empty($array[$k])) continue;
            $arResult[$k]=$array[$k];
        }
        return $arResult;
    }

    /**
     * Выполняет замену первого элемента
     *
     * @param $search
     * @param $replace
     * @param $text
     * @return string
     */
    final static public function str_replace_once($search, $replace, $text){
        return implode($replace, explode($search, $text, 2));
    }

    /**
     * Обработчик вызовов из автозагрузки SPL
     *
     * @param $class
     */
    final public static function autoload($class) {
        $class = str_replace(__NAMESPACE__.'\\', '', $class);
        $path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Extension' . DIRECTORY_SEPARATOR . $class . '.php';
        @include_once $path;
    }

    /**
     * Регаистрация обработчик автозагрузки классов
     * Для использования собственного автозагрузчика для шаблонизатора, используйте:
     * spl_autoload_unregister(array('Sim\Procedure','autoload'));
     */
    final static public function autoload_register(){

        //spl_autoload_register отключает автозагрузку oldschool,
        //даже если он был добавлен с помощью spl_autoload_register!
        //Для этого сохраняем старый автозагрузкик, для последующего использования
        $uses_autoload = function_exists('__autoload') && (!($tmp = spl_autoload_functions()) || ($tmp[0] === '__autoload'));

        //Регистрируем функуию автозагрузки
        spl_autoload_register(array(__CLASS__, 'autoload'));

        //Регистрируем старый автозагрузкич если он передан
        if ($uses_autoload) spl_autoload_register('__autoload');
    }

}

/**
 * Исключения
 *
 * Class Exception
 * @package Sim
 */
class Exception  extends \Exception {

    protected $_content = array();

    final function set($name, $message, $type = 'string'){
        switch ($type){
            case 'html':
                $this->_content[] = '<br><b>'.$name.':</b> <xmp class="sim_debug">'.$message.'</xmp>';
                break;
            case 'array':
                function arr_to_str($array, $tab = ''){
                    $result = $tab.'array('."\n";
                    $tab_ = $tab."&nbsp;&nbsp;";
                    foreach ($array as $k => $v){
                        if (is_array($v)){
                            $result.= $tab_.'['.$k.'] => '."\n".arr_to_str($v, $tab_."&nbsp;&nbsp;")."\n";
                        } else {
                            $result.= $tab_.'['.$k.'] => '.$v."\n";
                        }
                    }
                    $result.= $tab.')';
                    return $result;
                }
                $this->_content[] = '<br><b>'.$name.':</b> <pre class="sim_debug">'.arr_to_str($message).'</pre>'; break;
                break;
            default:
                $this->_content[] = '<br><b>'.$name.':</b> '.$message;
        }
        return $this;
    }

    final function showError(){
        echo '<style>
                .sim_debug {
                    background-color: #f2efef; 
                    border: 1px solid #b6b6b6; 
                    padding: 20px; 
                    max-width: 800px; 
                    max-height: 100px; 
                    overflow: hidden;
                    transition: 0.3s;
                }
                .sim_debug:hover {
                    background-color: #f2efef; 
                    max-height: 600px; 
                    overflow: scroll;
                    transition: 1s;
                }
              </style>
              <p><b>Sim Error:</b> '.$this->message.'</p>';
        if (!empty($this->_content)){
            foreach ($this->_content as $content_item){
                echo $content_item;
            }
        }
        die();
    }
}