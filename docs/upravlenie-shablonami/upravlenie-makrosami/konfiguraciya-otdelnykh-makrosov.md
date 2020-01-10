# Конфигурация отдельных макросов

Настройка конфигураций, также может быть проведена отдельно для каждого макроса. Перечень и синтаксис методов позволяющих выполнить такую настройку аналогичен базовому классу шаблонизатора

Пример настройки параметров для отдельных макросов:

```php
$Sim = new \SimTemplate\Sim();
$Sim->macros->add('nocontent','nocontent.html');
	$Sim->macros->get('nocontent')->setConfiguration(array(
     RootURL => '/templates_nocontent/',
     RootPath => '/home/public_html/templates_nocontent/',
     CachePath => '/home/public_html/cache/'
));

$Sim->macros->add(error,'error.html');
$Sim->macros->get('error')->setRootURL('/templates_error/');
$Sim->macros->get('error')->setRootPath('/home/public_html/templates_error/');
$Sim->macros->get('error')->setCachePath('/home/public_html/cache/');
```

