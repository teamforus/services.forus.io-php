<?php

namespace App\Services\Markdown\inline;

// work around https://github.com/facebook/hhvm/issues/1120
defined('ENT_HTML401') || define('ENT_HTML401', 0);

/**
 * Adds auto linking for URLs
 */
trait UrlLinkTrait
{
	/**
	 * Parses urls and adds auto linking feature.
	 * @marker http
	 * @marker ftp
	 */
	protected function parseUrl($markdown)
	{
		$pattern = <<<REGEXP
			/(?(R) # in case of recursion match parentheses
				 \(((?>[^\s()]+)|(?R))*\)
			|      # else match a link with title
				^(https?|ftp):\/\/(([^\s<>()]+)|(?R))+(?<![\.,:;\'"!\?\s])
			)/x
REGEXP;

		if (!in_array('parseLink', $this->context) && preg_match($pattern, $markdown, $matches)) {
			return [
				['autoUrl', $matches[0]],
				strlen($matches[0])
			];
		}
		return [['text', substr($markdown, 0, 4)], 4];
	}

	protected function renderAutoUrl($block)
	{
		$href = htmlspecialchars($block[1], ENT_COMPAT | ENT_HTML401, 'UTF-8');
		$decodedUrl = urldecode($block[1]);
		$secureUrlText = preg_match('//u', $decodedUrl) ? $decodedUrl : $block[1];
		$text = htmlspecialchars($secureUrlText, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');
		return "<a href=\"$href\">$text</a>";
	}
}
