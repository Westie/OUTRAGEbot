<?php

$finder = (new PhpCsFixer\Finder())
    ->path('.php-cs-fixer.php')
    ->path('src/')
    ->in(__DIR__);

$rules = [
    '@PSR12' => true,
    'array_syntax' => [
        'syntax' => 'short'
    ],
    'array_indentation' => true,
    'concat_space' => [
        'spacing' => 'one',
    ],
    'class_attributes_separation' => [
        'elements' => [
            'const' => 'one',
            'method' => 'one',
            'property' => 'one',
        ],
    ],
    'operator_linebreak' => [
        'only_booleans' => true,
        'position' => 'end',
    ],
    'ordered_class_elements' => [
        'order' => [
            'use_trait',
        ],
    ],
    'ordered_imports' => [
        'imports_order' => [
            'class',
            'function',
            'const',
        ],
        'sort_algorithm' => 'alpha',
    ],
    'no_extra_blank_lines' => [
        'tokens' => [
            'curly_brace_block',
            'extra',
            'use',
            'use_trait',
        ],
    ],
    'no_multiline_whitespace_around_double_arrow' => true,
    'no_trailing_comma_in_singleline_array' => true,
    'no_unused_imports' => true,
    'no_whitespace_before_comma_in_array' => true,
    'single_quote' => true,
    'single_space_after_construct' => true,
    'whitespace_after_comma_in_array' => true,
];

$config = (new PhpCsFixer\Config())
    ->setRules($rules)
    ->setFinder($finder);

return $config;
