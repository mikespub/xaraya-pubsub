<?php 
// File: $Id$
// ----------------------------------------------------------------------
// Xaraya eXtensible Management System
// Copyright (C) 2002 by the Xaraya Development Team.
// http://www.xaraya.org
// ----------------------------------------------------------------------

/**
 * create a new pubsub event
 * @param $args['module'] name of the module this event applies to
 * @param $args['eventtype'] the event type
 * @returns int
 * @return event ID on success, false on failure
 * @raise BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */
function pubsub_adminapi_addevent($args)
{
// This function will create a new 

    // Get arguments from argument array
    extract($args);
    $invalid = array();
    if (!isset($module) || !is_string($module)) {
        $invalid[] = 'module';
    }
    if (!isset($eventtype) || !is_string($eventtype)) {
        $invalid[] = 'eventtype';
    }
    if (count($invalid) > 0) {
        $msg = xarML('Invalid #(1) function #(3)() in module #(4)',
                    join(', ',$invalid), 'addevent', 'Pubsub');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return;
    }

    // Security check
    if (!xarSecAuthAction(0, 'Pubsub', '::', ACCESS_ADD)) {
	$msg = xarML('Not authorized to add #(1) items',
                    'Pubsub');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'NO_PERMISSION',
                       new SystemException($msg));
        return;
    }

    // Get datbase setup
    list($dbconn) = xarDBGetConn();
    $xartable = xarDBGetTables();
    $pubsubeventstable = $xartable['pubsub_events'];

    // check this event isn't already in the DB
    $sql = "SELECT xar_eventid
 	    FROM $pubsubeventstable
	    WHERE xar_module '" . xarVarPrepForStore($module) . "',
	          xar_eventtype '" . xarVarPrepForStore($eventtype) . "'";
    $result = $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        $msg = xarMLByKey('DATABASE_ERROR', $sql);
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'DATABASE_ERROR',
                       new SystemException(__FILE__.'('.__LINE__.'): '.$msg));
        return;
    } elseif (count($result) > 0) {
        $msg = xarML('Item already exists in function #(1)() in module #(2)',
                    'addevent', 'Pubsub');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                      new SystemException($msg));
        return;
    }
    
    // Get next ID in table
    $nextId = $dbconn->GenId($pubsubeventstable);

    // Add item
    $sql = "INSERT INTO $pubsubeventstable (
              xar_eventid,
              xar_module,
              xar_eventtype)
            VALUES (
              $nextId,
              '" . xarVarPrepForStore($module) . "',
              '" . xarvarPrepForStore($eventtype) . "')";
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
	$msg = xarMLByKey('DATABASE_ERROR', $sql);
	xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'DATABASE_ERROR',
                       new SystemException(__FILE__.'('.__LINE__.'): '.$msg));
        return;
    }

    // return eventID
    return $nextId;
}

/**
 * delete a pubsub event
 * @param $args['eventid'] ID of the item
 * @returns bool
 * @return true on success, false on failure
 * @raise BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */
function pubsub_adminapi_delevent($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    $invalid = array();
    if (!isset($eventid) || !is_numeric($eventid)) {
        $invalid[] = 'eventid';
    }
    if (count($invalid) > 0) {
        $msg = xarML('Invalid #(1) function #(3)() in module #(4)',
                    join(', ',$invalid) 'delevent', 'Pubsub');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return;
    }

    // Security check
    if (!xarSecAuthAction(0, 'Pubsub', '::', ACCESS_DELETE)) {
	$msg = xarML('Not authorized to delete #(1) items',
                    'Pubsub');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'NO_PERMISSION',
                       new SystemException($msg));
        return;
    }

    // Get datbase setup
    list($dbconn) = xarDBGetConn();
    $xartable = xarDBGetTables();
    $pubsubeventstable = $xartable['pubsub_events'];

    // Delete item
    $sql = "DELETE FROM $pubsubeventstable
            WHERE xar_eventid = '" . xarVarPrepForStore($eventid) . "'";
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
 	$msg = xarMLByKey('DATABASE_ERROR', $sql);
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'DATABASE_ERROR',
                       new SystemException(__FILE__.'('.__LINE__.'): '.$msg));
        return;
    }

    return true;
}

