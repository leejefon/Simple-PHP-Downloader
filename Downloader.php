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

		if ($file_path == null) {
			echo "File not found";;
		} else {
			$file_path = $_SERVER["DOCUMENT_ROOT"] . $file_path;
		}

		if (isset($this->options["zip"]) && $this->options["zip"] == true) {
			// check already zipped
			// do zip
		}

		if (file_exists($file_path)) {
			header('Content-Description: File Transfer');
			header('Content-Type: ' . mime_content_type($file_path));
			header('Content-Disposition: attachment; filename=' . $this->encodeFilename(basename($file_path)));
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
			// unlink file
		}

		exit;
	}

	private function getFile($uri, $files) {
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
}

?>
