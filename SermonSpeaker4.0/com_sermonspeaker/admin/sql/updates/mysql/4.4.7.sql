ALTER TABLE #__sermon_sermons ADD `modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE #__sermon_sermons ADD `modified_by` INT(10) NOT NULL DEFAULT '0';
ALTER TABLE #__sermon_speakers ADD `modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE #__sermon_speakers ADD `modified_by` INT(10) NOT NULL DEFAULT '0';
ALTER TABLE #__sermon_series ADD `modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE #__sermon_series ADD `modified_by` INT(10) NOT NULL DEFAULT '0';
ALTER TABLE #__sermon_series ADD `zip_created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE #__sermon_series ADD `zip_content` TEXT NOT NULL;
ALTER TABLE #__sermon_series ADD `zip_progress` INT(11) NOT NULL DEFAULT '0';
