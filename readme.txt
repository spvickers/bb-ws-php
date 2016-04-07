These files are provided as-is to provide a simple illustration of one way in which Blackboard Learn 9 services can be accessed using PHP.  They have been tested with PHP 5.3.8.

System requirements:

  - PHP with SOAP library installed
  - WS-Security library (from http://code.google.com/p/wse-php/, files included with this script)
  - System admin access to Learn 9
  - Context, Course and User web services should be both available and discoverable (see Administrator Panel > Web Services page); if not using SSL then also ensure the "SSL Required" option is not enabled


Using the example script:

1.  Extract the PHP files into a single directory.
2.  Edit the config.php file to add the URL of your Learn 9 server and the Proxy Tool Registration Password (see the Administrator Panel > Building Blocks > Proxy Tools > Proxy Tools Global Properties page)
3.  From a command line prompt in the directory where the files have been placed run the bb_ws.php file (e.g. "php bb_ws.php"); this will display a page showing the available options
4.  Register the tool in Learn 9: "php bb_ws.php register".
5.  Make the tool available in Learn 9 (see Administrator Panel > Building Blocks > Proxy Tools page).
6.  The other options should now work; for example:
       php bb_ws.php courses administrator (list of courses for a user)
       php bb_ws.php course _2_1 (course details by id)
       php bb_ws.php course cid (course details by courseId)
       php bb_ws.php user _1_1 (user details by id)
       php bb_ws.php user administrator (user details by username)


Stephen P Vickers
10 February 2013
