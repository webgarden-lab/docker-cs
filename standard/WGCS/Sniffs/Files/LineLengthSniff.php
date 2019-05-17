<?php

namespace WGCS\Sniffs\Files;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer_File;
use WGCS\Sniffs\TSniffs;

/**
 * Generic_Sniffs_Files_LineLengthSniff.
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

/**
 * Generic_Sniffs_Files_LineLengthSniff.
 *
 * Checks all lines in the file, and throws warnings if they are over 80
 * characters in length and errors if they are over 100. Both these
 * figures can be changed by extending this sniff in your own standard.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Class LineLengthSniff
 *
 * @package WGCS\Sniffs\Files
 *
 * Exported from Generic sniff
 */
class LineLengthSniff implements Sniff
{
	use TSniffs;
	
	const SKIP_VALUE
		= [
			"!skipLineLength",
			"!skipSniff",
		];
	
	/**
	 * The limit that the length of a line should not exceed.
	 *
	 * @var int
	 */
	public $lineLimit = 160;
	
	/**
	 * The limit that the length of a line must not exceed.
	 *
	 * Set to zero (0) to disable.
	 *
	 * @var int
	 */
	public $absoluteLineLimit = 200;
	
	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register()
	{
		return [T_OPEN_TAG];
		
	}//end register()
	
	/**
	 * @param PHP_CodeSniffer_File $phpcsFile
	 * @param int $stackPtr
	 *
	 * @return mixed
	 */
	public function process(File $phpcsFile, $stackPtr)
	{
		$tokens = $phpcsFile->getTokens();
		
		if ($this->checkSkip($tokens)) {
			return false;
		}
		
		for ($i = 1; $i < $phpcsFile->numTokens; $i++) {
			if ($tokens[$i]['column'] === 1) {
				$this->checkLineLength($phpcsFile, $tokens, $i);
			}
		}
		
		$this->checkLineLength($phpcsFile, $tokens, $i);
		
		// Ignore the rest of the file.
		return ($phpcsFile->numTokens + 1);
		
	}//end process()
	
	/**
	 * Checks if a line is too long.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 * @param array $tokens The token stack.
	 * @param int $stackPtr The first token on the next line.
	 *
	 * @return null|false
	 */
	protected function checkLineLength(File $phpcsFile, $tokens, $stackPtr)
	{
		// The passed token is the first on the line.
		$stackPtr--;
		
		if ($tokens[$stackPtr]['column'] === 1
			&& $tokens[$stackPtr]['length'] === 0
		) {
			// Blank line.
			return;
		}
		
		if ($tokens[$stackPtr]['column'] !== 1
			&& $tokens[$stackPtr]['content'] === $phpcsFile->eolChar
		) {
			$stackPtr--;
		}
		
		$lineLength = ($tokens[$stackPtr]['column'] + $tokens[$stackPtr]['length'] - 1);
		
		// Record metrics for common line length groupings.
		if ($lineLength <= 80) {
			$phpcsFile->recordMetric($stackPtr, 'Line length', '80 or less');
		} elseif ($lineLength <= 120) {
			$phpcsFile->recordMetric($stackPtr, 'Line length', '81-120');
		} elseif ($lineLength <= 150) {
			$phpcsFile->recordMetric($stackPtr, 'Line length', '121-150');
		} else {
			$phpcsFile->recordMetric($stackPtr, 'Line length', '151 or more');
		}
		
		// If this is a long comment, check if it can be broken up onto multiple lines.
		// Some comments contain unbreakable strings like URLs and so it makes sense
		// to ignore the line length in these cases if the URL would be longer than the max
		// line length once you indent it to the correct level.
		if ($lineLength > $this->lineLimit
			&& ($tokens[$stackPtr]['code'] === T_COMMENT
				|| $tokens[$stackPtr]['code'] === T_DOC_COMMENT_STRING)
		) {
			$oldLength = strlen($tokens[$stackPtr]['content']);
			$newLength = strlen(ltrim($tokens[$stackPtr]['content'], "/#\t "));
			$indent = (($tokens[$stackPtr]['column'] - 1) + ($oldLength - $newLength));
			
			$nonBreakingLength = $tokens[$stackPtr]['length'];
			
			$space = strrpos($tokens[$stackPtr]['content'], ' ');
			if ($space !== false) {
				$nonBreakingLength -= ($space + 1);
			}
			
			if (($nonBreakingLength + $indent) > $this->lineLimit) {
				return;
			}
		}
		
		if ($this->absoluteLineLimit > 0
			&& $lineLength > $this->absoluteLineLimit
		) {
			$data = [
				$this->absoluteLineLimit,
				$lineLength,
			];
			
			$error = 'Line exceeds maximum limit of %s characters; contains %s characters';
			$phpcsFile->addError($error, $stackPtr, 'MaxExceeded', $data);
		} elseif ($lineLength > $this->lineLimit) {
			$data = [
				$this->lineLimit,
				$lineLength,
			];
			
			$warning = 'Line exceeds %s characters; contains %s characters';
			$phpcsFile->addWarning($warning, $stackPtr, 'TooLong', $data);
		}
		
	}
	
}
