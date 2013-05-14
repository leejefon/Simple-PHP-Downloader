<?php
/**
 * Downloader
 *
 * Manage the routes and the authentications
 *
 * Written By: Jeff Lee
 * Created On: 2012/05
 */

require_once("Download.php");

class Downloader extends Download {

	private $uri;

	private $mysql = array(
		'host' => 'localhost',
		'port' => '3306',
		'user' => 'DB_USER',
		'pass' => 'DB_PASS'
	);

	public function __construct() {

	}

	public function download($uri = null) {

		if ($uri == null) {
			return;
		}

		$this->uri = $uri;

		$this->authenticate();

		$file_path = $this->getFile($this->uri, $this->files);

		$file_name = $this->encodeFilename(basename($file_path));

		if ($file_path == null) {
			echo "File not found";;
		} else {
			$file_path = $_SERVER["DOCUMENT_ROOT"] . $file_path;
		}

		if (isset($this->options["zip"]) && $this->options["zip"] == true) {
			// check already zipped
			
			$file_name .= ".zip";
			$this->createZip(array($file_path), $file_name);
			$file_path = $file_name;
		}

		if (file_exists($file_path)) {
			header('Content-Description: File Transfer');
			header('Content-Type: ' . mime_content_type($file_path));
			header('Content-Disposition: attachment; filename=' . $file_name);
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
			header("Cache-Control: private",false); // required for certain browsers 
			header('Pragma: public');
			header('Content-Length: ' . filesize($file_path));
			ob_clean();
			flush();
			readfile($file_path);
		}

		if (isset($this->options["zip"]) && $this->options["zip"] == true) {
			unlink($file_path);
		}

		exit;
	}

	private function getFile($uri, $files) {
		if ($this->endsWith($uri[count($uri) - 1], '.zip')) {
			$this->options['zip'] = true;
			$uri[count($uri) - 1] = str_replace(".zip", "", $uri[count($uri) - 1]);
		}

		foreach ($files as $key => $val) {
			if ($uri[1] == $key) {
				if (is_array($val)) {
					array_shift($uri);
					return $this->getFile($uri, $val);
				}

				return $val;
			}
		}

		return null;
	}

	private function encodeFilename($file) {
		return str_replace(" ", "_", $file);
	}

	private function endsWith($haystack, $needle) {
		$length = strlen($needle);
		if ($length == 0) {
			return true;
		}

		return (substr($haystack, -$length) === $needle);
	}

	private function getAuth() {
		$this->options = $this->getOptions();
		if ($this->options != null) {
			if (isset($this->options["auth"])) {
				return $this->auth[$this->options["auth"]];
			}
		}
		return null;
	}

	private function getOptions() {
		$level = count($this->uri);
		$options = $this->options;

		for ($i = 1; $i < $level; $i++) {
			if (isset($options[$this->uri[$i]])) {
				$options = $options[$this->uri[$i]];
			}
		}

		if ($i == $level) {
			return $options;
		} else {
			return null;
		}
	}

	private function authenticate() {
		$auth = $this->getAuth();

		if ($auth == null) {
			return;
		}

		if (!isset($_SERVER["PHP_AUTH_USER"])) {
			header('WWW-Authenticate: Basic realm="' . $auth["realm"] . '"');
			header('HTTP/1.0 401 Unauthorized');
			echo 'The file requires authentication';
			exit;
		}

		$username = $_SERVER["PHP_AUTH_USER"]; // handle hash and salt
		$password = call_user_func($auth["hash"], $auth["salt"] . $_SERVER["PHP_AUTH_PW"]);

		$type = explode("/", $auth["type"]);

		if ($type[0] == "database") {

			if ($type[1] == "mysql") {
				$conn = "mysql:host=" . $this->mysql["host"] . ";dbname=" . $auth["dbname"];
			}

			try {
				$db = new PDO($conn, $this->mysql["user"], $this->mysql["pass"]);

				$query = "SELECT COUNT(*) FROM " . $auth["table"] . " WHERE " . $auth["user"] . " = :username AND " . $auth["pass"] . " = :password";

				$statement = $db->prepare($query);

				$statement->bindValue(':username', $username, PDO::PARAM_STR);
				$statement->bindValue(':password', $password, PDO::PARAM_STR);

				$statement->execute();

				if ($statement->fetchColumn() != 1) {
					header('WWW-Authenticate: Basic realm="' . $auth["realm"] . '"');
					header('HTTP/1.0 401 Unauthorized');
					echo "wrong pass";
					exit;
				}

			} catch (PDOException $e) {
				var_dump($e);
				exit;
			}
		}

		return;
	}

	// Source: http://davidwalsh.name/create-zip-php
	function createZip($files = array(), $destination = '', $overwrite = false) {
		//if the zip file already exists and overwrite is false, return false
		if (file_exists($destination) && !$overwrite) { return false; }
		//vars
		$valid_files = array();

		//if files were passed in...
		if (is_array($files)) {
			//cycle through each file
			foreach ($files as $file) {
				//make sure the file exists
				if (file_exists($file)) {
					$valid_files[] = $file;
				}
			}
		}

		//if we have good files...
		if (count($valid_files)) {
			//create the archive
			$zip = new ZipArchive();
			if ($zip->open($destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
				return false;
			}
			//add the files
			foreach ($valid_files as $file) {
				$zip->addFile($file, basename($file));
			}
			//debug
			//echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
		
			//close the zip -- done!
			$zip->close();
		
			//check to make sure the file exists
			return file_exists($destination);
		} else {
			return false;
		}
	}
}

?>