/**
 * update an existing pubsub event
 * @param $args['eventid'] the ID of the item
 * @param $args['module'] the new module name of the item
 * @param $args['eventtype'] the new event type of the item
 * @param $args['groupdescr'] the new group description of the item
 * @param $args['actionid'] the new action id for the item
 * @returns bool
 * @return true on success, false on failure
 * @raise BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */
function pubsub_adminapi_updateevent($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    $invalid = array();
    if (!isset($eventid) || !is_numeric($eventid)) {
        $invalid[] = 'eventid';
    }
    if (!isset($module) || !is_string($module)) {
        $invalid[] = 'module';
    }
    if (!isset($eventtype) || !is_string($eventtype)) {
        $invalid[] = 'eventtype';
    }
    if (!isset($groupdescr) || !is_string($groupdescr)) {
        $invalid[] = 'groupdescr';
    }
    if (!isset($actionid) || !is_numeric($actionid)) {
        $invalid[] = 'actionid';
    }
    if (count($invalid) > 0) {
        $msg = xarML('Invalid #(1) function #(3)() in module #(4)',
                    join(', ',$invalid), 'updateevent', 'Pubsub');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return;
    }

    // Security check
    if (!xarSecAuthAction(0, 'Pubsub', "$name::$eventid", ACCESS_EDIT)) {
	$msg = xarML('Not authorized to edit #(1) items',
                    'Pubsub');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'NO_PERMISSION',
                       new SystemException($msg));
        return;
    }

    // Get database setup
    list($dbconn) = xarDBGetConn();
    $xartable = xarDBGetTables();
    $pubsubeventstable = $xartable['pubsub_events'];

    // Update the item
    $sql = "UPDATE $pubsubeventstable
            SET xar_module = '" . xarVarPrepForStore($module) . "',
                xar_eventtype = '" . xarVarPrepForStore($eventtype) . "',
                xar_groupdescr = '" . xarVarPrepForStore($groupdescr) . "',
                xar_actionid = '" . xarVarPrepForStore($actionid) . "'
            WHERE xar_eventid = '" . xarVarPrepForStore($eventid) . "'";
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
 	$msg = xarMLByKey('DATABASE_ERROR', $sql);
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'DATABASE_ERROR',
                       new SystemException(__FILE__.'('.__LINE__.'): '.$msg));
        return;
    }

    return true;
}

/**
 * process a pubsub event and add it to the Queue
 * @param $args['pubsubid'] subscription identifier
 * @param $args['objectid'] the specific object in the module
 * @returns int
 * @return handling ID on success, false on failure
 * @raise BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */
function pubsub_adminapi_processevent($args)
{
// This function will create a new 

    // Get arguments from argument array
    extract($args);
    $invalid = array();
    if (!isset($pubsubid) || !is_numeric($pubsubid)) {
        $invalid[] = 'pubsubid';
    }
    if (!isset($objectid) || !is_numeric($objectid)) {
        $invalid[] = 'objectid';
    }
    if (count($invalid) > 0) {
        $msg = xarML('Invalid #(1) function #(3)() in module #(4)',
                    join(', ',$invalid), 'processevent', 'Pubsub');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM', 
                       new SystemException($msg));
        return;
    }

    // Security check
    if (!xarSecAuthAction(0, 'Pubsub', '::', ACCESS_ADD)) {
	$msg = xarML('Not authorized to add #(1) items',
                    'Pubsub');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'NO_PERMISSION',
                       new SystemException($msg));
        return;
    }

    // Get datbase setup
    list($dbconn) = xarDBGetConn();
    $xartable = xarDBGetTables();
    $pubsubprocesstable = $xartable['pubsub_process'];

    // Get next ID in table
    $nextId = $dbconn->GenId($pubsubprocesstable);

    // Add item
    $sql = "INSERT INTO $pubsubprocesstable (
              xar_handlingid,
              xar_pubsubid,
              xar_objectid,
	      xar_status)
            VALUES (
              $nextId,
              '" . xarVarPrepForStore($pubsubid) . "',
              '" . xarvarPrepForStore($objectid) . "',
              '" . xarvarPrepForStore('pending') . "')";
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
 	$msg = xarMLByKey('DATABASE_ERROR', $sql);
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'DATABASE_ERROR',
                       new SystemException(__FILE__.'('.__LINE__.'): '.$msg));
        return;
    }
    // TODO implement queuing properly
    // for now we'll just go parse the queue immediately
    pubsub_adminapi_processq();

    // return handlingID
    return $nextId;
}

