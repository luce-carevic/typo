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

if (!defined('DC_CONTEXT_ADMIN')) { return; }

// dead but useful code, in order to have translations
__('Typo').__('Brings smart typographic replacements for your blog entries and comments');

require_once dirname(__FILE__).'/inc/smartypants.php';

/* Add behavior callback, will be used for all types of posts (standard, page, galery item, ...) */
$core->addBehavior('coreAfterPostContentFormat',array('adminTypo','updateTypoEntries'));

/* Add behavior callbacks, will be used for all comments (not trackbacks) */
$core->addBehavior('coreBeforeCommentCreate',array('adminTypo','updateTypoComments'));
$core->addBehavior('coreBeforeCommentUpdate',array('adminTypo','updateTypoComments'));

/* Add menu item in extension list */
$_menu['Blog']->addItem(__('Typographic replacements'),
		'plugin.php?p=typo',
		urldecode(dcPage::getPF('typo/icon.png')),
		preg_match('/plugin.php\?p=typo(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('contentadmin',$core->blog->id));

/* Register favorite */
$core->addBehavior('adminDashboardFavorites',array('adminTypo','adminDashboardFavorites'));

/* Add behavior callbacks for posts actions */
$core->addBehavior('adminPostsActionsPage',array('adminTypo','adminPostsActionsPage'));
$core->addBehavior('adminPagesActionsPage',array('adminTypo','adminPagesActionsPage'));

/* Add behavior callbacks for comments actions */
$core->addBehavior('adminCommentsActionsPage',array('adminTypo','adminCommentsActionsPage'));

class adminTypo
{
	public static function adminDashboardFavorites($core,$favs)
	{
		$favs->register('Typo', array(
			'title' => __('Typographic replacements'),
			'url' => 'plugin.php?p=typo',
			'small-icon' => urldecode(dcPage::getPF('typo/icon.png')),
			'large-icon' => urldecode(dcPage::getPF('typo/icon-big.png')),
			'permissions' => 'contentadmin'
		));
	}

	public static function adminPostsActionsPage($core,$ap)
	{
		// Add menuitem in actions dropdown list
		if ($core->auth->check('contentadmin',$core->blog->id)) {
			$ap->addAction(
				array(__('Typo') => array(__('Typographic replacements') => 'typo')),
				array('adminTypo','adminPostsDoReplacements')
			);
		}
	}

	public static function adminPagesActionsPage($core,$ap)
	{
		// Add menuitem in actions dropdown list
		if ($core->auth->check('contentadmin',$core->blog->id)) {
			$ap->addAction(
				array(__('Typo') => array(__('Typographic replacements') => 'typo')),
				array('adminTypo','adminPagesDoReplacements')
			);
		}
	}

	public static function adminPostsDoReplacements($core,dcPostsActionsPage $ap,$post)
	{
		self::adminEntriesDoReplacements($core,$ap,$post,'post');
	}

	public static function adminPagesDoReplacements($core,dcPostsActionsPage $ap,$post)
	{
		self::adminEntriesDoReplacements($core,$ap,$post,'page');
	}

	public static function adminEntriesDoReplacements($core,dcPostsActionsPage $ap,$post,$type='post')
	{
		if (!empty($post['full_content'])) {
			// Do replacements
			$posts = $ap->getRS();
			if ($posts->rows()) {
				$dashes_mode = (integer)$core->blog->settings->typo->typo_dashes_mode;
				while ($posts->fetch())
				{
					if (($posts->post_excerpt_xhtml) || ($posts->post_content_xhtml)) {
						# Apply typo features to entry
						$cur = $core->con->openCursor($core->prefix.'post');

						if ($posts->post_excerpt_xhtml)
							$cur->post_excerpt_xhtml = SmartyPants($posts->post_excerpt_xhtml,($dashes_mode ? $dashes_mode : SMARTYPANTS_ATTR));
						if ($posts->post_content_xhtml)
							$cur->post_content_xhtml = SmartyPants($posts->post_content_xhtml,($dashes_mode ? $dashes_mode : SMARTYPANTS_ATTR));

						$cur->update('WHERE post_id = '.(integer) $posts->post_id);
					}
				}
				$ap->redirect(true,array('upd' => 1));
			} else {
				$ap->redirect();
			}
		} else {
			// Ask confirmation for replacements
			if ($type == 'page') {
				$ap->beginPage(
					dcPage::breadcrumb(
						array(
							html::escapeHTML($core->blog->name) => '',
							__('Pages') => 'plugin.php?p=pages',
							__('Typographic replacements') => ''
				)));
			} else {
				$ap->beginPage(
					dcPage::breadcrumb(
						array(
							html::escapeHTML($core->blog->name) => '',
							__('Entries') => 'posts.php',
							__('Typographic replacements') => ''
			)));
			}

			dcPage::warning(__('Warning! These replacements will not be undoable.'),false,false);

			echo
			'<form action="'.$ap->getURI().'" method="post">'.
			$ap->getCheckboxes().
			'<p><input type="submit" value="'.__('save').'" /></p>'.

			$core->formNonce().$ap->getHiddenFields().
			form::hidden(array('full_content'),'true').
			form::hidden(array('action'),'typo').
			'</form>';
			$ap->endPage();
		}
	}

	public static function adminCommentsActionsPage($core,$ap)
	{
		// Add menuitem in actions dropdown list
		if ($core->auth->check('contentadmin',$core->blog->id)) {
			$ap->addAction(
				array(__('Typo') => array(__('Typographic replacements') => 'typo')),
				array('adminTypo','adminCommentsDoReplacements')
			);
		}
	}

	public static function adminCommentsDoReplacements($core,dcCommentsActionsPage $ap,$post)
	{
		if (!empty($post['full_content'])) {
			// Do replacements
			$co = $ap->getRS();
			if ($co->rows()) {
				$dashes_mode = (integer)$core->blog->settings->typo->typo_dashes_mode;
				while ($co->fetch())
				{
					if ($co->comment_content) {
						# Apply typo features to comment
						$cur = $core->con->openCursor($core->prefix.'comment');
						$cur->comment_content = SmartyPants($co->comment_content,($dashes_mode ? $dashes_mode : SMARTYPANTS_ATTR));
						$cur->update('WHERE comment_id = '.(integer) $co->comment_id);
					}
				}
				$ap->redirect(true,array('upd' => 1));
			} else {
				$ap->redirect();
			}
		} else {
			// Ask confirmation for replacements
			$ap->beginPage(
				dcPage::breadcrumb(
						array(
							html::escapeHTML($core->blog->name) => '',
							__('Comments') => 'comments.php',
							__('Typographic replacements') => ''
			)));

			dcPage::warning(__('Warning! These replacements will not be undoable.'),false,false);

			echo
			'<form action="'.$ap->getURI().'" method="post">'.
			$ap->getCheckboxes().
			'<p><input type="submit" value="'.__('save').'" /></p>'.

			$core->formNonce().$ap->getHiddenFields().
			form::hidden(array('full_content'),'true').
			form::hidden(array('action'),'typo').
			'</form>';
			$ap->endPage();
		}
	}

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
