<?php

class AIOMcache {
	private static $rootPath;
	private static $aiomCachePath;
	private static $logFile;

	/**
	 * Serve the requested page if cached and not expired
	 *
	 * @author Matjaž Potočnik
	 * @return bool false if content is not served from cache
	 *
	 */
	public static function cache() {
		self::$rootPath = self::getRootPath();
		self::$aiomCachePath = self::$rootPath . '/site/assets/cache/aiom/';
		self::$logFile = self::$rootPath . '/site/assets/logs/aiom.txt';

		//return if not a guest or is POST request or GET request has query string or caching not enabled
		if(isset($_COOKIE['wire_challenge']) || isset($_COOKIE['wires_challenge']) ||
			(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') ||
			(isset($_SERVER['QUERY_STRING']) && strpos($_SERVER['QUERY_STRING'], '&') !== false) ||
			!is_file(self::$aiomCachePath . 'aiom.enabled')) {
				//self::log('cache INFO: condition not met ' . $_SERVER['QUERY_STRING']);
				return false;
			}

		$it = isset($_GET['it']) ? $_GET['it'] : '';
		$it = trim($it, '/') . '/';
		if($it === '/') $it = '';

		$aiomCacheFile = self::$aiomCachePath . $it . 'cache.json';

		//some general logging
		//self::log("cache INFO: it: $it, queryString: " . $_SERVER['QUERY_STRING']);

		// check if AIOM cache file exist
		if(!is_file($aiomCacheFile) || !is_readable($aiomCacheFile)) {
			self::log("cache MISS: /$it no cache file $aiomCacheFile");
			return false;
		}

		// AIOM cache file exists, open it and get "real" Page cache file, cache time and template files
		//todo: use json
		$aiomCacheFileContent = file_get_contents($aiomCacheFile);
		$aiomCacheFileContentArr = json_decode($aiomCacheFileContent, true);
		if(json_last_error() !== JSON_ERROR_NONE || count($aiomCacheFileContentArr) < 4) {
			self::log("cache ERROR: invalid format of $aiomCacheFile");
			self::removeCacheFile($aiomCacheFile);
			return false;
		}

		$pageCacheFile = $aiomCacheFileContentArr[0];       //eg. C:/inetpub/wwwroot/site/assets/cache/Page/1/page2_1234.cache
		$pageCacheTime = $aiomCacheFileContentArr[1];       //eg. 3600 - not used
		$pageCacheExpireTime = $aiomCacheFileContentArr[2]; //eg. 1583141543
		$pageCacheExpireDate = $aiomCacheFileContentArr[3]; //eg. 2020-02-20 21:57:03 - not used
		$tplFiles = count($aiomCacheFileContentArr) > 4 ? $aiomCacheFileContentArr[4] : array(); //eg. C:/inetpub/wwwroot/site/templates/basic-page.php

		//self::log("cache INFO: cacheTime: $pageCacheTime, cacheExpireTime: $pageCacheExpireTime, cacheExpireDate: $pageCacheExpireDate");

		// check if page cache file expired
		if($pageCacheExpireTime < time()) {
			//self::log("cache INFO: $pageCacheFile expired");
			self::removeCacheFile($aiomCacheFile);
			return false;
		}

		// check if one of the template files is newer than page cachefile
		$pageCacheExpireFilemtime = @filemtime($pageCacheFile);
		foreach($tplFiles as $tplFile) {
			//self::log("cache INFO: checking $tplFile, " . date("Y-m-d H:i:s", @filemtime($tplFile)));
			if(is_file($tplFile) && filemtime($tplFile) > $pageCacheExpireFilemtime) {
				//template file is newer than cachefile, invalidate (remove) cache files for the page
				//self::log("cache INFO: $tplFile is newer than $pageCacheFile");
				self::removeCacheFile($aiomCacheFile);
				return false;
			}
		}

		//now return content from the page cache file
		$out = @file_get_contents($pageCacheFile);

		if($out === false) {
			//some error occured or page cache file is empty
			self::log("cache ERROR: /$it $pageCacheFile empty or nonexistent");
			self::removeCacheFile($aiomCacheFile);
			return false;
		}

		//we have a content, serve it
		$len = @mb_strlen($out, 'utf8');
		self::rewrite($out);
		self::log("cache HIT: /$it serving $pageCacheFile ($len bytes)");
		echo $out;
		exit(0);
	}

	/**
	 * Get root path, check it, and optionally auto-detect it if not provided
	 * Taken from /wire/core/ProcessWire.php
	 *
	 * @author Ryan Cramer, modified by Matjaž Potočnik
	 * @param bool|string $rootPath Root path if already known, in which case we’ll just modify as needed
	 *   …or specify boolean true to get absolute root path, which disregards any symbolic links to core.
	 * @return string
	 *
	 */
	private static function getRootPath($rootPath = '') {
		if($rootPath !== true && strpos($rootPath, '..') !== false) {
			$rootPath = realpath($rootPath);
		}

		if(empty($rootPath) && !empty($_SERVER['SCRIPT_FILENAME'])) {
			// first try to determine from the script filename
			$parts = explode(DIRECTORY_SEPARATOR, $_SERVER['SCRIPT_FILENAME']);
			array_pop($parts); // most likely: index.php
			$rootPath = implode('/', $parts) . '/';
			if(!file_exists($rootPath . 'wire/core/ProcessWire.php')) $rootPath = '';
		}

		if(empty($rootPath) || $rootPath === true) {
			// if unable to determine from script filename, attempt to determine from current file
			$parts = explode(DIRECTORY_SEPARATOR, __FILE__);
			$parts = array_slice($parts, 0, -3); // removes "ProcessWire.php", "core" and "wire"
			$rootPath = implode('/', $parts);
		}

		if(DIRECTORY_SEPARATOR != '/') {
			$rootPath = str_replace(DIRECTORY_SEPARATOR, '/', $rootPath);
		}

		//MP remove trailing / to be consistent with $_SERVER['DOCUMENT_ROOT']
		return rtrim($rootPath, '/');
	}

	/**
	 * Remove given $line from $chunk and add counter to end of $line indicating quantity that was removed
	 * Taken from /wire/core/FileLog.php
	 *
	 * @author Ryan Cramer
	 * @param string $line
	 * @param string $chunk
	 * @param int $chunkSize
	 * @since 3.0.143
	 *
	 */
	private static function removeLineFromChunk(&$line, &$chunk, $chunkSize) {

		$qty = 0;
		$chunkLines = explode("\n", $chunk);

		foreach($chunkLines as $key => $chunkLine) {

			$x = 1;
			if($key === 0 && strlen($chunk) >= $chunkSize) continue; // skip first line since it’s likely a partial line

			// check if line appears in this chunk line
			if(strpos($chunkLine, $line) === false) continue;

			// check if line also indicates a previous quantity that we should add to our quantity
			if(strpos($chunkLine, ' ^+') !== false) {
				list($chunkLine, $n) = explode(' ^+', $chunkLine, 2);
				if(ctype_digit($n)) $x += (int) $n;
			}

			// verify that these are the same line
			if(strpos(trim($chunkLine) . "\n", trim($line) . "\n") === false) continue;

			// remove the line
			unset($chunkLines[$key]);

			// update the quantity
			$qty += $x;
		}

		if($qty) {
			// append quantity to line, i.e. “^+2” indicating 2 more indentical lines were above
			$chunk = implode("\n", array_values($chunkLines));
			$line .= " ^+$qty";
		}
	}

	/**
	 * Save the given log entry string
	 * Taken from /wire/core/FileLog.php
	 *
	 * @author Ryan Cramer, modified by Matjaž Potočnik
	 * @param string $str
	 * @param array $options options to modify behavior (Added 3.0.143)
	 *  - `allowDups` (bool): Allow duplicating same log entry in same runtime/request? (default=true)
	 *  - `mergeDups` (int): Merge previous duplicate entries that also appear near end of file?
	 *     To enable, specify int for quantity of bytes to consider from EOF, value of 1024 or higher (default=0, disabled)
	 *  - `maxTries` (int): If log entry fails to save, maximum times to re-try (default=20)
	 *  - `maxTriesDelay` (int): Micro seconds (millionths of a second) to delay between re-tries (default=2000)
	 * @return bool Success state: true if log written, false if not.
	 *
	 */
	private static function log($str, array $options = array()) {

		$logFile = self::$logFile;
		//@file_put_contents($logFile, date("Y-m-d H:i:s") . "\t" . $str . "\r\n", FILE_APPEND);
		//return;

		$defaults = array(
			'mergeDups' => 0,
			'allowDups' => true,
			'maxTries' => 2, //MP 20
			'maxTriesDelay' => 100, //MP 2000
		);
		$delimeter = "\t";

		if(!$logFile) return;

		$options = array_merge($defaults, $options);
		//$hash = md5($str);
		$ts = date("Y-m-d H:i:s");
		//MP $str = $this->cleanStr($str);
		$line = $delimeter . $str; // log entry, excluding timestamp
		$hasLock = false; // becomes true when lock obtained
		$fp = false; // becomes resource when file is open

		// if we've already logged this during this instance, then don't do it again
		//MP if(!$options['allowDups'] && isset($this->itemsLogged[$hash])) return true;

		// determine write mode
		$mode = file_exists($logFile) ? 'a' : 'w';
		if($mode === 'a' && $options['mergeDups']) $mode = 'r+';

		// open the log file
		for($tries = 0; $tries <= $options['maxTries']; $tries++) {
			$fp = fopen($logFile, $mode);
			if($fp) break;
			// if unable to open for reading/writing, see if we can open for append instead
			if($mode === 'r+' && $tries > ($options['maxTries'] / 2)) $mode = 'a';
			usleep($options['maxTriesDelay']);
		}

		// if unable to open, exit now
		if(!$fp) return false;

		// obtain a lock
		for($tries = 0; $tries <= $options['maxTries']; $tries++) {
			$hasLock = flock($fp, LOCK_EX);
			if($hasLock) break;
			usleep($options['maxTriesDelay']);
		}

		// if unable to obtain a lock, we cannot write to the log
		if(!$hasLock) {
			fclose($fp);
			return false;
		}

		// if opened for reading and writing, merge duplicates of $line
		if($mode === 'r+' && $options['mergeDups']) {
			// do not repeat the same log entry in the same chunk
			$chunkSize = (int) $options['mergeDups'];
			if($chunkSize < 1024) $chunkSize = 1024;
			fseek($fp, -1 * $chunkSize, SEEK_END);
			$chunk = fread($fp, $chunkSize);
			// check if our log line already appears in the immediate earlier chunk
			if(strpos($chunk, $line) !== false) {
				// this log entry already appears 1+ times within the last chunk of the file
				// remove the duplicates and replace the chunk
				$chunkLength = strlen($chunk);
				self::removeLineFromChunk($line, $chunk, $chunkSize);
				fseek($fp, 0, SEEK_END);
				$oldLength = ftell($fp);
				$newLength = $chunkLength > $oldLength ? $oldLength - $chunkLength : 0;
				ftruncate($fp, $newLength);
				fseek($fp, 0, SEEK_END);
				fwrite($fp, $chunk);
			}
		} else {
			// already at EOF because we are appending or creating
		}

		// add the log line
		$result = fwrite($fp, "$ts$line\n");

		// release the lock and close the file
		flock($fp, LOCK_UN);
		fclose($fp);

		//MP if($result && !$options['allowDups']) $this->itemsLogged[$hash] = true;

		// if we were creating the file, make sure it has the right permission
		if($mode === 'w') {
			@chmod($logFile, octdec('0644')); //MP
			//$files = $this->wire('files'); /** @var WireFileTools $files */
			//$files->chmod($logFile);
		}

		return (int) $result > 0;
	}

	/**
	 * Add attribute to the body tag and comment at the end of html
	 *
	 * @author Matjaž Potočnik
	 * @param string &$html
	 *
	 */
	private static function rewrite(&$html) {

		// add comment at the end of the document
		$changed = false;
		$c = "<!--AIOM-->";
		if(stripos($html, $c) === false) {
			if(stripos($html, '</html>')) {
				$html = str_ireplace("</html>", "</html>$c", $html);
			} else {
				$html .= $c;
			}
			$changed = true;
		}

		// add attribute to the body tag
		$c = "data-cache='AIOM'";
		if(stripos($html, $c) === false) {
			if(stripos($html, '<body>')) {
				$html = str_ireplace("<body>", "<body $c>", $html);
			} else if (stripos($html, '<body ')) {
				$html = str_ireplace("<body ", "<body $c ", $html);
			}
			$changed = true;
		}
		//self::log("cache INFO: html taged as cache=$changed");
		return $changed;
	}

	/**
	 * Remove cached aiom file
	 *
	 * @author Matjaž Potočnik
	 * @param string $file aiom cache file to remove
	 * @param bool $delFolder indicator to remove parent folder
	 *
	 */
	private static function removeCacheFile($file, $delFolder = false) {
		$ret = @unlink($file);
		if($ret) {
			//self::log("cache INFO: cache file expired, $file deleted");
		} else {
			self::log("cache ERROR: cache file expired, $file delete failed");
		}

		if(!$delFolder) return;

		//should I delete real cache file too?
		//@unlink($pageCacheFile);

		//delete parent folder if empty and different than root folder
		//On windows, when a parent folder is locked in file explorer,
		//deleteing a folder here/now my cause failure to create a folder
		//later on in the script execution in the module. The folder
		//will eventually be created in the next page request.
		$dir = dirname($file);
		if($dir . '/' !== self::$aiomCachePath) {
			$iterator = new \FilesystemIterator($dir);
			if(!$iterator->valid()) {
				//directory is empty
				$ret = @rmdir($dir);
				if($ret) {
					//self::log("cache MISS: /$it cache file expired, $dir deleted");
				} else {
					self::log("cache ERROR: /$it cache file expired, $dir delete failed");
				}
			}
		}
	}

}

AIOMCache::cache();
