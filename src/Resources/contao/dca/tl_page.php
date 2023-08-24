<?php

/* 
 * @package   [Members-Map-Bundle]
 * @author    Taheri Create Core Team
 * @license   GNU/LGPL
 * @copyright Taheri Create 2023 - 2026
 */

$GLOBALS['TL_DCA']['tl_page']['palettes']['root'] = str_replace('{publish_legend},published,start,stop', '{google_api},google_api_key;{publish_legend},published,start,stop', $GLOBALS['TL_DCA']['tl_page']['palettes']['root']);
$GLOBALS['TL_DCA']['tl_page']['palettes']['rootfallback'] = str_replace('{publish_legend},published,start,stop', '{google_api},google_api_key;{publish_legend},published,start,stop', $GLOBALS['TL_DCA']['tl_page']['palettes']['rootfallback']);

$GLOBALS['TL_DCA']['tl_page']['fields']['google_api_key'] = [
    'label'                   => array('Google Api Key', 'Enter your google api key.'),
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('mandatory' => false),
	'sql'                     => "Varchar(100) NOT NULL default ''",
];

