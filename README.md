# php-viewlayer
Render PHP view scripts into a PSR-7 Response object.

```
<?php

require __DIR__ . '/vendor/autoload.php';

$settings = [
	'template_path'	=> __DIR__ . '/templates'
];


$renderer = new \LianzhView\Renderer($settings['template_path']);


```