<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of typo, a plugin for Dotclear 2.
# 
# Copyright (c) 2011 Franck Paul and contributors
# carnet.franck.paul@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

// Getting current parameters
$typo_active = (boolean)$core->blog->settings->typo->typo_active;
$typo_entries = (boolean)$core->blog->settings->typo->typo_entries;
$typo_comments = (boolean)$core->blog->settings->typo->typo_comments;

// Saving new configuration
if (!empty($_POST['saveconfig'])) {
	try
	{
		$core->blog->settings->addNamespace('typo');

		$typo_active = (empty($_POST['active']))?false:true;
		$typo_entries = (empty($_POST['entries']))?false:true;
		$typo_comments = (empty($_POST['comments']))?false:true;
		$core->blog->settings->typo->put('typo_active',$typo_active,'boolean');
		$core->blog->settings->typo->put('typo_entries',$typo_entries,'boolean');
		$core->blog->settings->typo->put('typo_comments',$typo_comments,'boolean');
		$core->blog->triggerBlog();
		$msg = __('Configuration successfully updated.');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}
?>
<html>
<head>
	<title><?php echo __('Typo'); ?></title>
</head>

<body>
<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt; <?php echo __('Typo'); ?></h2>

<?php if (!empty($msg)) dcPage::message($msg); ?>

<div id="typo_options">
	<form method="post" action="plugin.php">
	<fieldset>
		<legend><?php echo __('Plugin activation'); ?></legend>
		<p class="field">
			<?php echo form::checkbox('active', 1, $typo_active); ?>
			<label class="classic" for="active"><?php echo __('Enable Typo for this blog'); ?></label>
		</p>
	</fieldset>

	<fieldset>
		<legend><?php echo __('Options'); ?></legend>
		<p class="field">
			<?php echo form::checkbox('entries', 1, $typo_entries); ?>
			<label class="classic" for="entries"><?php echo __('Enable Typo for entries'); ?></label>
		</p>
		<p class="form-note"><?php echo __('Activating this option enforces typographic replacements in blog entries'); ?></p>
		<p class="field">
			<?php echo form::checkbox('comments', 1, $typo_comments); ?>
			<label class="classic" for="comments"><?php echo __('Enable Typo for comments'); ?></label>
		</p>
		<p class="form-note"><?php echo __('Activating this option enforces typographic replacements in blog comments (excluding trackbacks)'); ?></p>
	</fieldset>

	<p><input type="hidden" name="p" value="typo" />
	<?php echo $core->formNonce(); ?>
	<input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" />
	</p>
	</form>
</div>

</body>
</html>