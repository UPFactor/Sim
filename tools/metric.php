<?php
$page = <<<'EOD'
<!DOCTYPE html>
<html>
<head>
    <title>SimTemplate Metric</title>
	<meta charset="utf-8">
    <style type="text/css">
        p.h1 {
            font-weight: bold;
            font-size: 130%;
        }
        p.h2 {
            font-weight: bold;
            font-size: 110%;
            margin-top: 40px;
        }
        table {
            border-collapse: collapse;
            border-spacing: 0;
        }
        table th, td.th {
            background-color: #f5f5f5;
            text-align: left;
            font-weight: bold;
        }
        table td, table th {
            border: 1px solid #ececec;
            min-width: 150px;
            padding: 5px;
        }
        div.content {
            padding-left:30px;
            margin-left: 20px;
            border-left:3px solid #000000;
        }
        div.command_list {
            border: 1px solid #ececec;
            padding:10px;
            max-width: 800px; 
            max-height: 400px; 
            overflow: auto;
            transition: 0.3s;
        }
    </style>
</head>
<body>
    <p  class="h1">SimTemplate Metric</p>
    <section data-sim="usemacro('content', $data);"></section>
</body>
</html>
EOD;

$content = <<<'EOD'
<div>
    <div>
        <p class="h2">Statistics</p>
        <table>
            <tr>
                <th></th>
                <th>Time of processing</th>
                <th>Allocated memory/peak</th>
            </tr>
            <tr>
                <td>Total </td>
                <td data-sim="content($data.time:round{6}+'s.')"></td>
                <td data-sim="content($data.memory:round{3}+'Kb. / '+$data.memory_peak:round{3}+'Kb.')"></td>
            </tr>
            <tr data-sim="repeat($data.log as $log);">
                <td data-sim="content($log.item.description)"></td>
                <td data-sim="content($log.item.result.time:round{6}+'s.')"></td>
                <td data-sim="content($log.item.result.memory:round{3}+'Kb.')"></td>
            </tr>
        </table>
    </div>
    
    <div>
        <p class="h2">Execution Options</p>
        <table>
            <tr data-sim="repeat($data.params.main as $option)">
                <td class="th" data-sim="content($option.item.name+': ')"></td>
                <td data-sim="content($option.item.params)"></td>
            </tr>
        </table>
    </div>
        
    <div data-sim="if ($data.params.command:empty ? ignore())">
        <p class="h2">Sequence of operators</p>
        <div class="command_list" data-sim="set($padding, 0); foreach($data.params.command as $command);">
            <!--? if ($command['item']['params'] == 'done') $padding-=10; ?--> 
            <p data-sim="
                attr('style', 'padding-left:'+$padding+'px\;');
                content($command.item.name+' — '+$command.item.params);
            "></p>
            <!--? if ($command['item']['params'] == 'execute') $padding+=10; ?-->    
        </div>
    </div>
    
    <div data-sim="if($data.params.macros:empty ? ignore());">
        <p class="h2">Used macros</p>
        <div class="content" data-sim="repeat($data.params.macros as $macro);">
        <p class="h2" data-sim="content($macro.item.name);"></p>
        <div data-sim="usemacro('content', $macro.item.params);"></div>
        </div>
    </div>
    
    <div data-sim="if($data.params.include:empty ? ignore());">
        <p class="h2">Used inclusions</p>
        <div class="content" data-sim="repeat($data.params.include as $include);">
        <p class="h2" data-sim="content($include.item.name);"></p>
        <div data-sim="usemacro('content', $include.item.params);"></div>
        </div>
    </div>
</div>
EOD;

$data = json_decode($_REQUEST['data'], true);
require_once($data['sim']);
$sim = new \Sim\Environment();
$sim->macros->add('content', $content);
$sim->execute($page, $data['metric']);
