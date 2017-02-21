<?php

/*
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$header = <<<'EOF'
This file is part of the GraphAware Neo4j Client package.

(c) GraphAware Limited <http://graphaware.com>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/src/')
    ->in(__DIR__.'/tests/')
;

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,

        'array_syntax' => ['syntax' => 'short'],
        'header_comment' => ['header' => $header],
        'linebreak_after_opening_tag' => true,
        'ordered_imports' => true,
        'phpdoc_order' => true,

        // 'modernize_types_casting' => true,
        // 'no_useless_return' => true,
        // 'phpdoc_add_missing_param_annotation' => true,
        // 'protected_to_private' => true,
        // 'strict_param' => true,
    ])
    ->setFinder($finder)
;
