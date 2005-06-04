<?php
/**
 * File: $Id$
 *
 * Pubsub User Interface
 *
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2002 by the Xaraya Development Team.
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.xaraya.org
 *
 * @subpackage Pubsub Module
 * @author Chris Dudley <miko@xaraya.com>
 * @author Garrett Hunter <garrett@blacktower.com>
 */

/**
 * display pubsub element next to a registered event
 *  - A subscribe icon is if the user is registered
 *  - Nothing is displayed for an unregisted user
 *  - An unsubscribe option is displayed to users currently subscribed
 * 
 * @param $args['extrainfo'] URL to return
 * @returns output
 * @return output with pubsub information
 */
function pubsub_user_displayicon($args)
{
    extract($args);

    // do nothing if user not logged in otherwise subscribe 
    // the currently logged in user
    if (xarUserIsLoggedIn()) {
        $userid = xarUserGetVar('uid');
    } else {
        return '';
    }
    if (!isset($extrainfo)) {
         $extrainfo = array();
    }

    /**
     * Validate parameters
     */
    $invalid = array();
    if(!isset($extrainfo) || !is_array($extrainfo)) {
        $invalid[] = 'extrainfo';
    } elseif(isset($extrainfo['cid'])) {
        
        $cid = $extrainfo['cid'];
        
        if (isset($extrainfo['cid']) && is_numeric($extrainfo['cid'])) {
            $cid = $extrainfo['cid'];
        }
// FIXME: handle this in a cleaner way - cfr. categories navigation
        if (isset($extrainfo['current_module']) && is_string($extrainfo['current_module'])) {
            $modname = $extrainfo['current_module'];
        } elseif (isset($extrainfo['module']) && is_string($extrainfo['module'])) {
            $modname = $extrainfo['module'];
        }
        if(isset($extrainfo['current_itemtype']) && is_numeric($extrainfo['current_itemtype'])) {
            $itemtype = $extrainfo['current_itemtype'];
        } elseif(isset($extrainfo['itemtype']) && is_numeric($extrainfo['itemtype'])) {
            $itemtype = $extrainfo['itemtype'];
        }
        if (isset($extrainfo['returnurl']) && is_string($extrainfo['returnurl'])) {
            $returnurl = $extrainfo['returnurl'];
        }
    } else {
        // May only subscribe to categories, no category, pubsub does nothing.        
        return array('donotdisplay'=>TRUE);
    }
    
    if (count($invalid) > 0) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
            join(', ', $invalid), 'user', 'displayicon','pubsub');
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                            new SystemException($msg));
    } else {
    }
///

    // When called via hooks, the module name may be empty, so we get it from
    // the current module
    if (empty($modname)) {
        $modname = xarModGetName();
    }

    $modid = xarModGetIDFromName($modname);

    if (empty($itemtype)) {
        $itemtype = 0;
    }

    // if pubsub isn't hooked to this module & itemtype, don't show subscription either
    if (!xarModIsHooked('pubsub',$modname,$itemtype)) return array('donotdisplay'=>TRUE);

/// check for unsubscribe
    /**
     * Fetch the eventid to check
     */
    // Database information
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();

    $pubsubeventstable = $xartable['pubsub_events'];
    $pubsubregtable = $xartable['pubsub_reg'];

    $query = "SELECT xar_pubsubid
                FROM $pubsubeventstable, $pubsubregtable
               WHERE $pubsubeventstable.xar_modid = ?
                 AND $pubsubeventstable.xar_itemtype = ?
                 AND $pubsubeventstable.xar_cid = ?
                 AND $pubsubeventstable.xar_eventid = $pubsubregtable.xar_eventid
                 AND $pubsubregtable.xar_userid = ?";

        $bindvars = array((int)$modid, (int)$itemtype, (int)$cid, (int)$userid);
        if (isset($extra)) {
            $query .= " AND $pubsubeventstable.xar_extra = ?";
            array_push($bindvars, $extra);
        }
        $result =& $dbconn->Execute($query, $bindvars);
    if (!$result) return;
    if ($result->EOF) {
        /**
         * If we get a hit on pubsub_reg, that mean we are already subscribed
         */
        $data['subscribe'] = 1;
    } else { 
        $data['subscribe'] = 0;
    }

    $data['subdata'] = array ('modname' => $modname
                             ,'modid'   => $modid
                             ,'itemtype' => $itemtype
                             ,'cid'     => $cid
                             ,'extra'   => isset($extra) ? $extra : null
                             ,'returnurl' => $returnurl
                             ,'subaction' => $data['subscribe']
                             );

    $data['subURL'] = xarModURL('pubsub','user','modifysubscription',$data['subdata']);                             
    $data['subTEXT'] = xarML ('Subscribe');                             

    $data['unsubURL'] = xarModURL('pubsub','user','modifysubscription',$data['subdata']);                             
    $data['unsubTEXT'] = xarML ('Unsubscribe');                             

    return $data;

} // END displayicon

?>
