<?php
/**
 * Prepare admin pages header
 *
 * @return array
 */
function posAdminPrepareHead()
{
	global $langs, $conf;

	$langs->load("pos@pos");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/pos/admin/pos.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;
	$head[$h][0] = dol_buildpath("/pos/admin/restaurant.php", 1);
	$head[$h][1] = $langs->trans("restaurant");
	$head[$h][2] = 'restaurant';	
	$h++;

	$head[$h][0] = dol_buildpath("/pos/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	
	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@pos:/pos/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@pos:/pos/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'pos');

	return $head;
}
