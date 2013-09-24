<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of typo, a plugin for Dotclear 2.
#
# Copyright (c) Franck Paul and contributors
# carnet.franck.paul@gmail.com
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
$_menu['Blog']->addItem(__('Typographic replacements'),'plugin.php?p=typo','index.php?pf=typo/icon.png',
		preg_match('/plugin.php\?p=typo(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('contentadmin',$core->blog->id));

$core->addBehavior('adminDashboardFavs',array('adminTypo','adminDashboardFavs'));

/* Add behavior callbacks for posts actions */
$core->addBehavior('adminPostsActionsPage',array('adminTypo','adminPostsActionsPage'));
$core->addBehavior('adminPagesActionsPage',array('adminTypo','adminPagesActionsPage'));

/* Add behavior callbacks for comments actions */
$core->addBehavior('adminCommentsActionsPage',array('adminTypo','adminCommentsActionsPage'));

class adminTypo
{
	public static function adminDashboardFavs($core,$favs)
	{
		$favs['Typo'] = new ArrayObject(array('Typo','Typographic replacements','plugin.php?p=typo',
			'index.php?pf=typo/icon.png','index.php?pf=typo/icon-big.png',
			$core->auth->check('contentadmin',$core->blog->id),null,null));
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
				while ($posts->fetch())
				{
					if (($posts->post_excerpt_xhtml) || ($posts->post_content_xhtml)) {
						# Apply typo features to entry
						$cur = $core->con->openCursor($core->prefix.'post');

						if ($posts->post_excerpt_xhtml)
							$cur->post_excerpt_xhtml = SmartyPants($posts->post_excerpt_xhtml);
						if ($posts->post_content_xhtml)
							$cur->post_content_xhtml = SmartyPants($posts->post_content_xhtml);

						$cur->update('WHERE post_id = '.(integer) $posts->post_id);
					}
				}
				$ap->redirect(array('upd' => 1),true);
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
							'<span class="page-title">'.__('Typographic replacements').'</span>' => ''
				)));
			} else {
				$ap->beginPage(
					dcPage::breadcrumb(
						array(
							html::escapeHTML($core->blog->name) => '',
							__('Entries') => 'posts.php',
							'<span class="page-title">'.__('Typographic replacements').'</span>' => ''
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
				while ($co->fetch())
				{
					if ($co->comment_content) {
						# Apply typo features to comment
						$cur = $core->con->openCursor($core->prefix.'comment');
						$cur->comment_content = SmartyPants($co->comment_content);
						$cur->update('WHERE comment_id = '.(integer) $co->comment_id);
					}
				}
				$ap->redirect(array('upd' => 1),true);
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
							'<span class="page-title">'.__('Typographic replacements').'</span>' => ''
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
				/* Transform typo for excerpt (XHTML) */
				if (isset($ref['excerpt_xhtml'])) {
					$excerpt = &$ref['excerpt_xhtml'];
					if ($excerpt) {
						$excerpt = SmartyPants($excerpt);
					}
				}
				/* Transform typo for content (XHTML) */
				if (isset($ref['content_xhtml'])) {
					$content = &$ref['content_xhtml'];
					if ($content) {
						$content = SmartyPants($content);
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
					$cur->comment_content = SmartyPants($cur->comment_content);
				}
			}
		}
	}
}
?>