/**
 * Process the queue and run all pending jobs
 * @returns bool
 * @return number of jobs run on success, false if not
 * @raise DATABASE_ERROR
 */
function pubsub_adminapi_processq($args)
{
    // Get arguments from argument array
    extract($args);

    // Database information
    list($dbconn) = xarDBGetConn(); 
    $xartable = xarDBGetTables();
    $pubsubprocesstable = $xartable['pubsub_process'];

    // Get all jobs in pending state
    $sql = "SELECT xar_pubsubid,
    		   xar_objectid
            FROM $pubsubprocesstable
            WHERE xar_status = '" . xarVarPrepForStore('pending') . "'";
    $result = $dbconn->Execute($sql);
    // set count to 0
    $count = 0;

    if ($dbconn->ErrorNo() != 0) {
 	$msg = xarMLByKey('DATABASE_ERROR', $sql);
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'DATABASE_ERROR',
                       new SystemException(__FILE__.'('.__LINE__.'): '.$msg));
        return;
    } else {
        while (!$result->EOF) {
            // run the job passing it the pubsub and object ids.
	    pubsub_adminapi_runjob($result->fields[0], $result->fields[1]);
	    $count++;
	    $result->MoveNext();
        }
    }
    return $count;
}

/**
 * run the job
 * @param $args['pubsubid'] the subscription id
 * @param $args['objectid'] the specific object in the module
 * @returns bool
 * @return true on success, false on failure
 * @raise BAD_PARAM, DATABASE_ERROR
 */
