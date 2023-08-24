<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package   [Members-Map-Bundle]
 * @author    Taheri Create Core Team
 * @license   GNU/LGPL
 * @copyright Taheri Create 2023 - 2026
 */


namespace Tahericreate\MembersMapBundle\Model;

use \Contao\Model;
use Tahericreate\MembersMapBundle\FeMod\PartnerMap;
/*
 * Class MemberExtendedModel
 *
 * @author Taheri Create Core Team
 */

class MemberExtendedModel extends \MemberModel
{
	/**
	 * Function findMapData Get the item spares based on given criteria
	 * Author: Taheri Create Core Team
	 * 
	 * @param	string	$partnerType	The ID of the familyMm
	 * @param	string	$zip			The ID of the file
	 * @param	string	$city			The ID of the file
	 * @return @collection
	 **/
	public static function findMapData(string $partnerType, string $plz = '', string $city = '')
	{
		if (!$plz && !$city && $partnerType == 'a') {
			return self::findBy('country', 'de');
		} elseif (!$plz && !$city && $partnerType == 'p') {
			$objMember = \Database::getInstance()->prepare("SELECT * FROM " . self::$strTable . " WHERE memberline=? AND (country=? OR country=?)")->execute('GFH', 'de', 'international');
			if ($objMember->numRows) {
				return static::createCollectionFromDbResult($objMember, self::$strTable);
			}
		} elseif ($plz && !$city && $partnerType == 'a') {
			$objMember = \Database::getInstance()->prepare("SELECT * FROM " . self::$strTable . " WHERE postal LIKE ? AND (country=? OR country=?)")->execute('%' . $plz . '%', 'de', 'international');
			if ($objMember->numRows) {
				return static::createCollectionFromDbResult($objMember, self::$strTable);
			} else {
				return self::findBy('country', 'de');
			}
		} elseif ($plz && !$city && $partnerType == 'p') {
			$objMember = \Database::getInstance()->prepare("SELECT * FROM " . self::$strTable . " WHERE memberline=? AND postal LIKE ? AND (country=? OR country=?)")->execute('GFH', '%' . $plz . '%', 'de', 'international');
			if ($objMember->numRows) {
				return static::createCollectionFromDbResult($objMember, self::$strTable);
			} else {
				return self::findBy('country', 'de');
			}
		} elseif (!$plz && $city && $partnerType == 'a') {
			$objMember = \Database::getInstance()->prepare("SELECT * FROM " . self::$strTable . " WHERE city LIKE ? AND (country=? OR country=?)")->execute('%' . $city . '%', 'de', 'international');
			if ($objMember->numRows) {
				return static::createCollectionFromDbResult($objMember, self::$strTable);
			} else {
				return self::findBy('country', 'de');
			}
		} elseif (!$plz && $city && $partnerType == 'p') {
			$objMember = \Database::getInstance()->prepare("SELECT * FROM " . self::$strTable . " WHERE memberline=? AND city LIKE ? AND (country=? OR country=?)")->execute('GFH', '%' . $city . '%', 'de', 'international');
			if ($objMember->numRows) {
				return static::createCollectionFromDbResult($objMember, self::$strTable);
			} else {
				return self::findBy('country', 'de');
			}
		} elseif ($plz && $city && $partnerType == 'a') {
			$objMember = \Database::getInstance()->prepare("SELECT * FROM " . self::$strTable . " WHERE postal=? AND city LIKE ?")->execute($plz, '%' . $city . '%');
			if ($objMember->numRows) {
				return static::createCollectionFromDbResult($objMember, self::$strTable);
			} else {
				$searchCords = \Vrisini\ShopsystemBundle\FeMod\PartnerMap::getGeocode($plz, $city);
				$lat = $searchCords[0];
				$lng = $searchCords[1];

				// Calculate distance using Haversine formula in the database query
				$sqlQuery = "
					SELECT *,
							(
								6371 * acos(
									cos(
										radians(" . $lat . ")
									) *
									cos(
										radians(SUBSTRING_INDEX(SUBSTRING_INDEX(geocode, ',', 2), ',', -1))
									) * 
									cos( 
										radians(SUBSTRING_INDEX(SUBSTRING_INDEX(geocode, ',', 1), ',', -1)) - radians(" . $lng . ")
									) + 
									sin(
										radians(" . $lat . ")
									) *
									sin(
										radians(SUBSTRING_INDEX(SUBSTRING_INDEX(geocode, ',', 2), ',', -1))
									)
								)
							) as distance 
					FROM " . self::$strTable . "
					HAVING distance < 40";

				// Get the model results
				$objDatabase = \Database::getInstance();
				$objResult = $objDatabase->prepare($sqlQuery)
					->limit(100)
					->execute();
				return static::createCollectionFromDbResult($objResult, self::$strTable);
			}
		} elseif ($plz && $city && $partnerType == 'p') {
			$objMember = \Database::getInstance()->prepare("SELECT * FROM " . self::$strTable . " WHERE memberline=? AND postal=? AND city LIKE ?")->execute('GFH', $plz, '%' . $city . '%');
			if ($objMember->numRows) {
				return static::createCollectionFromDbResult($objMember, self::$strTable);
			} else {
				$searchCords = \Vrisini\ShopsystemBundle\FeMod\PartnerMap::getGeocode($plz, $city);
				$lat = $searchCords[0];
				$lng = $searchCords[1];

				// Calculate distance using Haversine formula in the database query
				$sqlQuery = "
					SELECT *,
							(
								6371 * acos(
									cos(
										radians(" . $lat . ")
									) *
									cos(
										radians(SUBSTRING_INDEX(SUBSTRING_INDEX(geocode, ',', 2), ',', -1))
									) * 
									cos( 
										radians(SUBSTRING_INDEX(SUBSTRING_INDEX(geocode, ',', 1), ',', -1)) - radians(" . $lng . ")
									) + 
									sin(
										radians(" . $lat . ")
									) *
									sin(
										radians(SUBSTRING_INDEX(SUBSTRING_INDEX(geocode, ',', 2), ',', -1))
									)
								)
							) as distance 
					FROM " . self::$strTable . "
					HAVING distance < 40 AND memberline='GFG'";

				// Get the model results
				$objDatabase = \Database::getInstance();
				$objResult = $objDatabase->prepare($sqlQuery)
					->limit(100)
					->execute();
				return static::createCollectionFromDbResult($objResult, self::$strTable);
			}
		} else {
			die('PT:: ' . $partnerType . ' PLZ:: ' . $plz . ' CITY:: ' . $city);
		}
		return null;
	}
}

class_alias(MemberExtendedModel::class, 'MemberExtendedModel');
