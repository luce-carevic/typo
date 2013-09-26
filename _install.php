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

$new_version = $core->plugins->moduleInfo('typo','version');
$old_version = $core->getVersion('typo');

if (version_compare($old_version,$new_version,'>=')) return;

try
{
	if (version_compare(DC_VERSION,'2.2','<'))
	{
		throw new Exception('Typo requires Dotclear 2.2');
	}
	
	$core->blog->settings->addNamespace('typo');

	// Default state is active for entries content and inactive for comments
	$core->blog->settings->typo->put('typo_active',true,'boolean','Active',false,true);
	$core->blog->settings->typo->put('typo_entries',true,'boolean','Apply on entries',false,true);
	$core->blog->settings->typo->put('typo_comments',false,'boolean','Apply on comments',false,true);

	$core->setVersion('typo',$new_version);
	
	return true;
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}
return false;