function pubsub_adminapi_runjob($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    $invalid = array();
    if (!isset($pubsubid) || !is_numeric($pubsubid)) {
        $invalid[] = 'pubsubid';
    }
    if (!isset($objectid) || !is_numeric($objectid)) {
        $invalid[] = 'objectid';
    }
    if (count($invalid) > 0) {
        $msg = xarML('Invalid #(1) function #(3)() in module #(4)',
                    join(', ',$invalid), 'runjob', 'Pubsub');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return;
    }
    
    // Database information
    list($dbconn) = xarDBGetConn(); 
    $xartable = xarDBGetTables();
    $pubsubregtable = $xartable['pubsub_reg'];

    // Get info on job to run
    $sql = "SELECT xar_actionid,
    		   xar_eventid
            FROM $pubsubregtable
            WHERE xar_pubsubid = '" . xarVarPrepForStore($pubsubid) . "'";
    $result   = $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
 	$msg = xarMLByKey('DATABASE_ERROR', $sql);
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'DATABASE_ERROR',
                       new SystemException(__FILE__.'('.__LINE__.'): '.$msg));
        return;
    }
    $actionid = $result->fields[0];
    $eventid  = $result->fields[1]);
    list($action,$info)   =  explode(':', $actionid);
    if ($action = "mail" || $action = "htmlmail") {
	// check mail address is a valid email address
	if (!eregi("^([A-Za-z0-9_]|\\-|\\.)+@(([A-Za-z0-9_]|\\-)+\\.)[A-Za-z]{2,4}$", $info)) {
	    // address invalid
	    $msg = xarML('Invalid E-mail address #(1)',
                    $info);
            xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                     new SystemException($msg));
            return;
	} else {
       	    // Database information
    	    $pubsubtemplatetable = $xartable['pubsub_template'];
    	    // Get info on job to run
    	    $sql = "SELECT xar_template
            	    FROM $pubsubtemplatetable
            	    WHERE xar_eventid = '" . xarVarPrepForStore($eventid) . "'";
    	    $result   = $dbconn->Execute($sql);
            if ($dbconn->ErrorNo() != 0) {
 	         $msg = xarMLByKey('DATABASE_ERROR', $sql);
                 xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'DATABASE_ERROR',
                               new SystemException(__FILE__.'('.__LINE__.'): '.$msg));
                 return;
            }
    	    $template = $result->fields[0];
	    // *** TODO  ***
	    // need to define some variables for user firstname and surname,etc.
	    // might not be able to use the normal BL user vars as they would 
	    // probabaly expand to currently logged in user, not the user for 
	    // this event.
	    // need to create the $tplData array with all the information in it

	    // call BL with the template to parse it and generate the HTML
	    $html = xarTplString($template, $tplData);
	    $plaintext = strip_tags($html);
	    if ($action = "htmlmail") { 
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
	    
	       // send the mail
               mail($info,     // to
	            $subject,  // subject
		    '',        // empty mesage body as sending multipart messages
	            $message); // message
	    } else {
	       // send the mail
               mail($info,      // to
	            $subject,   // subject
		    $plaintext, // empty mesage body as sending multipart messages
	            "From: xarConfigGetVar('adminmail')\r\nReply-to: xarConfigGetVar('adminmail')\r\n"); 
	    }   
            // delete job from queue now it has run
	    pubsub_adminapi_deljob($handlingid);
        }
    } else {
        // invalid action - update queue accordingly
	pubsub_adminapi_updatejob($handlingid,$pubsubid,$objectid,'error');
	$msg = xarML('Invalid #(1) action',
                 'Pubsub');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                 new SystemException($msg));
        return;
    }
    return true;
}

/**
 * delete a pubsub job from the queue
 * @param $args['handlingid'] ID of the job to delete
 * @returns bool
 * @return true on success, false on failure
 * @raise BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */
function pubsub_adminapi_deljob($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    $invalid = array();
    if (!isset($handlingid) || !is_numeric($handlingid)) {
        $invalid[] = 'handlingid';
    }
    if (count($invalid) > 0) {
        $msg = xarML('Invalid #(1) function #(3)() in module #(4)',
                    join(', ',$invalid), 'deljob', 'Pubsub');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return;
    }

    // Security check
    if (!xarSecAuthAction(0, 'Pubsub', "::$handlingid", ACCESS_DELETE)) {
	$msg = xarML('Not authorized to delete #(1) items',
                    'Pubsub');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'NO_PERMISSION',
                       new SystemException($msg));
        return;
    }
    
    // Get datbase setup
    list($dbconn) = xarDBGetConn();
    $xartable = xarDBGetTables();
    $pubsubprocesstable = $xartable['pubsub_process'];

    // Delete item
    $sql = "DELETE FROM $pubsubprocesstable
            WHERE xar_handlingid = '" . xarVarPrepForStore($handlingid) . "'";
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
 	$msg = xarMLByKey('DATABASE_ERROR', $sql);
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'DATABASE_ERROR',
                       new SystemException(__FILE__.'('.__LINE__.'): '.$msg));
        return;
    }

    return true;
}

/**
 * update an existing pubsub job
 * @param $args['handlingid'] the ID of the item
 * @param $args['pubsubid'] the new pubsub id for the item
 * @param $args['objectid'] the new object id for the item
 * @param $args['status']   the new status for the item
 * @returns bool
 * @return true on success, false on failure
 * @raise BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */ 
