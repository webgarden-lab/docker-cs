<?php

namespace WGCS\Sniffs\Metrics;

/**
 * Checks the cyclomatic complexity (McCabe) for functions.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer_File;
use WGCS\Sniffs\TSniffs;

/**
 * Checks the cyclomatic complexity (McCabe) for functions.
 *
 * The cyclomatic complexity (also called McCabe code metrics)
 * indicates the complexity within a function by counting
 * the different paths the function includes.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Johann-Peter Hartmann <hartmann@mayflower.de>
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2007-2014 Mayflower GmbH
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class CyclomaticComplexitySniff implements Sniff
{
	use TSniffs;
	
	const SKIP_VALUE
		= [
			"!skipSniff",
			"!skipCyclomaticComplexity",
		];
	
	/**
	 * A complexity higher than this value will throw a warning.
	 *
	 * @var int
	 */
	public $complexity = 15;
	
	/**
	 * A complexity higer than this value will throw an error.
	 *
	 * @var int
	 */
	public $absoluteComplexity = 20;
	
	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register()
	{
		return [T_FUNCTION];
		
	}//end register()
	
	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 * @param int $stackPtr The position of the current token
	 *                                        in the stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process(File $phpcsFile, $stackPtr)
	{
		$this->currentFile = $phpcsFile;
		
		$tokens = $phpcsFile->getTokens();
		
		if ($this->checkSkip($tokens)) {
			return;
		}
		
		// Ignore abstract methods.
		if (isset($tokens[$stackPtr]['scope_opener']) === false) {
			return;
		}
		
		// Detect start and end of this function definition.
		$start = $tokens[$stackPtr]['scope_opener'];
		$end = $tokens[$stackPtr]['scope_closer'];
		
		// Predicate nodes for PHP.
		$find = [
			T_CASE => true,
			T_DEFAULT => true,
			T_CATCH => true,
			T_IF => true,
			T_FOR => true,
			T_FOREACH => true,
			T_WHILE => true,
			T_DO => true,
			T_ELSEIF => true,
		];
		
		$complexity = 1;
		
		// Iterate from start to end and count predicate nodes.
		for ($i = ($start + 1); $i < $end; $i++) {
			if (isset($find[$tokens[$i]['code']]) === true) {
				$complexity++;
			}
		}
		
		if ($complexity > $this->absoluteComplexity) {
			$error = 'Function\'s cyclomatic complexity (%s) exceeds allowed maximum of %s';
			$data = [
				$complexity,
				$this->absoluteComplexity,
			];
			$phpcsFile->addError($error, $stackPtr, 'MaxExceeded', $data);
		} elseif ($complexity > $this->complexity) {
			$warning = 'Function\'s cyclomatic complexity (%s) exceeds %s; consider refactoring the function';
			$data = [
				$complexity,
				$this->complexity,
			];
			$phpcsFile->addWarning($warning, $stackPtr, 'TooHigh', $data);
		}
		
	}//end process()
	
}//end class
