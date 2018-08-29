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
 * run the job
 * @param $args['id'] the process handling id
 * @param $args['pubsubid'] the subscription id
 * @param $args['objectid'] the specific object in the module
 * @param $args['id'] the template id for this job
 * @return bool true on success, false on failure
 * @throws BAD_PARAM, DATABASE_ERROR
 */
function pubsub_adminapi_runjob($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    $invalid = array();
    if (!isset($id) || !is_numeric($id)) $invalid[] = 'id';
    if (!isset($pubsubid) || !is_numeric($pubsubid)) $invalid[] = 'pubsubid';
    if (!isset($objectid) || !is_numeric($objectid)) $invalid[] = 'objectid';
    if (!isset($id) || !is_numeric($id)) $invalid[] = 'id';

    if (count($invalid) > 0) {
        $msg = xarML('Invalid #(1) function #(3)() in module #(4)',
                    join(', ',$invalid), 'runjob', 'Pubsub');
        throw new Exception($msg);
    }

    // Database information
    $dbconn =& xarDB::getConn();
    $xartable =& xarDB::getTables();
    $pubsubsubscriptionstable = $xartable['pubsub_subscriptions'];
    $pubsubeventstable = $xartable['pubsub_events'];

    // Get info on job to run
    $query = "SELECT actionid,
                     userid,
                     $pubsubsubscriptionstable.eventid,
                     modid,
                     itemtype,
                     $pubsubsubscriptionstable.email
              FROM $pubsubsubscriptionstable
              LEFT JOIN $pubsubeventstable
              ON $pubsubsubscriptionstable.eventid = $pubsubeventstable.eventid
              WHERE pubsubid = ?";
    $result   = $dbconn->Execute($query, array((int)$pubsubid));
    if (!$result) return;

    if ($result->EOF) return;

    list($actionid,$userid,$eventid,$modid,$itemtype,$email) = $result->fields;

    if( $userid != -1 ) {
        $info = xarUser::getVar('email',$userid);
        $name = xarUser::getVar('uname',$userid);
    } else {
        $emailinfo = explode(' ',$email,2);
        $info    = $emailinfo[0];
        if( isset($emailinfo[1]) ) {
            $name = $emailinfo[1];
        } else {
            $name = '';
        }
    }

    $modinfo = xarMod::getInfo($modid);
    if (empty($modinfo['name'])) {
        $msg = xarML('Invalid #(1) function #(3)() in module #(4)', 'module', 'runjob', 'Pubsub');
        throw new Exception($msg);
    } else {
        $modname = $modinfo['name'];
    }

    switch ($actionid)
    {
        case 1:
            $action = 'mail';
            break;
        case 2: // currently unused
            $action = 'htmlmail';
            break;
        default:
            $action = 'unknown';
            break;
    }

    if ($action == "mail" || $action == "htmlmail") {
        // Database information
        $pubsubtemplatestable = $xartable['pubsub_templates'];
        // Get the (compiled) template to use
        $query = "SELECT compiled, template
                  FROM $pubsubtemplatestable
                  WHERE id = ?";
        $result   = $dbconn->Execute($query, array((int)$id));
        if (!$result) return;

        if ($result->EOF) {
            $msg = xarML('Invalid #(1) template', 'Pubsub');
            throw new Exception($msg);
        }

        $compiled = $result->fields[0];
        $templatecontent = $result->fields[1];

        if (empty($compiled)) {
            $msg = xarML('Invalid #(1) template', 'Pubsub');
            throw new Exception($msg);
        }
        // Close the result
        $result->Close();

        $tplData = array();
        $tplData['userid'] = $userid;
        $tplData['name'] = $name;
        $tplData['module'] = $modname;
        $tplData['itemtype'] = $itemtype;
        $tplData['itemid'] = $objectid;
        $tplData['templatecontent'] =$templatecontent;

        // (try to) retrieve a title and link for this item
        $itemlinks = xarMod::apiFunc($modname,'user','getitemlinks',
                                   array('itemtype' => $itemtype,
                                         'itemids' => array($objectid)),
                                   0); // don't throw an exception here
        if (!empty($itemlinks) && !empty($itemlinks[$objectid])) {
            $tplData['title'] = $itemlinks[$objectid]['label'];
            $tplData['link'] =  $itemlinks[$objectid]['url'];
        } else {
            $tplData['title'] = xarML('Item #(1)', $objectid);
            $tplData['link'] =  xarModURL($modname,'user','main');
        }

        // *** TODO  ***
        // need to define some variables for user firstname and surname,etc.
        // might not be able to use the normal BL user vars as they would
        // probably expand to currently logged in user, not the user for
        // this event.
        // But you can use $userid to get the relevant user, as above...

         if( xarModVars::get('pubsub','subjecttitle') == 1 ) {
             $subject = $tplData['title'];
         } else {
             $subject = xarML('Publish / Subscribe Notification');
         }
         $fmail = xarModVars::get('role', 'adminmail');
         $fname = xarModVars::get('role', 'adminmail');

        // call BL with the (compiled) template to parse it and generate the HTML free plaintext version
        $html = xarTplString($compiled, $tplData);
        $tplData['htmlcontent'] = $html;
        $tplData['textcontent'] = strip_tags($html);

        $UseTemplateVersions = xarModVars::get('pubsub', 'UseTemplateVersions') ? true : false;
        if ($UseTemplateVersions) {
             $htmltemplate = 'html-' . $id;
             $texttemplate = 'text-' . $id;
        } else {
             $htmltemplate = 'html';
             $texttemplate = 'text';
        }

        $htmlmessage= xarTplModule('pubsub','user','mail',$tplData,$htmltemplate);
        if (xarCurrentErrorID() == 'TEMPLATE_NOT_EXIST') {
            xarErrorHandled();
            // Default to the module template
            $htmlmessage= xarTplModule('pubsub', 'user', 'mail',$tplData,'html');
        }
        $textmessage= xarTplModule('pubsub','user','mail', $tplData,$texttemplate);
        if (xarCurrentErrorID() == 'TEMPLATE_NOT_EXIST') {
            xarErrorHandled();
            $textmessage= xarTplModule('pubsub', 'user', 'mail',$tplData,'text');
        }

            /*
            $boundary = "b" . md5(uniqid(time()));
            $message = "From: xarConfigGetVar('adminmail')\r\nReply-to: xarConfigGetVar('adminmail')\r\n";
            $message .= "Content-type: multipart/mixed; ";
            $message .= "boundary = $boundary\r\n\r\n";
            $message .= "This is a MIME encoded message.\r\n\r\n";
            // first the plaintext message
            $message .= "--$boundary\r\n";
            $message .= "Content-type: text/plain\r\n";
            $message .= "Content-Transfer-Encoding: base64";
            $message .= "\r\n\r\n" . chunk_split(base64_encode($plaintext)) . "\r\n";
            // now the HTML version
            $message .= "--$boundary\r\n";
            $message .= "Content-type: text/html\r\n";
            $message .= "Content-Transfer-Encoding: base64";
            $message .= "\r\n\r\n" . chunk_split(base64_encode($html)) . "\r\n";

            */
        if ($actionid == 2 ) {
             // Send the mail using the mail module
             if (!xarMod::apiFunc('mail',
                                'admin',
                                'sendhtmlmail',
                                array('info'     => $info,
                                      'name'     => $name,
                                      'subject'  => $subject,
                                      'message'  => $textmessage,
                                      'htmlmessage' => $htmlmessage,
                                      'from'     => $fmail,
                                      'fromname' => $fname,
                                      'usetemplates' => false))) return;
        } else {
             // plaintext mail
             if (!xarMod::apiFunc('mail',
                                'admin',
                                'sendmail',
                                array('info'     => $info,
                                      'name'     => $name,
                                      'subject'  => $subject,
                                      'message'  => $textmessage,
                                      'htmlmessage' => $htmlmessage,
                                      'from'     => $fmail,
                                      'fromname' => $fname,
                                      'usetemplates' => false))) return;
        }
        // delete job from queue now it has run
        xarMod::apiFunc('pubsub','admin','deljob', array('id' => $id));

    } else {
        // invalid action - update queue accordingly
        xarMod::apiFunc('pubsub','admin','updatejob',
                      array('id' => $id,
                            'pubsub_id' => $pubsub_id,
                            'object_id' => $object_id,
                            'template_id' => $template_id,
                            'status' => 'error'));
        $msg = xarML('Invalid #(1) action',
                     'Pubsub');
        throw new Exception($msg);
    }
    return true;
}

?>
