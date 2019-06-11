<?php
// see https://github.com/FriendsOfPHP/PHP-CS-Fixer

$dirs = [
    __DIR__.'/Adapter',
    __DIR__.'/Command',
    __DIR__.'/DataCollector',
    __DIR__.'/DependencyInjection',
    __DIR__.'/Entity',
    __DIR__.'/Event',
    __DIR__.'/EventListener',
    __DIR__.'/Exception',
    __DIR__.'/Form',
    __DIR__.'/Handler',
    __DIR__.'/Injector',
    __DIR__.'/Mapping',
    __DIR__.'/Metadata',
    __DIR__.'/Naming',
    __DIR__.'/Storage',
    __DIR__.'/Tests',
    __DIR__.'/Twig',
    __DIR__.'/Util',
];

$finder = PhpCsFixer\Finder::create()
    ->in($dirs)
;

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP71Migration:risky' => true,
        '@PHPUnit60Migration:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => true,
        'declare_strict_types' => false,
        'native_function_invocation' => true,
        'fopen_flags' => ['b_mode' => true],
        'php_unit_mock_short_will_return' => true,
    ])
    ->setFinder($finder)
;