function pubsub_adminapi_updatejob($args)
{
    // Get arguments from argument array
    extract($args);
    
    // Argument check
    $invalid = array(); 
    if (!isset($handlingid) || !is_numeric($handlingid)) {
        $invalid[] = 'handlingid';
    }
    if (!isset($pubsubid) || !is_numeric($pubsubid)) {
        $invalid[] = 'pubsubid';
    }
    if (!isset($objectid) || !is_numeric($objectid)) {
        $invalid[] = 'objectid';
    }
    if (!isset($status) || !is_string($status)) {
        $invalid[] = 'status';
    }
    if (count($invalid) > 0) {
        $msg = xarML('Invalid #(1) function #(3)() in module #(4)',
                    join(', ',$invalid), 'updatejob', 'Pubsub');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return;
    }

    // Security check
    if (!xarSecAuthAction(0, 'Pubsub', "::$handlingid", ACCESS_EDIT)) {
	$msg = xarML('Not authorized to edit #(1) items',
                    'Pubsub');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'NO_PERMISSION',
                       new SystemException($msg));
        return;
    }

    // Get database setup
    list($dbconn) = xarDBGetConn();
    $xartable = xarDBGetTables();
    $pubsubprocesstable = $xartable['pubsub_process'];

    // Update the item
    $sql = "UPDATE $pubsubprocesstable
            SET xar_pubsubid = '" . xarVarPrepForStore($pubsubid) . "',
                xar_objectid = '" . xarVarPrepForStore($objectid) . "',
                xar_status = '" . xarVarPrepForStore($status) . "'
            WHERE xar_handlingid = '" . xarVarPrepForStore($handlingid) . "'";
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
 	$msg = xarMLByKey('DATABASE_ERROR', $sql);
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'DATABASE_ERROR',
                       new SystemException(__FILE__.'('.__LINE__.'): '.$msg));
        return;
    }    
    return true;
}

/**
 * create a new pubsub template
 * @param $args['eventid'] name of the event this template applies to
 * @param $args['template'] the template text
 * @returns int
 * @return template ID on success, false on failure
 * @raise BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */
function pubsub_adminapi_addtemplate($args)
{
// This function will create a new 

    // Get arguments from argument array
    extract($args);
    $invalid = array(); 
    if (!isset($template) || !is_string($template)) {
        $invalid[] = 'template';
    }
    if (!isset($eventid) || !is_numeric($eventid)) {
        $invalid[] = 'eventid';
    }
    if (count($invalid) > 0) {
        $msg = xarML('Invalid #(1) function #(3)() in module #(4)',
                    join(', ',$invalid), 'addtemplate', 'Pubsub');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return;
    }

    // Security check
    if (!xarSecAuthAction(0, 'Pubsub', '::', ACCESS_ADD)) {
	$msg = xarML('Not authorized to add #(1) items',
                    'Pubsub');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'NO_PERMISSION',
                       new SystemException($msg));
        return;
    }

    // Get datbase setup
    list($dbconn) = xarDBGetConn();
    $xartable = xarDBGetTables();
    $pubsubtemplatetable = $xartable['pubsub_template'];

    // check this event isn't already in the DB
    $sql = "SELECT xar_templateid
            FROM $pubsubtemplatetable
            WHERE xar_eventid '" . xarVarPrepForStore($eventid) . "'";
    $result = $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        $msg = xarMLByKey('DATABASE_ERROR', $sql);
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'DATABASE_ERROR',
                       new SystemException(__FILE__.'('.__LINE__.'): '.$msg));
        return;
    } elseif (count($result) > 0) {
        $msg = xarML('Item already exists in function #(1)() in module #(2)',
                    'addtemplate', 'Pubsub');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                      new SystemException($msg));
        return;
    }

    // Get next ID in table
    $nextId = $dbconn->GenId($pubsubtemplatetable);

    // Add item
    $sql = "INSERT INTO $pubsubtemplatetable (
              xar_templateid,
              xar_eventid,
              xar_template)
            VALUES (
              $nextId,
              '" . xarVarPrepForStore($eventid) . "',
              '" . xarvarPrepForStore($template) . "')";
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
 	$msg = xarMLByKey('DATABASE_ERROR', $sql);
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'DATABASE_ERROR',
                       new SystemException(__FILE__.'('.__LINE__.'): '.$msg));
        return;
    }

    // return eventID
    return $nextId;
}

