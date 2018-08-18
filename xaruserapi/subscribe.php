<?php
/**
 * Pubsub Module
 *
 * @package modules
 * @subpackage pubsub module
 * @category Third Party Xaraya Module
 * @version 2.0.0
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://xaraya.com/index.php/release/181.html
 * @author Pubsub Module Development Team
 * @author Chris Dudley <miko@xaraya.com>
 * @author Garrett Hunter <garrett@blacktower.com>
 * @author Marc Lutolf <mfl@netspan.ch>
 */
/**
 * subscribe user to a pubsub element
 * @param $args['modid'] module ID of event
 * @param $args['itemtype'] itemtype of event
 * @param $args['cid'] cid of event
 * @param $args['extra'] some extra group criteria
 *
 * @param $args['userid'] the subscriber OR
 * @param $args['email'] Email address of anonymous user
 * @param int actionid The id for the action to take
 * @param $args['groupdescr'] <unknown>
 * @returns output
 * @return output with pubsub information
 */
function pubsub_userapi_subscribe($args)
{
    extract($args);

    // Argument check
    $invalid = array();
    if (!isset($modid))                    { $invalid[] = 'modid'; }
    if (!isset($cid))                      { $invalid[] = 'cid'; }
    if (!isset($itemtype))                 { $invalid[] = 'itemtype'; }
    if (!isset($userid) && !isset($email)) { $invalid[] = 'userid/email'; }
    if (count($invalid) > 0) {
        $msg = xarML('Invalid #(1) in function #(2)() in module #(3)',
        join(', ',$invalid), 'subscribe', 'Pubsub');
        throw new Exception($msg);
    }

    if( !isset($userid) && isset($email) && !empty($email) )
    {
        $userid = -1;
        //TODO: Email validation to make sure this is a valid looking email address
    } elseif ( !isset($email) ) {
        $email = '';
    }

    if (!isset($extra)) {
        $extra = '';
    }
    // use htmlemail or not
    if (!isset($actionid)) {
        $actionid = 1;
    }
    if (!isset($groupdescr))
        $groupdescr = 'Subscribe';

    // check if we already have an event for this, or create it if necessary
    $eventid = xarMod::apiFunc('pubsub','admin','checkevent',
                             array('modid' => $modid,
                                   'itemtype' => $itemtype,
                                   'cid' => $cid,
                                   'extra' => $extra,
                                   'groupdescr' => $groupdescr));
    if (empty($eventid)) return; // throw back

// TODO: fill in eventid *and* actionid (wherever that is supposed to come from)
// AM hardcoding actionid to 1 for now, will have to work out options for htmlmail etc. later
    if (!xarMod::apiFunc('pubsub',
                       'user',
                       'adduser',
                        array('eventid' => $eventid
                             ,'actionid' => $actionid
                             ,'userid' => $userid
                             ,'email' => $email
                              ))) {
        $msg = xarML('Bad return from #(1) in function #(2)() in module #(3)',
                     'adduser', 'subscribe', 'Pubsub');
        throw new Exception($msg);
    }

    return true;

} // END subscribe

?>