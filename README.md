phpHue
======

PHP classes for interacting with the Phillips Hue lighting system.

Getting started
---------------

If you want to include this in your own application, just load the ```HueBridge``` class which handles the communication with the Hue bridge.

Sample:

```php
<?php
require('phpHue/HueBridge.php');

$bridge = null;

try {
    $bridge = new HueBridge('hue.veloc1ty.lan', 'yourapikeygoesgere');
}
catch ( InvalidArgumentException $ex ) {
    echo('Oh noes. The provided values makes no sense!');
}

?>
```

If something f
