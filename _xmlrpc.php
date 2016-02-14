<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of typo, a plugin for Dotclear 2.
#
# Copyright (c) Franck Paul and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

require_once dirname(__FILE__).'/inc/smartypants.php';

/* Add behavior callback, will be used for all types of posts (standard, page, galery item, ...) */
$core->addBehavior('coreAfterPostContentFormat',array('xmlrpcTypo','updateTypoEntries'));

/* Add behavior callbacks, will be used for all comments (not trackbacks) */
$core->addBehavior('coreBeforeCommentCreate',array('xmlrpcTypo','updateTypoComments'));
$core->addBehavior('coreBeforeCommentUpdate',array('xmlrpcTypo','updateTypoComments'));

class xmlrpcTypo
{
	public static function updateTypoEntries($ref)
	{
		global $core;
		if ($core->blog->settings->typo->typo_active && $core->blog->settings->typo->typo_entries) {
			if (@is_array($ref)) {
				$dashes_mode = (integer)$core->blog->settings->typo->typo_dashes_mode;
				/* Transform typo for excerpt (XHTML) */
				if (isset($ref['excerpt_xhtml'])) {
					$excerpt = &$ref['excerpt_xhtml'];
					if ($excerpt) {
						$excerpt = SmartyPants($excerpt,($dashes_mode ? $dashes_mode : SMARTYPANTS_ATTR));
					}
				}
				/* Transform typo for content (XHTML) */
				if (isset($ref['content_xhtml'])) {
					$content = &$ref['content_xhtml'];
					if ($content) {
						$content = SmartyPants($content,($dashes_mode ? $dashes_mode : SMARTYPANTS_ATTR));
					}
				}
			}
		}
	}

	public static function updateTypoComments($blog,$cur)
	{
		global $core;
		if ($core->blog->settings->typo->typo_active && $core->blog->settings->typo->typo_comments)
		{
			/* Transform typo for comment content (XHTML) */
			if (!(boolean)$cur->comment_trackback) {
				if ($cur->comment_content != null) {
					$dashes_mode = (integer)$core->blog->settings->typo->typo_dashes_mode;
					$cur->comment_content = SmartyPants($cur->comment_content,($dashes_mode ? $dashes_mode : SMARTYPANTS_ATTR));
				}
			}
		}
	}
}