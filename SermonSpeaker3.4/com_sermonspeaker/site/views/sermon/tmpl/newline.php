<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
if ($this->params->get('ga')) { $callback = "&callback=".$this->params->get('ga'); }
$return = SermonspeakerHelperSermonspeaker::insertAddfile($this->row->addfile, $this->row->addfileDesc);
$id = $this->row->id;
?>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr class="componentheading">
		<th align="left" valign="bottom"><?php echo JText::_('SINGLESERMON'); ?></th>
	</tr>
</table>
<!-- Begin Data -->
<table border="0" cellpadding="7" cellspacing="7">
	<tr>
		<td valign="top"><b><?php echo JText::_('SERMONNAME'); ?>:</b></td><td>
		<?php if ($this->params->get('hide_dl') == "0" && strlen($this->row->sermon_path) > 0) {
			echo "<a title=\"".JText::_('DOWNLOAD_HOOVER_TAG')."\" href=\"".$this->lnk."\">".$this->row->sermon_title."</a>";
		} else {
			echo $this->row->sermon_title;
		} ?>
	</td></tr>
	<?php if ($this->params->get('client_col_sermon_scripture_reference')){ ?>
		<tr>
			<td valign="top"><b><?php echo JText::_('SCRIPTURE'); ?>:</b></td>
			<td><?php echo $this->row->sermon_scripture; ?></td>
		</tr>
	<?php }
	if ($this->params->get('client_col_player') && strlen($this->row->sermon_path) > 0){ ?>
		<tr>
			<td></td>
			<td>
			<?php
			$ret = SermonspeakerHelperSermonspeaker::insertPlayer($this->lnk);
			$pp_ret = explode("/",$ret);
			$pp_h = $pp_ret[0];
			$pp_w = $pp_ret[1];
			?>
			</td>
		</tr>
	<?php } // if client_col_player
	$this->lnk = str_replace('\\','/',$this->lnk); ?>
	<?php if ($this->params->get('dl_button') == "1" && strlen($this->row->sermon_path) > 0) { ?>
		<tr>
			<td></td>
			<?php echo SermonspeakerHelperSermonspeaker::insertdlbutton($id, $this->row->sermon_path); ?>
		</tr>
		<?php }
		if ($this->params->get('popup_player') == "1" && strlen($this->row->sermon_path) > 0) { ?>
		<tr>
			<td></td>
			<td><input class="popup_btn" type="button" name="<?php echo JText::_('POPUP_PLAYER'); ?>" value="<?php echo JText::_('POPUP_PLAYER'); ?>" onClick="popup = window.open('<?php echo JRoute::_("index.php?view=sermon&layout=popup&id=$id&tmpl=component"); ?>', 'PopupPage', 'height=<?php echo $pp_h.",width=".$pp_w; ?>,scrollbars=yes,resizable=yes'); return false" /></td>
		</tr>
		<?php } ?>
	<?php
		$return = SermonspeakerHelperSermonspeaker::insertAddfile($this->row->addfile, $this->row->addfileDesc);
		if ($return != NULL) {
			echo "<tr><td valign =\"top\"><b>".JText::_('ADDFILE').":</b></td><td>".$return."</td></tr>";
		} ?>
		<?php if ($this->params->get('client_col_sermon_notes') && strlen($this->row->notes) > 0){
			echo "<tr><td valign =\"top\"><b>".JText::_('SERMONNOTES').":</b></td><td>".$this->row->notes."</td></tr>";
		} ?>
</table>
<table width="100%">
	<?php
	// Support for JComments
	$comments = JPATH_BASE.DS.'components'.DS.'com_jcomments'.DS.'jcomments.php';
	if (file_exists($comments)) {
		require_once($comments); ?>
		<tr><td><br></td></tr>
		<tr>
			<td>
				<?php echo JComments::showComments($id, 'com_sermonspeaker', $this->row->sermon_title); ?>
			</td>
		</tr>
	<?php } ?>
</table>
