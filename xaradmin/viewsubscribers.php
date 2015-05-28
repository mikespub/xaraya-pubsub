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
 * Displays a list of subscribers to a given category. Provides an option
 * to manually remove a subscriber.
 */
function pubsub_admin_viewsubscribers()
{
    if (!xarVarFetch('eventid', 'int::', $eventid)) return;
    if (!xarVarFetch('pubsubid','int::', $pubsubid, FALSE)) return;
    if (!xarVarFetch('unsub',   'int::', $unsub, FALSE)) return;

    if (empty($eventid)) {
        $msg = xarML('Invalid #(1) for function #(2)() in module #(3)',
                    'event id', 'viewsubscribers', 'Pubsub');
        throw new Exception($msg);
    }

    if ($unsub && $pubsubid) {
        if (!xarMod::apiFunc('pubsub', 'user', 'deluser', array('pubsubid' => $pubsubid))) {
            $msg = xarML('Bad return from #(1) in function #(2)() in module #(3)',
                         'deluser', 'viewsubscribers', 'Pubsub');
            throw new Exception($msg);
        }
    }

    $info = xarMod::apiFunc('pubsub','user','getevent', array('eventid' => $eventid));
    if (empty($info)) {
        $msg = xarML('Invalid #(1) for function #(2)() in module #(3)',
                    'event id', 'viewsubscribers', 'Pubsub');
        throw new Exception($msg);
    }

    $data['items'] = array();
    $data['namelabel'] = xarVarPrepForDisplay(xarML('Publish / Subscribe Administration'));
    $data['catname'] = xarVarPrepForDisplay($info['catname']);
    $data['cid'] = $info['cid'];
    $data['modname'] = $info['modname'];
    if (!empty($info['itemtype'])) {
        $data['modname'] .= ' ' . $info['itemtype'];
    }
    $data['itemtype'] = $info['itemtype'];
    $data['eventid'] = $eventid;
    $data['authid'] = xarSecGenAuthKey();
    $data['pager'] = '';

    if (!xarSecurityCheck('AdminPubSub')) return;

    // The user API function is called
    $subscribers = xarMod::apiFunc('pubsub', 'user', 'getsubscribers', array('eventid'=>$eventid));

    $data['items'] = $subscribers;

    $data['returnurl'] = xarModURL('pubsub', 'user', 'viewsubscribers', array('eventid'=>$eventid));

    // TODO: add a pager (once it exists in BL)
    $data['pager'] = '';

    // return the template variables defined in this template

    return $data;

} // END ViewSubscribers

?>
