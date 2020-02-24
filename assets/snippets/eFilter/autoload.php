<?php
spl_autoload_register(
    function($class) {
        static $classes = null;
        if ($classes === null) {
            $classes = [
                '\\DLTemplate' => MODX_BASE_PATH . 'assets/snippets/DocLister/lib/DLTemplate.class.php',
                '\\Helpers\\Config' => MODX_BASE_PATH . 'assets/lib/Helpers/Config.php',
                '\\modResource' => MODX_BASE_PATH . 'assets/lib/MODxAPI/modResource.php'
            ];
        }
        if (isset($classes[$class])) {
            require __DIR__ . $classes[$class];
        } else {
            if (strpos($class, 'eFilter\\') === 0) {
                $path = implode('/', array_slice(explode('\\', $class), 1));
                switch (true) {
                    case is_readable (__DIR__ . '/' . $path . '.php'):
                        require __DIR__ . '/' . $path . '.php';
                        break;
                    case is_readable (__DIR__ . '/src/' . $path . '.php'):
                        require __DIR__ . '/src/' . $path . '.php';
                        break;
                    default:
                        break;
                }
            }
            
        }
    }, true, false
);
