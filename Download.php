<?php
/**
 * Download
 *
 * Routes of all the download files
 *
 * Written By: Jeff Lee
 * Created On: 2012/05
 */

class Download {

	protected $files = array(
		'docs' => array(
			'Resume' => '/about/docs/Resume.pdf',
		),

		'cert' => array(
			'CCNA' => '/about/certPDF/Cisco Certified Network Associate (Scan).pdf',
		),

		'code' => array(
			'ece297-server' => '/project/code.repo/c/database_server/src.tgz',
		),

		'server2go' => '/_backup.private/server2go.zip',
	);

	// Defaults:
	//   auth = null,
	//   zip = false
	protected $options = array(
		'server2go' => array(
			'auth' => 'auth1',
		),

		'code' => array(
			'baseConverter.exe' => array( 'zip' => true ),
		)
	);

	protected $auth = array(
		'auth1' => array(
			'realm' => 'Member Only',
			'type' => 'database/mysql',
			'dbname' => 'database_name',
			'table' => 'table_name',
			'user' => 'user_login',
			'pass' => 'user_pass',
			'hash' => 'md5',
			'salt' => 'aoiusdifuowe%s'
		)
	);
}

?>
