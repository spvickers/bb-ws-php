<?php
/*
 *  bb_ws-php - An example script accessing the Blackboard Learn 9 web services using PHP
 *  Copyright (C) 2013  Stephen P Vickers
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * Contact: stephen@spvsoftwareproducts.com
 *
 * Version history:
 *   1.0.0  10-Feb-13  Initial version
*/

// Load configuration settings
  require_once('config.php');

// Load dependent library files
  require_once('lib.php');

// Print header
  print 'Learn 9 server: ' . SERVER_URL . "\n";
  print 'Proxy tool:     ' . VENDOR_ID . '/' . PROGRAM_ID . "\n\n";

  $ok = isset($argv[1]);
  $err = FALSE;

  if ($ok) {

// Initialise a Context SOAP client object
    try {
      $context_client = new BbSoapClient(SERVER_URL . '/webapps/ws/services/Context.WS?wsdl');
    } catch (Exception $e) {
      $ok = FALSE;
      print "ERROR: {$e->getMessage()}\n";
    }

  }

  if ($ok) {

    $action = strtolower($argv[1]);

    if ($action == 'register') {
##
#### Register the tool
##
      print 'Registering tool... ';
      $register_tool = new stdClass();
      $register_tool->clientVendorId = VENDOR_ID;
      $register_tool->clientProgramId = PROGRAM_ID;
      $register_tool->registrationPassword = REGISTRATION_PASSWORD;
      $register_tool->description = TOOL_DESCRIPTION;
      $register_tool->initialSharedSecret = SHARED_SECRET;
      $register_tool->requiredToolMethods =   array('Context.WS:loginTool', 'Context.WS:getMemberships', 'User.WS:getUser', 'Course.WS:getCourse');
      try {
        $result = $context_client->registerTool($register_tool);
        $ok = $result->return->status;
        if ($ok) {
          print "Success (now make the proxy tool available in Learn 9)\n";
        } else {
          $err = TRUE;
          print "Failed (may already be registered)\n";
        }
      } catch (Exception $e) {
        $err = TRUE;
        print "ERROR: {$e->getMessage()}\n";
      }

    } else {

// Get a session ID
      try {
        $result = $context_client->initialize();
        $password = $result->return;
      } catch (Exception $e) {
        $err = TRUE;
        print "ERROR: {$e->getMessage()}\n";
      }

      if (!$err) {

// Log in as a tool
        $input = new stdClass();
        $input->password = SHARED_SECRET;
        $input->clientVendorId = VENDOR_ID;
        $input->clientProgramId = PROGRAM_ID;
        $input->loginExtraInfo = '';  // not used but must not be NULL
        $input->expectedLifeSeconds = 3600;
        try {
          $result = $context_client->loginTool($input);
        } catch (Exception $e) {
          $err = TRUE;
          print "ERROR: {$e->getMessage()}\n";
        }
      }

      if (!$err) {

        if ($action == 'courses') {
##
#### Get courses for a user
##
          $ok = isset($argv[2]);
          if ($ok) {
            print 'Retrieving courses...';

            $member = new stdClass();
            $member->userid = $argv[2];
            try {
              $result = $context_client->getMemberships($member);
              for ($i = 0; $i < count($result->return); $i++) {
                print " {$result->return[$i]->externalId}";
              }
              print "\n";
            } catch (Exception $e) {
              $err = TRUE;
              print "ERROR: {$e->getMessage()}\n";
            }

          }

        } else if ($action == 'course') {
##
#### Get course details
##
          $ok = isset($argv[2]);

          if ($ok) {
// Initialise a Course SOAP client object
            try {
              $course_client = new BbSoapClient(SERVER_URL . '/webapps/ws/services/Course.WS?wsdl');
            } catch (Exception $e) {
              $ok = FALSE;
              print "ERROR: {$e->getMessage()}\n";
            }
          }

          if ($ok) {
            print 'Retrieving course... ';
            $course = new stdClass();
            $course->filter = new stdClass();
            if (substr($argv[2], 0, 1) == '_') {
              $course->filter->ids = array($argv[2]);
              $course->filter->filterType = 3;
            } else {
              $course->filter->courseIds = array($argv[2]);
              $course->filter->filterType = 1;
            }
            try {
              $result = $course_client->getCourse($course);
              $ok = $result->return;
              if ($ok) {
                print "{$result->return->name} ";
                if ($course->filter->filterType == 3) {
                  print "({$result->return->courseId})\n";
                } else {
                  print "({$result->return->id})\n";
                }
              } else {
                $err = TRUE;
                print "not found\n";
              }
            } catch (Exception $e) {
              $err = TRUE;
              print "ERROR: {$e->getMessage()}\n";
            }
          }

        } else if ($action == 'user') {
##
#### Get user details
##
          $ok = isset($argv[2]);

          if ($ok) {
// Initialise a User SOAP client object
            try {
              $user_client = new BbSoapClient(SERVER_URL . '/webapps/ws/services/User.WS?wsdl');
            } catch (Exception $e) {
              $ok = FALSE;
              print "ERROR: {$e->getMessage()}\n";
            }
          }

          if ($ok) {
            print 'Retrieving user... ';
            $user = new stdClass();
            $user->filter = new stdClass();
            if (substr($argv[2], 0, 1) == '_') {
              $user->filter->id = array($argv[2]);
              $user->filter->filterType = 2;
            } else {
              $user->filter->name = array($argv[2]);
              $user->filter->filterType = 6;
            }
            try {
              $result = $user_client->getUser($user);
              $ok = $result->return;
              if ($ok) {
                print "{$result->return->extendedInfo->givenName} {$result->return->extendedInfo->familyName} ";
                if ($user->filter->filterType == 2) {
                  print "({$result->return->name})\n";
                } else {
                  print "({$result->return->id})\n";
                }
              } else {
                $err = TRUE;
                print "not found\n";
              }
            } catch (Exception $e) {
              $err = TRUE;
              print "ERROR: {$e->getMessage()}\n";
            }
          }

        } else {

          $ok = FALSE;

        }

      }

    }

  }

  if (!$ok && !$err) {
##
#### Display usage information
##
    print "Usage:\n";
    print "  {$argv[0]} register            -- register the proxy tool\n";
    print "  {$argv[0]} courses {username}  -- retrieve a list of courses for a user\n";
    print "  {$argv[0]} course {course}     -- retrieve details of a course (id/courseId)\n";
    print "  {$argv[0]} user {user}         -- retrieve details of a user (id/username)\n";

  }

?>
