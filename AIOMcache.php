<?php

// A file should declare new symbols (classes, functions, constants, etc.) and cause no other side effects.
// This warning is issued as AIOMCache::cache(); is called at the and
// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

// Each class must be in a namespace of at least one level
// No namespace is present here, but that's ok
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

// Only comments are longer
// phpcs:disable Generic.Files.LineLength.TooLong

// Allow statments like this: if(...) continue;
// phpcs:disable Generic.ControlStructures.InlineControlStructure.NotAllowed

class AIOMcache
{
    /** @var string */
    private static $rootPath;

    /** @var string */
    private static $aiomCachePath;

    /** @var string */
    private static $logFile;

    /**
     * Serve the requested page if cached and not expired
     *
     * @author Matjaž Potočnik
     * @return bool false if content is not served from cache
     *
     */
    public static function cache()
    {
        self::$rootPath = self::getRootPath();
        self::$aiomCachePath = self::$rootPath . '/site/assets/cache/aiom/';
        self::$logFile = self::$rootPath . '/site/assets/logs/aiom.txt';

        $query_string = self::toString($_SERVER['QUERY_STRING'] ?? '');

        //return if not a guest, request method is not GET, query string is not a simple
        //cache URL (?it=... with no '&' and no 'it=processwire'), or caching not enabled
        if (
            isset($_COOKIE['wire_challenge']) || isset($_COOKIE['wires_challenge']) ||
            ($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET' ||
            ($query_string !== '' &&
                (strpos($query_string, 'it=') !== 0 || strpos($query_string, '&') !== false || strpos($query_string, 'it=processwire') !== false)
            ) ||
            !is_file(self::$aiomCachePath . 'aiom.enabled')
        ) {
            //self::log('cache INFO: condition not met ' . $_SERVER['QUERY_STRING']);
            return false;
        }

        $it = self::toString($_GET['it'] ?? '');
        if (strpos($it, '..') !== false) return false;
        $it = trim($it, '/');
        if ($it !== '') $it .= '/';

        $aiomCacheFile = self::$aiomCachePath . $it . 'cache.json';

        //some general logging, uncomment only for debugging
        //self::log("cache INFO: it: $it, queryString: " . $query_string);

        //check if AIOM cache file exist
        //this also serve as a way to "sanitize" $it
        if (!is_file($aiomCacheFile) || !is_readable($aiomCacheFile)) {
            //self::log("cache MISS: /$it no cache file $aiomCacheFile");
            return false;
        }

        //AIOM cache file exists, open it and get "real" Page cache file, cache time and template files
        $aiomCacheFileContent = file_get_contents($aiomCacheFile);
        if ($aiomCacheFileContent === false) {
            self::log("cache ERROR: unreadable $aiomCacheFile");
            return false;
        }
        $aiomCacheFileArr = json_decode($aiomCacheFileContent, true);
        if (!is_array($aiomCacheFileArr) || !isset($aiomCacheFileArr[0], $aiomCacheFileArr[2])) {
            self::log("cache ERROR: invalid format of $aiomCacheFile");
            self::removeCacheFile($aiomCacheFile);
            return false;
        }

        /** @var list<mixed> $aiomCacheFileArr */
        $pageCacheFile = self::toString($aiomCacheFileArr[0]);       //eg. /site/assets/cache/Page/1/page2_1234.cache
        //$pageCacheTime = $aiomCacheFileArr[1];       //eg. 3600 - not used
        $pageCacheExpireTime = is_numeric($aiomCacheFileArr[2]) ? (int) $aiomCacheFileArr[2] : 0; //eg. 1583141543
        //$pageCacheExpireDate = $aiomCacheFileArr[3]; //eg. 2020-02-20 21:57:03 - not used
        //$tplFiles = count($aiomCacheFileArr) > 4 ? $aiomCacheFileArr[4] : []; //eg. /site/templates/basic-page.php
        $tplFiles = isset($aiomCacheFileArr[4]) && is_array($aiomCacheFileArr[4]) ? $aiomCacheFileArr[4] : []; //eg. /site/templates/basic-page.php

        //self::log("cache INFO: cacheTime: $pageCacheTime, cacheExpireTime: $pageCacheExpireTime, cacheExpireDate: $pageCacheExpireDate");

        //check if page cache file expired
        if ($pageCacheExpireTime <= time()) {
            //self::log("cache INFO: $pageCacheFile expired");
            self::removeCacheFile($aiomCacheFile);
            return false;
        }

        //check if one of the template files is newer than page cachefile
        $pageCacheExpireFilemtime = @filemtime($pageCacheFile);
        foreach ($tplFiles as $tplFile) {
            $tplFile = self::toString($tplFile);
            //self::log(sprintf('cache INFO: checking %s, %s', $tplFile, date('Y-m-d H:i:s', @filemtime($tplFile))));
            if (is_file($tplFile) && filemtime($tplFile) > $pageCacheExpireFilemtime) {
                //template file is newer than cachefile, invalidate (remove) cache files for the page
                //self::log(sprintf('cache INFO: %s is newer than %s', $tplFile, $pageCacheFile));
                self::removeCacheFile($aiomCacheFile);
                return false;
            }
        }

        //now return content from the page cache file
        $out = @file_get_contents($pageCacheFile);

        if ($out === false || $out === '') {
            //some error occured or page cache file is empty
            self::log(sprintf('cache ERROR: /%s %s empty or nonexistent', $it, $pageCacheFile));
            self::removeCacheFile($aiomCacheFile);
            return false;
        }

        //we have a content, serve it
        //$len = @mb_strlen($out, 'UTF-8');
        self::rewrite($out);
        //self::log(sprintf('cache HIT: /%s serving %s (%d bytes)', $it, $pageCacheFile, $len));

        header('X-AIOM-Cache: HIT');
        /*
        if ($pageCacheExpireFilemtime > 0) {
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $pageCacheExpireFilemtime) . ' GMT');
        }
        */
        echo $out;
        exit(0);
    }

    /**
     * Get root path, check it, and optionally auto-detect it if not provided
     * Taken from /wire/core/ProcessWire.php
     *
     * @author Ryan Cramer, modified by Matjaž Potočnik
     * @return string
     *
     */
    private static function getRootPath()
    {
        // current working directory
        $rootPath = realpath('');
        if (!$rootPath || !file_exists($rootPath . '/wire/core/ProcessWire.php')) {
            $rootPath = '';
        }

        if (!$rootPath && !empty($_SERVER['SCRIPT_FILENAME'])) {
            // dirname() correctly handles both Unix and Windows directory separators
            $rootPath = dirname(self::toString($_SERVER['SCRIPT_FILENAME']));
            if (!file_exists($rootPath . '/wire/core/ProcessWire.php')) $rootPath = '';
        }

        if (!$rootPath) {
            // if unable to determine from script filename, assume the web root is 4 levels up
            $rootPath = dirname(__FILE__, 4);
            if (!file_exists($rootPath . '/wire/core/ProcessWire.php')) $rootPath = '';
        }

        if (DIRECTORY_SEPARATOR !== '/') {
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
     * @return void
     * @since 3.0.143
     */
    private static function removeLineFromChunk(&$line, &$chunk, $chunkSize)
    {

        $qty = 0;
        $chunkLines = explode("\n", $chunk);

        foreach ($chunkLines as $key => $chunkLine) {
            $x = 1;
            if ($key === 0 && strlen($chunk) >= $chunkSize) continue; // skip first line since it’s likely a partial line

            // check if line appears in this chunk line
            if (strpos($chunkLine, $line) === false) continue;

            // check if line also indicates a previous quantity that we should add to our quantity
            if (strpos($chunkLine, ' ^+') !== false) {
                [$chunkLine, $n] = explode(' ^+', $chunkLine, 2);
                if (ctype_digit($n)) $x += (int) $n;
            }

            // verify that these are the same line
            if (strpos(trim($chunkLine) . "\n", trim($line) . "\n") === false) continue;

            // remove the line
            unset($chunkLines[$key]);

            // update the quantity
            $qty += $x;
        }

        if ($qty !== 0) {
            // append quantity to line, i.e. “^+2” indicating 2 more indentical lines were above
            $chunk = implode("\n", array_values($chunkLines));
            $line .= ' ^+' . $qty;
        }
    }

	/**
	 * Obtain stable lock for this log file pathname
	 *
	 * @param int $maxTries
	 * @param int $maxTriesDelay
	 * @return resource|false
	 *
	 */
	private static function lockLogFile($maxTries, $maxTriesDelay)
    {
		if(!self::$logFile) return false;
		$lockFile = self::$logFile . '.lock';
		$isNew = !file_exists($lockFile);
		$fp = fopen($lockFile, 'c');
		if(!$fp) return false;
		for($tries = 0; $tries <= $maxTries; $tries++) {
			if(flock($fp, LOCK_EX)) {
				if($isNew) @chmod($lockFile, 0644);
				return $fp;
			}
			usleep($maxTriesDelay);
		}
		fclose($fp);
		return false;
	}

    /**
     * Save the given log entry string
     * Taken from /wire/core/FileLog.php
     *
     * @author Ryan Cramer, modified by Matjaž Potočnik
     * @param string $str
     * @param array<string, int|bool> $options options to modify behavior (Added 3.0.143)
     *  - `allowDups` (bool): Allow duplicating same log entry in same runtime/request? (default=true)
     *  - `mergeDups` (int): Merge previous duplicate entries that also appear near end of file?
     *     To enable, specify int for quantity of bytes to consider from EOF, value of 1024 or higher (default=0, disabled)
     *  - `maxTries` (int): If log entry fails to save, maximum times to re-try (default=20)
     *  - `maxTriesDelay` (int): Micro seconds (millionths of a second) to delay between re-tries (default=2000)
     * @return bool Success state: true if log written, false if not.
     *
     */
    private static function log($str, $options = [])
    {

        $logFile = self::$logFile;
        //@file_put_contents($logFile, date("Y-m-d H:i:s") . "\t" . $str . "\r\n", FILE_APPEND);
        //return;

        $defaults = [
            'mergeDups' => 0,
            'allowDups' => true,
            'maxTries' => 2, //MP 20
            'maxTriesDelay' => 100, //MP 2000
        ];
        $delimeter = "\t";

        if (!$logFile) return false;

        $options = array_merge($defaults, $options);
        $ts = date("Y-m-d H:i:s");
        $line = $delimeter . $str; // log entry, excluding timestamp
        $hasLock = false; // becomes true when lock obtained
        $fp = false; // becomes resource when file is open
        $lockFp = false; // stable lock for log pathname
        $maxTriesDelay = (int) $options['maxTriesDelay'];
        $maxTries = (int) $options['maxTries'];

        // if we've already logged this during this instance, then don't do it again
        //MP if(!$options['allowDups'] && isset($this->itemsLogged[$hash])) return true;

        $lockFp = self::lockLogFile($maxTries, $maxTriesDelay);
		if(!$lockFp) {
			return false;
		}

        // determine write mode
        $mode = file_exists($logFile) ? 'a' : 'w';
        if ($mode === 'a' && $options['mergeDups']) {
            $mode = 'r+';
        }

        // open the log file
        for ($tries = 0; $tries <= $maxTries; $tries++) {
            $fp = fopen($logFile, $mode);
            if ($fp) {
                break;
            }

            // if unable to open for reading/writing, see if we can open for append instead
            if ($mode === 'r+' && $tries > ($maxTries / 2)) {
                $mode = 'a';
            }

            usleep($maxTriesDelay);
        }

        // if unable to open, exit now
        if (!$fp) {
            flock($lockFp, LOCK_UN);
		    fclose($lockFp);
            return false;
        }

        // obtain a lock
        for ($tries = 0; $tries <= $maxTries; $tries++) {
            $hasLock = flock($fp, LOCK_EX);
            if ($hasLock) {
                break;
            }

            usleep($maxTriesDelay);
        }

        // if unable to obtain a lock, we cannot write to the log
        if (!$hasLock) {
            fclose($fp);
            flock($lockFp, LOCK_UN);
		    fclose($lockFp);
            return false;
        }

        // if opened for reading and writing, merge duplicates of $line
        if ($mode === 'r+' && $options['mergeDups']) {
            // do not repeat the same log entry in the same chunk
            $chunkSize = (int) $options['mergeDups'];
            if ($chunkSize < 1024) $chunkSize = 1024;
            fseek($fp, -1 * $chunkSize, SEEK_END);
            $chunk = fread($fp, $chunkSize);
            $chunk = $chunk === false ? '' : $chunk;
            // check if our log line already appears in the immediate earlier chunk
            if (strpos($chunk, $line) !== false) {
                // this log entry already appears 1+ times within the last chunk of the file
                // remove the duplicates and replace the chunk
                $chunkLength = strlen($chunk);
                self::removeLineFromChunk($line, $chunk, $chunkSize);
                fseek($fp, 0, SEEK_END);
                $oldLength = ftell($fp);
                $newLength = max(0, $oldLength - $chunkLength);
                ftruncate($fp, $newLength);
                fseek($fp, 0, SEEK_END);
                fwrite($fp, (string) $chunk);
            }
        } else {
            // already at EOF because we are appending or creating
        }

        // add the log line
        $result = fwrite($fp, $ts . $line . PHP_EOL);

        // release the lock and close the file
        flock($fp, LOCK_UN);
        fclose($fp);

        //MP if($result && !$options['allowDups']) $this->itemsLogged[$hash] = true;

        // if we were creating the file, make sure it has the right permission
        if ($mode === 'w') {
            @chmod($logFile, 0644); //MP
            //$files = $this->wire('files'); /** @var WireFileTools $files */
            //$files->chmod($logFile);
        }

        flock($lockFp, LOCK_UN);
		fclose($lockFp);

        return (int) $result > 0;
    }

    /**
     * Add an attribute to the body tag and comment at the end of the HTML
     *
     * @author Matjaž Potočnik
     * @param string $html HTML content (modified by reference)
     * @return bool True if HTML was modified, false otherwise.
     *
     */
    private static function rewrite(&$html)
    {

        // add comment at the end of the document
        $changed = false;
        $c = "<!--AIOM-->";
        if (stripos($html, $c) === false) {
            if (stripos($html, '</html>') !== false) {
                $html = str_ireplace("</html>", "</html>$c", $html);
            } else {
                $html .= $c;
            }

            $changed = true;
        }

        // add an attribute to the body tag
        $c = "data-cache='AIOM'";
        if (stripos($html, $c) === false) {
            if (stripos($html, '<body>') !== false) {
                $html = str_ireplace("<body>", "<body $c>", $html);
                $changed = true;
            } elseif (stripos($html, '<body ') !== false) {
                $html = str_ireplace("<body ", "<body $c ", $html);
                $changed = true;
            }
        }

        //self::log(sprintf('cache INFO: html taged as cache=%s', $changed));
        return $changed;
    }

    /**
     * Returns the given value as a string, or an empty string if it is not a string.
     *
     * This utility ensures safe string usage in contexts where PHPStan would
     * otherwise complain about mixed types (e.g., when handling superglobals,
     * json decoded values, or filesystem reads).
     *
     * @param mixed $var The value to convert to string if possible.
     * @return string The original string or an empty string if not a string.
     */
    private static function toString($var) {
        return is_string($var) ? $var : '';
    }

    /**
     * Remove cached aiom file
     *
     * @author Matjaž Potočnik
     * @param string $file aiom cache file to remove
     * @return void
     *
     */
    private static function removeCacheFile($file)
    {
        $ret = @unlink($file);
        if ($ret) {
            //self::log(sprintf('cache INFO: cache file expired, %s deleted', $file));
        } else {
            self::log(sprintf('cache ERROR: cache file expired, but %s delete failed', $file));
        }

        //should I delete real cache file too?
        //@unlink($pageCacheFile)
        //No - page cache file is owned by core PageRender and self-invalidates
        //(expiry + template mtime); AIOM's template_files case is handled by
        //the module's checkTemplateFiles() hook at render time.;
    }
}

AIOMcache::cache();
