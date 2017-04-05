<?php

$finder = PhpCsFixer\Finder::create()
    ->notPath('var')
    ->notPath('app/*')
    ->notPath('bin/*')
    ->notPath('build')
    ->notPath('node_modules')
    ->notPath('web/*')
    ->notPath('storage')
    ->notPath('vendor')
    ->notPath('app/Swagger')
    ->notName('server.php')
    ->in(__DIR__)
    ->name('*.php')
    ->notName('*.twig')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return PhpCsFixer\Config::create()
    ->setFinder($finder)
    ->setRiskyAllowed(false)
    ->setRules([
        '@Symfony' => true,
        '@PSR2' => true,
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => ['align_equals' => true, 'align_double_arrow' => true],
        'heredoc_to_nowdoc' => true,
        'no_multiline_whitespace_before_semicolons' => true,
//        'no_unreachable_default_argument_value' => true,
        'no_unused_imports' => true,
        'no_useless_return' => true,
        'not_operator_with_successor_space' => true,
        'ordered_imports' => true,
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_order' => true,
        'unary_operator_spaces' => false
    ]
);
