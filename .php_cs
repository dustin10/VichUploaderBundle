<?php
// see https://github.com/FriendsOfPHP/PHP-CS-Fixer

$finder = PhpCsFixer\Finder::create()
    ->in([__DIR__.'/Command', __DIR__.'/DependencyInjection', __DIR__.'/Form', __DIR__.'/Tests', __DIR__.'/Twig', __DIR__.'/Util'])
;

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => true,
    ])
    ->setFinder($finder)
;