/**
 * delete a pubsub template
 * @param $args['templateid'] ID of the item
 * @returns bool
 * @return true on success, false on failure
 * @raise BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */
function pubsub_adminapi_deltemplate($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    $invalid = array();
    if (!isset($templateid) || !is_numeric($templateid)) {
        $invalid[] = 'templateid';
    }
    if (count($invalid) > 0) {
        $msg = xarML('Invalid #(1) function #(3)() in module #(4)',
                    join(', ',$invalid), 'deltemplate', 'Pubsub');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return;
    }

    // Security check
    if (!xarSecAuthAction(0, 'Pubsub', '::', ACCESS_DELETE)) {
	$msg = xarML('Not authorized to delete #(1) items',
                    'Pubsub');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'NO_PERMISSION',
                       new SystemException($msg));
        return;
    }

    // Get datbase setup
    list($dbconn) = xarDBGetConn();
    $xartable = xarDBGetTables();
    $pubsubtemplatetable = $xartable['pubsub_template'];

    // Delete item
    $sql = "DELETE FROM $pubsubtemplatetable
            WHERE xar_templateid = '" . xarVarPrepForStore($templateid) . "'";
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
 	$msg = xarMLByKey('DATABASE_ERROR', $sql);
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'DATABASE_ERROR',
                       new SystemException(__FILE__.'('.__LINE__.'): '.$msg));
        return;
    }

    return true;
}

/**
 * update an existing pubsub template
 * @param $args['templateid'] the ID of the item
 * @param $args['eventid'] the new eventid of the item
 * @param $args['template'] the new template text of the item
 * @returns bool
 * @return true on success, false on failure
 * @raise BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */
function pubsub_adminapi_updatetemplate($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    $invalid = array(); 
    if (!isset($eventid) || !is_numeric($eventid)) {
        $invalid[] = 'eventid';
    }
    if (!isset($temnplateid) || !is_numeric($templateid)) {
        $invalid[] = 'templateid';
    }
    if (!isset($template) || !is_string($template)) {
        $invalid[] = 'template';
    }
    if (count($invalid) > 0) {
        $msg = xarML('Invalid #(1) function #(3)() in module #(4)',
                    join(', ',$invalid), 'updatetemplate', 'Pubsub');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return;
    }

    // Security check
    if (!xarSecAuthAction(0, 'Pubsub', "$name::$templateid", ACCESS_EDIT)) {
	$msg = xarML('Not authorized to edit #(1) items',
                    'Pubsub');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'NO_PERMISSION',
                       new SystemException($msg));
        return;
    }

    // Get database setup
    list($dbconn) = xarDBGetConn();
    $xartable = xarDBGetTables();
    $pubsubtemplatetable = $xartable['pubsub_template'];

    // Update the item
    $sql = "UPDATE $pubsubtemplatetable
            SET xar_template = '" . xarVarPrepForStore($template) . "',
                xar_eventid = '" . xarVarPrepForStore($eventid) . "'
            WHERE xar_templateid = '" . xarVarPrepForStore($templateid) . "'";
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
 	$msg = xarMLByKey('DATABASE_ERROR', $sql);
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'DATABASE_ERROR',
                       new SystemException(__FILE__.'('.__LINE__.'): '.$msg));
        return;
    }

    return true;
}
?>