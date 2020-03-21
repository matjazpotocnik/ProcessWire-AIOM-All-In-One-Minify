## Changelog ##

4.0.1
- Bug fixes (Matjaž Potočnik)

4.0.0

- Added AIOM+ template caching (Matjaž Potočnik)
- Added tabbed interface (Matjaž Potočnik)
- Updated readme, changelog, usage (Matjaž Potočnik)

3.2.9

- Fixed less.php compatibility in PHP 7.4 (Matjaž Potočnik)
- Updated html-min, simple_html_dom and css-selector (Matjaž Potočnik)

3.2.8

- Fixed minify process when css/js files in html source has query strings (Matjaž Potočnik)

3.2.6

- Added options to automatically minimize JS and CSS files (Matjaž Potočnik)
- Using the original HTML minimize algorithm instead of voku - it's faster (Matjaž Potočnik)

3.2.5

* Using https://github.com/voku/HtmlMin to minimize html (Matjaž Potočnik)

3.2.4

* A lot of fixes, see CHANGELOG.md (Matjaž Potočnik) 

3.2.3

* Typo fix in a variable (Thanks to SteveB)

3.2.2

* Security Fix: CHMOD ([#44](https://github.com/conclurer/ProcessWire-AIOM-All-In-One-Minify/issues/44))
* Bugfix: MD5 hash with many files ([#46](https://github.com/conclurer/ProcessWire-AIOM-All-In-One-Minify/issues/46))
* Improvement in the detection of file changes

3.2.1

* Bugfix: $config->scripts was not included properly
* Support for @-webkit-keyframes added

3.2

* New CSS Compressor: AIOM now uses YUI Compressor (thanks to hwmaier)
* You can now use $config->scripts and $config->styles in AIOM ([#31](https://github.com/conclurer/ProcessWire-AIOM-All-In-One-Minify/issues/31))
* Bugfix: Empty {} brackets will only be partly removed ([#23](https://github.com/conclurer/ProcessWire-AIOM-All-In-One-Minify/issues/23))
* Bugfix: CSS pseudo classes will be compressed incorrectly ([#33](https://github.com/conclurer/ProcessWire-AIOM-All-In-One-Minify/issues/33))

3.1.5

* Bugfix: Links to images, which are embedded in CSS, are broken if the DOCUMENT_ROOT is not equal to ProcessWire root. 

3.1.4

* Bugfix: CacheFiles for Pages are now deleted when a new minimized file is created
* Bugfix: An error is thrown if the document root is different to ProcessWire's base path
* Note: AIOM is now also developed by [Conclurer](https://www.conclurer.com).

3.1.3

* New LESS version: Update parser to version 1.7.1
  * improved parser exceptions with invalid less
  * prevent fround() from changing integer into double
  * prevent fatal error with preg_match()
  * fix undefined variable
* New CSSMin version: Update script to version 1.1.2
  * Some improvements
  * Bugfix: Broken rule for Firefox 27.0.1 (Animation second "s" lost)
* Push to stable

3.1.2 

* New feature: Enable or disable directory traversal filter in the backend ([#12](https://github.com/conclurer/ProcessWire-AIOM-All-In-One-Minify/issues/12))
* New LESS version: Update parser to Version 1.7

3.1.1

* New feature: Conditional loading
* Update readme / documentation

3.0.1

* BugFix ([#11](https://github.com/conclurer/ProcessWire-AIOM-All-In-One-Minify/issues/11)): Wrong class order in Less.php parser (Thanks to Ryan Pierce)

3.0.0 

* AIOM+ tested with ProcessWire 2.4
* Module now multilingual
* New feature: LESS support (direct parsing and minimization server-side on the fly)
* Update readme / documentation

2.2.2 

* BugFix ([#8](https://github.com/conclurer/ProcessWire-AIOM-All-In-One-Minify/issues/8)): Many errors if debug mode is activated (Thanks to JoZ3)
* Better error handling
* Additional information about spaces in the HTML minimization. See ([#6](https://github.com/conclurer/ProcessWire-AIOM-All-In-One-Minify/issues/6)) (Thanks to philippreiner)

2.2.1

* Code Corrections / class name

2.2.0

* New feature: File is not minimized when ".min" or "-min" is at the end of the filename. For example ~~~file-1.min.js~~~.
* Update CSSMin library to Version 1.1 (inspired by Yahoo! YUI compressor)
* Update JSMin library to Version 2.7.1 (Security fix, recommended update)
* Performance improvements on first minification

2.1.1

* Old code removed
* Cleanup

2.1.0

* New CSS minimization library
* Performance improvements

2.0.0

* Configurable backend (Prefix, lifetime, dev-mode and tips)
* Empty cache on the backend
* Domain sharding / cookieless domain
* Domain sharding for SSL
* Performance improvements
* Quick introduction in the backend
* Performance tips in the backend
* .htaccess instructions for domain sharding in the backend

1.1.1  

* CSS filter update

1.1.0  

* BugFix ([#1](https://github.com/conclurer/ProcessWire-AIOM-All-In-One-Minify/issues/1)): Error: Exception: RecursiveDirectoryIterator ... Permission denied (Thanks to JoZ3 and Ryan)
* New Short-Syntax AIOM::CSS(); and AIOM::JS();
* New default settings for css minify (speed up minimizing)
* New option: enable/disable HTML minify
* New option: enable/disable development mode (combine but no minimizing)
* Some optimizations

1.0.0  
* Initial release

This forked version includes these fixes:

https://github.com/FlipZoomMedia/ProcessWire-AIOM-All-In-One-Minify/issues/48
https://github.com/Philzen/ProcessWire-AIOM-All-In-One-Minify/commit/7c0c6f333459faf4c9156546fcc635ce26c73f8f

https://github.com/FlipZoomMedia/ProcessWire-AIOM-All-In-One-Minify/pull/49
https://github.com/FlipZoomMedia/ProcessWire-AIOM-All-In-One-Minify/pull/49/commits/a7d7dbc0f1d484eb16b00c6d1378d24c086d8a09

https://github.com/FlipZoomMedia/ProcessWire-AIOM-All-In-One-Minify/pull/50
https://github.com/FlipZoomMedia/ProcessWire-AIOM-All-In-One-Minify/pull/50/commits/7c0c6f333459faf4c9156546fcc635ce26c73f8f

https://github.com/FlipZoomMedia/ProcessWire-AIOM-All-In-One-Minify/pull/53
https://github.com/FlipZoomMedia/ProcessWire-AIOM-All-In-One-Minify/pull/53/commits/31657211ffc223c5a8b924bf1347adc3732c37da

https://github.com/FlipZoomMedia/ProcessWire-AIOM-All-In-One-Minify/issues/54

https://github.com/FlipZoomMedia/ProcessWire-AIOM-All-In-One-Minify/pull/57
https://github.com/FlipZoomMedia/ProcessWire-AIOM-All-In-One-Minify/pull/57/commits/891484b48b38c9df97609250760eef63cc71bd0d

https://github.com/FlipZoomMedia/ProcessWire-AIOM-All-In-One-Minify/issues/61

https://github.com/FlipZoomMedia/ProcessWire-AIOM-All-In-One-Minify/issues/62

https://github.com/FlipZoomMedia/ProcessWire-AIOM-All-In-One-Minify/issues/64

https://github.com/FlipZoomMedia/ProcessWire-AIOM-All-In-One-Minify/issues/65

https://github.com/kixe/ProcessWire-AIOM-All-In-One-Minify/commit/6a3b5570c01df4776dafa6e2112fa80e61dfcb04
https://github.com/kixe/ProcessWire-AIOM-All-In-One-Minify/commit/b6ad1495f781ed1e9c0cc70e4d91d07b56855f8d

https://github.com/FlipZoomMedia/ProcessWire-AIOM-All-In-One-Minify/issues/67

https://gist.github.com/recca0120/5930842de4e0a43a48b8bf027ab058f9

https://github.com/gr4y/ProcessWire-AIOM-All-In-One-Minify/commit/e4fc75f2b9e92dc8ab09573c985f3f900b71cb43

https://processwire.com/talk/topic/5630-module-aiom-all-in-one-minify-for-css-less-js-and-html/?page=8&tab=comments#comment-155120
