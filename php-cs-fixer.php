<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create();
$finder->exclude('*');



return (new Config())
    ->setRules([
        '@PSR12' => true,          // użyj standardu PSR-12
        'array_syntax' => ['syntax' => 'short'], // preferuj [] zamiast array()
        'no_unused_imports' => true, 
        'ordered_imports' => true,
    ])
    ->setFinder($finder);
