<?php

/*
 * janitor (http://juliangut.com/janitor).
 * Effortless maintenance management.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/janitor
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

use Symfony\CS\Config;
use Symfony\CS\Finder;
use Symfony\CS\Fixer\Contrib\HeaderCommentFixer;
use Symfony\CS\FixerInterface;

$header = <<<'HEADER'
janitor (http://juliangut.com/janitor).
Effortless maintenance management.

@license BSD-3-Clause
@link https://github.com/juliangut/janitor
@author Julián Gutiérrez <juliangut@gmail.com>
HEADER;

HeaderCommentFixer::setHeader($header);

$finder = Finder::create()
    ->files()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->name('*.php');

return Config::create()
    ->level(FixerInterface::PSR2_LEVEL)
    ->setUsingCache(true)
    ->fixers([
        'short_tag',
        'header_comment',
        'single_blank_line_before_namespace',
        'unused_use',
        'ordered_use',
        'remove_leading_slash_use',
        'remove_lines_between_uses',
        'include',
        'self_accessor',
        'native_function_casing',
        'newline_after_open_tag',
        'whitespacy_lines',
        'multiline_spaces_before_semicolon',
        'spaces_before_semicolon',
        'ereg_to_preg',
        'empty_return',
        'unneeded_control_parentheses',
        'unary_operators_spaces',
        'function_typehint_space',
        'method_argument_default_value',
        'trim_array_spaces',
        'array_element_white_space_after_comma',
        'short_array_syntax',
        'multiline_array_trailing_comma',
        'single_array_no_trailing_comma',
        'single_quote',
        'spaces_cast',
        'short_scalar_cast ',
        'concat_with_spaces',
        'no_useless_else',
        'no_empty_comment',
        'phpdoc_indent',
        'phpdoc_trim',
        'phpdoc_to_comment',
        'phpdoc_short_description',
        'phpdoc_inline_tag',
        'phpdoc_no_access',
        'phpdoc_no_package',
        'phpdoc_order',
        'phpdoc_separation',
        'phpdoc_var_without_name',
        'phpdoc_types',
        'phpdoc_type_to_var',
        'phpdoc_scalar',
        'phpdoc_single_line_var_spacing',
        'phpdoc_params',
        'phpdoc_no_empty_return',
        'no_empty_lines_after_phpdocs',
        'php_unit_construct',
        'php_unit_dedicate_assert'
    ])
    ->finder($finder);
