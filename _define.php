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

$this->registerModule(
	/* Name */				"Typo",
	/* Description*/		"Brings smart typographic replacements for your blog entries and comments",
	/* Author */			"Franck Paul",
	/* Version */			'1.9',
	array(
		/* Permissions */	'permissions' =>	'contentadmin',
		/* Type */			'type' =>			'plugin'
	)
);
