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
$core->addBehavior('adminPostsActionsCombo',array('adminTypo','adminPostsActionsCombo'));
$core->addBehavior('adminPagesActionsCombo',array('adminTypo','adminPostsActionsCombo'));
$core->addBehavior('adminPostsActions',array('adminTypo','adminPostsActions'));
$core->addBehavior('adminPostsActionsContent',array('adminTypo','adminPostsActionsContent'));

/* Add behavior callbacks for comments actions */
$core->addBehavior('adminCommentsActionsCombo',array('adminTypo','adminCommentsActionsCombo'));
$core->addBehavior('adminCommentsActions',array('adminTypo','adminCommentsActions'));
$core->addBehavior('adminCommentsActionsContent',array('adminTypo','adminCommentsActionsContent'));

class adminTypo
{
	public static function adminDashboardFavs($core,$favs)
	{
		$favs['Typo'] = new ArrayObject(array('Typo','Typographic replacements','plugin.php?p=typo',
			'index.php?pf=typo/icon.png','index.php?pf=typo/icon-big.png',
			$core->auth->check('contentadmin',$core->blog->id),null,null));
	}

	public static function adminPostsActionsCombo($args)
	{
		global $core;
		// Add menuitem in actions dropdown list
		if ($core->auth->check('contentadmin',$core->blog->id))
			$args[0][__('Typo')] = array(__('Typographic replacements') => 'typo');
	}

	public static function adminPostsActionsContent($core,$action,$hidden_fields,$form_uri="posts_actions.php")
	{
		if ($action == 'typo')
		{
			$redir = (isset($_POST['redir']) ? $_POST['redir'] : '');
			if (isset($_POST['post_type']) && $_POST['post_type'] == 'page') {
				echo dcPage::breadcrumb(
					array(
						html::escapeHTML($core->blog->name) => '',
						__('Pages') => ($redir != '' ? $redir : 'plugin.php?p=pages'),
						'<span class="page-title">'.__('Typographic replacements').'</span>' => ''
					));
			} else {
				echo dcPage::breadcrumb(
					array(
						html::escapeHTML($core->blog->name) => '',
						__('Entries') => ($redir != '' ? $redir : 'posts.php'),
						'<span class="page-title">'.__('Typographic replacements').'</span>' => ''
					));
			}

			dcPage::warning(__('Warning! These replacements will not be undoable.'),false,false);

			echo
			'<form action="'.$form_uri.'" method="post">'.
			$hidden_fields.
			$core->formNonce().
			form::hidden(array('action'),'typo').
			form::hidden(array('full_content'),'true').
			'<p><input type="submit" value="'.__('save').'" /></p>'.
			'</form>';
		}
	}

	public static function adminPostsActions($core,$posts,$action,$redir)
	{
		if ($action == 'typo' && !empty($_POST['set_typo'])
			&& $core->auth->check('contentadmin',$core->blog->id))
		{
			try
			{
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

				http::redirect($redir);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
	}

	public static function adminCommentsActionsCombo($args)
	{
		global $core;
		// Add menuitem in actions dropdown list
		if ($core->auth->check('contentadmin',$core->blog->id))
			$args[0][__('Typo')] = array(__('Typographic replacements') => 'typo');
	}

	public static function adminCommentsActionsContent($core,$action,$hidden_fields,$form_uri="comments_actions.php")
	{
		if ($action == 'typo')
		{
			echo dcPage::breadcrumb(
				array(
					html::escapeHTML($core->blog->name) => '',
					__('Comments') => 'comments.php',
					'<span class="page-title">'.__('Typographic replacements').'</span>' => ''
				));

			dcPage::warning(__('Warning! These replacements will not be undoable.'),false,false);

			echo
			'<form action="'.$form_uri.'" method="post">'.
			'<p>'.
			form::checkbox('set_typo','1',$core->blog->settings->typo->typo_active).
			' <label for="set_typo" class="classic">'.__('Apply typographic replacements for selected comments').'</label></p>'.
			$hidden_fields.
			$core->formNonce().
			form::hidden(array('action'),'typo').
			form::hidden(array('full_content'),'true').
			'<p><input type="submit" value="'.__('save').'" /></p>'.
			'</form>';
		}
	}

	public static function adminCommentsActions($core,$co,$action,$redir)
	{
		if ($action == 'typo' && !empty($_POST['set_typo'])
			&& $core->auth->check('contentadmin',$core->blog->id))
		{
			try
			{
				if ((boolean)$_POST['set_typo']) {
					while ($co->fetch())
					{
						if ($co->comment_content) {
							# Apply typo features to comment
							$cur = $core->con->openCursor($core->prefix.'comment');

							if ($co->comment_content)
								$cur->comment_content = SmartyPants($co->comment_content);

							$cur->update('WHERE comment_id = '.(integer) $co->comment_id);
						}
					}
				}

				http::redirect($redir);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
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