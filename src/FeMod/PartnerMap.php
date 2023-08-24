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


/**
 * Namespace
 */

namespace Tahericreate\MembersMapBundle\FeMod;

use Tahericreate\MembersMapBundle\Model\MemberExtendedModel;

/**
 * Class PartnerMap
 *
 * @copyright  Taheri Create 2023 - 2026
 * @author     Taheri Create Core Team
 * @package    Devtools
 */
class PartnerMap extends \Module
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'partner_map';

    /**
     * Display a wildcard in the back end
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### VI SHOPSYSTEM - PARTNER MAP ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&table=tl_module&act=edit&id=' . $this->id;

            return $objTemplate->parse();
        }
        return parent::generate();
    }

    /**
     * Generate the content
     */
    protected function compile()
    {
        // Add public assets to global list
		$GLOBALS['TL_CSS'][] = 'bundles/membersmap/css/style.css';
		$GLOBALS['TL_JAVASCRIPT'][] = 'bundles/membersmap/js/map-handler.js';
        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/membersmap/js/custom.js';

        // Get google api key
        global $objPage;		
		$objRoot = \PageModel::findById($objPage->rootId);
        $googleApiKey = $objRoot->google_api_key;

        // Find partner details by partnerId
        if (\Input::post('mode') == 'memberDetails') { // Filter by family	
            die($this->getPartnerDetails(\Input::post('memberId')));
        }

        if (\Input::post('partner-type')) {
            $objMembers = MemberExtendedModel::findMapData(\Input::post('partner-type'), \Input::post('plz'), \Input::post('city'));
        } else {
            $objMembers = \MemberModel::findAll();
        }
        $geocodes = array();
        $count = 0;
        foreach ($objMembers as $objMember) {
            $memberGroup = unserialize($objMember->groups);
            if ($objMember->geocode && $memberGroup[0] == 1) {
                $partnerLoc = explode(',', $objMember->geocode);
                $geocodes[] = [
                    'latitude'    => $partnerLoc[1],
                    'longitude' => $partnerLoc[0],
                    'id'        => $objMember->id,
                    'member_type' => $objMember->memberline
                ];
            }
            $count++;
        }

        //Assign Values to View
        $this->Template->objMembers = $objMembers;
        $this->Template->geocodes = $geocodes;
        $this->Template->googleApiKey = $googleApiKey;
        $this->Template->count = $count;
    }

    protected function getPartnerDetails($partnerId)
    {
        $objmember = \MemberModel::findById($partnerId);

        // Send values to view
        $objTemplate = new \FrontendTemplate('partner_details');
        $objTemplate->objmember = $objmember;

        return $objTemplate->parse();
    }

    public static function getGeocode($plz, $city, $key)
    {

        // google map geocode api url
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $plz . '+' . $city . '&key=AIzaSyDWuAO2lhTWpCUtcRnQyAMRfazyT5Z3fCs';

        // get the json response
        $resp_json = file_get_contents($url);

        // decode the json
        $resp = json_decode($resp_json, true);

        // response status will be 'OK', if able to geocode given address 
        if ($resp['status'] == 'OK') {

            // get the important data
            $lati = isset($resp['results'][0]['geometry']['location']['lat']) ? $resp['results'][0]['geometry']['location']['lat'] : "";
            $longi = isset($resp['results'][0]['geometry']['location']['lng']) ? $resp['results'][0]['geometry']['location']['lng'] : "";
            $formatted_address = isset($resp['results'][0]['formatted_address']) ? $resp['results'][0]['formatted_address'] : "";

            // verify if data is complete
            if ($lati && $longi && $formatted_address) {

                // put the data in the array
                $data_arr = array();

                array_push(
                    $data_arr,
                    $lati,
                    $longi,
                    $formatted_address
                );
                // echo '<pre>';
                // print_r($data_arr);
                // die('</pre>');
                return $data_arr;
            } else {
                return false;
            }
        } else {
            echo '<strong>ERROR: ' . $resp['status'] . '</strong>';
            return false;
        }
    }
}
