<?php

namespace Gesof\MakerBundle\Helper;

class ExtraValidator
{
	public static function validateBundleNamespace($namespace)
	{
		if (!preg_match('/^[a-z][a-z0-9_\\\]*$/i', $namespace)) {
			throw new \InvalidArgumentException('The bundle namespace contains invalid characters.');
		}

		if (!preg_match('/Bundle$/', $namespace)) {
			throw new \InvalidArgumentException('The bundle namespace must end with "Bundle".');
		}

		// validate reserved keywords
		$reservedWords = self::getPhpReservedWords();

		foreach (explode('\\', $namespace) as $word) {
			if (in_array(strtolower($word), $reservedWords)) {
				throw new \InvalidArgumentException(sprintf('The bundle namespace cannot contain PHP reserved words: "%s".', $word));
			}
		}

		return $namespace;
	}

	/**
	 * @return string[]
	 */
	public static function getPhpReservedWords()
	{
		return array(
			'__CLASS__',
			'__DIR__',
			'__FILE__',
			'__FUNCTION__',
			'__LINE__',
			'__METHOD__',
			'__NAMESPACE__',
			'__TRAIT__',
			'__halt_compiler',
			'abstract',
			'and',
			'array',
			'as',
			'break',
			'callable',
			'case',
			'catch',
			'class',
			'clone',
			'const',
			'continue',
			'declare',
			'default',
			'die',
			'do',
			'echo',
			'else',
			'elseif',
			'empty',
			'enddeclare',
			'endfor',
			'endforeach',
			'endif',
			'endswitch',
			'endwhile',
			'eval',
			'exit',
			'extends',
			'final',
			'finally',
			'fn',
			'for',
			'foreach',
			'function',
			'global',
			'goto',
			'if',
			'implements',
			'include',
			'include_once',
			'instanceof',
			'insteadof',
			'interface',
			'isset',
			'list',
			'match',
			'namespace',
			'new',
			'or',
			'print',
			'private',
			'protected',
			'public',
			'require',
			'require_once',
			'return',
			'static',
			'switch',
			'throw',
			'trait',
			'try',
			'unset',
			'use',
			'var',
			'while',
			'xor',
			'yield',
		);
	}
}