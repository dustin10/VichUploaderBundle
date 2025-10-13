<?php
// see https://github.com/FriendsOfPHP/PHP-CS-Fixer

$finder = (new PhpCsFixer\Finder())
    ->in([__DIR__.'/config', __DIR__.'/src', __DIR__.'/tests'])
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP8x1Migration' => true,
        '@PHPUnit10x0Migration:risky' => true,
        'declare_strict_types' => false,
        'native_function_invocation' => [
            'include' => ['@internal'],
        ],
        'fopen_flags' => ['b_mode' => true],
        'php_unit_mock_short_will_return' => true,
        'new_with_parentheses' => true,
    ])
    ->setFinder($finder)
;
