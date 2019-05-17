<?php

namespace WGCS\Sniffs;

/**
 * Class TSniffs
 *
 * @package WGCS\Sniffs
 */
trait TSniffs
{
	
	public function checkSkip(array $tokens)
	{
		foreach ($tokens as $token) {
			if ($token['type'] === "T_DOC_COMMENT_STRING" && in_array($token["content"], self::SKIP_VALUE)) {
				return true;
			}
		}
		
		return false;
	}
	
}
