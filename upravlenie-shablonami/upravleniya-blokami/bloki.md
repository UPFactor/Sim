# Определение блока

Блоки реализуют механизм пространства имен внутри шаблона, что позволит сгруппировать логически связанные массивы данных, исключив необходимость использования длинной цепочки вложенности.

{% hint style="info" %}
Блоки являются непосредственной частью шаблона и обозначаются специальной разметкой.
{% endhint %}

**Определения границ блока в шаблоне:**

```markup
<!--block:my_header_block-->
<header>
     <h1 data-sim="content($data.title);"> … </h1>
     <p>
          Автор: <b data-sim="content($data.author);"> … </b>
     </p>
</header>
<!--end:my_header_block-->
```

**Передача данных в блок:**

```php
$Sim = new \SimTemplate\Sim();
$Sim->data->block('my_header_block')->add(array(
    'title'=>'Page Title',
    'author'=>'Page author'
));

… 

$sim->execute('my_template.html');
```

Внутри блока `my_header_block` переменные могут вызываться именно так, как они были объявлены — `$data.title`. Но вне блока требуется указание полного пути. Например, вне блока `my_header_block` идентификатор `title` должен указываться как `$block.my_header_block.title` или `$root.block.my_header_block.title`.

{% hint style="info" %}
Использование блоков, структурирует страницу и позволит закрепить за каждой ее частью собственный обработчик данных.
{% endhint %}

