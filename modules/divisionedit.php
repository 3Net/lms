<?php

/*
 * LMS version 1.11-git
 *
 *  (C) Copyright 2001-2012 LMS Developers
 *
 *  Please, see the doc/AUTHORS for more information about authors!
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License Version 2 as
 *  published by the Free Software Foundation.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307,
 *  USA.
 *
 *  $Id$
 */

if(!empty($_GET['changestatus']))
{
	$DB->Execute('UPDATE divisions SET status = (CASE WHEN status > 0 THEN 0 ELSE 1 END)
		WHERE id = ?', array(intval($_GET['id'])));
	$SESSION->redirect('?m=divisionlist');
}

$olddiv = $DB->GetRow('SELECT * FROM divisions WHERE id = ?', array($_GET['id']));

if(!empty($_POST['division'])) 
{
	$division = $_POST['division'];
		
	foreach($division as $key => $value)
	        $division[$key] = trim($value);
			
	if($division['name']=='' && $division['description']=='' && $division['shortname']=='')
	{
		$SESSION->redirect('?m=divisionlist');
	}
	
	$division['id'] = $olddiv['id'];
	
	if($division['name'] == '')
		$error['name'] = trans('Division long name is required!');

	if($division['shortname'] == '')
		$error['shortname'] = trans('Division short name is required!');
	elseif($olddiv['shortname'] != $division['shortname']
		&& $DB->GetOne('SELECT 1 FROM divisions WHERE shortname = ?', array($division['shortname'])))
	{
		$error['shortname'] = trans('Division with specified name already exists!');
	}
	
	if($division['address'] == '')
		$error['address'] = trans('Address is required!');

	if($division['city'] == '')
		$error['city'] = trans('City is required!');
	
	if($division['zip'] == '')
		$error['zip'] = trans('Zip code is required!');
	elseif(!check_zip($division['zip']))
		$error['zip'] = trans('Incorrect ZIP code!');

        if($division['ten'] != '' && !check_ten($division['ten']) && !isset($division['tenwarning']))
        {
                $error['ten'] = trans('Incorrect Tax Exempt Number! If you are sure you want to accept it, then click "Submit" again.');
                $division['tenwarning'] = 1;
        }

        if($division['regon'] != '' && !check_regon($division['regon']))
                $error['regon'] = trans('Incorrect Business Registration Number!');

	if($division['account'] != '' && (strlen($division['account'])>48 || !preg_match('/^([A-Z][A-Z])?[0-9]+$/', $division['account'])))
		$error['account'] = trans('Wrong account number!');

	if($division['inv_paytime'] == '')
                $division['inv_paytime'] = NULL;

	if(!$error)
	{
		$DB->Execute('UPDATE divisions SET name=?, shortname=?, address=?, 
			city=?, zip=?, countryid=?, ten=?, regon=?, account=?, inv_header=?, 
			inv_footer=?, inv_author=?, inv_cplace=?, inv_paytime=?,
			inv_paytype=?, description=?, status=?, url=?, email=?, rpt=?, rjpt=?, rbe=? 
			WHERE id=?',
			array(
				    $division['name'],
				    $division['shortname'],
				    $division['address'],
				    $division['city'],
				    $division['zip'],
				    $division['countryid'],
				    $division['ten'],
				    $division['regon'],
				    $division['account'],
				    $division['inv_header'],
				    $division['inv_footer'],
				    $division['inv_author'],
				    $division['inv_cplace'],
				    $division['inv_paytime'],
				    $division['inv_paytype'] ? $division['inv_paytype'] : null,
				    $division['description'],
				    (!empty($division['status']) ? 1 : 0),
				    ($division['url'] ? $division['url'] : NULL),
				    ($division['email'] ? $division['email'] : NULL),
				    ($division['rpt'] ? $division['rpt'] : NULL),
				    ($division['rjpt'] ? $division['rjpt'] : NULL),
				    ($division['rbe'] ? $division['rbe'] : NULL),
				    $division['id']
			));
		
		if (SYSLOG)
		    addlogs('aktualizacja danych dla firmy '.$division['name'],'e=up;m=conf;');
		
		$SESSION->redirect('?m=divisionlist');
	}
}	

$layout['pagetitle'] = trans('Edit Division: $a', $olddiv['shortname']);

$SESSION->save('backto', $_SERVER['QUERY_STRING']);

$SMARTY->assign('division', !empty($division) ? $division : $olddiv);
$SMARTY->assign('countries', $LMS->GetCountries());
$SMARTY->assign('error', $error);
$SMARTY->display('divisionedit.html');

?>
