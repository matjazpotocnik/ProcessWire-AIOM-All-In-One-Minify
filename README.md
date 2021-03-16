# AIOM+ (All In One Minify) #

#### Simple caching solution with minifying and parsing for CSS, LESS, JS and HTML ####
-----------------------------

AIOM+ (All In One Minify) is a ProcessWire module to improve the performance of your website.
By a simple function call Style sheets, LESS and JavaScript files can be parsed, minimized and
combined into single file. This reduces the server requests, loading time and reduces the
traffic. Besides, the generated HTML source code, Style sheets and JavaScript files can be minimized
automatically (without any programming). Even more: AIOM+ can enhance ProcessWire's builtin template
caching and **noticeable** speed up your site.

**NOTE**: This forked version includes several pull requests, fixes and modifications from the
unmaintained [original AIOM+](https://github.com/FlipZoomMedia/ProcessWire-AIOM-All-In-One-Minify). See the
changelog at the end of this document or CHANGELOG.md or sources for more info. Also, from version 4.0.0
AIOM+ template caching is added by me.

**NOTE:** Minimizing process, especially in automatic mode, can actually increase the page rendering time!
I recommend that you minimize your assets in advance, then set up template caching and enable
AIOM+ caching. Or use a commercial product [ProCache](https://processwire.com/store/pro-cache/).

**NOTE:** If you implement manual minimizing of CSS/LESS/JS files (by calling API function in your template 
file) and you uninstall this module, your site will stop working! I don't use LESS or Domain sharding, so
I never tested it.

- - -

#### Information ####

* All paths are relative to the template folder. URLs in css files will be automatically corrected. Nothing needs to be changed. 
* If you make changes to the source stylesheet, LESS or javascript files, a new parsed and combined version is created automatically. 
* If you make changes to the source stylesheet, LESS or javascript files and AIOM+ template caching is enabled, the template cache is cleared.
* During development, you can enable developer mode. Files are parsed and combined but not minimized and browser caching is prevented. 
* You can use the short syntax `\AIOM` or use the full class name `\AllInOneMinify` in your templates. 
* The generated files can be delivered via a subdomain (Domain sharding / Cookieless domain).
* **NOTE**: There are a few unsupported LESS features: 
    * Evaluation of JavaScript expressions within back-ticks
    * Definition of custom functions

## Table of content ##

* [Installation](#installation)
* [Caching](#caching)
* [Minimizing](#minimizing)
* [Minimize Stylesheets and parse LESS files](#minimize-stylesheets-and-parse-less-files)
* [LESS variables access in multiple files](#less-variables-access-in-multiple-files)
* [Minimize Javascripts](#minimize-javascripts)
* [Conditional loading](#conditional-loading)
* [Directory Traversal Filter](#directory-traversal-filter)
* [Exclude minimized files](#already-minimized-files-no-longer-minimized)
* [Minimize HTML automaticaly](#minimize-html)
* [Minimize CSS automatically](#minimize-css)
* [Minimize JS automatically](#minimize-js)
* [Development mode](#development-mode)
* [Changelog](#changelog)
* [Others](#questions-or-comments)

<a name='instalation'></a>
## Installation ## 

1. Copy the files for this module to /site/modules/AllInOneMinify/
2. In admin: Modules > Refresh.
3. Install Module "AIOM+ (All In One Minify) for CSS, LESS, JS and HTML".

<a name='caching'></a>
## Caching ##

In ProcessWire, a template can be configured to cache its output on the front-end so that it only
executes its PHP template-file at certain intervals (cache time) and delivers cached content the
rest of the time. Templates caching can reduce page render time on resource-heavy pages by
serving pages from disk cache rather than creating pages on every request. But caching is not that
efficient on simple, resource-light pages. AIOM+ uses cached pages but delivers them more
efficiently. For this to work, you have to enable template caching AND also edit
`/index.php` file on your webroot to include file `AIOMcache.php`. I know that modifying core
ProcessWire files is not a good idea, but I don't see other options. index.php file is very rarely
changed during ProcessWire upgrade, so I think it's not a big deal. Edit `index.php` located on your
webroot folder with a text editor and add this line **before** any ProcessWire statements:

~~~html
@include('site/modules/AllInOneMinify/AIOMcache.php')
~~~

As an alternative, if you don't want or can't change index.php, the next option is to edit 
`/site/config.php` and add this at the end of the config:

~~~html
@include($config->paths->siteModules . 'AllInOneMinify/AIOMcache.php');
~~~

This option is not as fast as inclusion in index.php since ProcessWire is already halfway done booting,
but it's better than nothing.

Next, edit the template you want to enable caching, select the _Cache_ tab and enter:

_Cache Status:_ Enabled, _Cache Time:_ enter the number of seconds, e.g. 3600 for an hour,
_Page Save/Cache Expiration:_ Clear cache for the saved page only,
_Cache when rendering pages for these users:_ Guests only

Finally, enable AIOM+ template caching in this module settings under the Caching tab.

When you use template caching (with or without AIOM+) and you modify the primary template file (for
example, basic-page.php), prepend file (_init.php) or append file, the modifications made
to those files are not reflected on the cached page. AIOM+ can be instructed to monitor those files, and
if they are changed, it clears the cache for the page. You can specify files to monitor by entering
the file names in the Aditional cache clear option field. Enter each file name on its own line.
Files are relative to `/site/templates/` folder. Specify template files (alternate/prepend/append)
as `{template_files}` and `{config_template_files}`. This monitoring of files works even if you don't
enable AIOM+ caching.

**NOTE:** AIOM+ template caching works only for guest users (even if you setup template caching for guests
and logged-in users), that is, it won't fire up if _wire_challenge_ or _wires_challenge_ cookie is present in
the page request. It also won't work for POST requests or GET requests with parameters. When a page is served 
from the AIOM+ cache `data-cache='AIOM'` will be added to the `<body>` or `<html>` tag. 

<a name='minimizing'></a>
## Minimizing ##

You can minimize generated HTML source, CSS/LESS and JS files. CSS and JS files can be minimized automatically 
or manually. In automatic mode, AIOM+ parses the generated HTML and replaces the reference to CSS/JS files with
the minified version. In manual mode, you call the API function in your template file to minimize files. 
**Don't mix both methods!**

<a name='minimize-stylesheets-and-parse-less-files'></a>
## Minimize Stylesheets and parse LESS files manually ##

Minimization of a single file.

~~~html
<!-- CSS Stylesheet -->
<link rel="stylesheet" href="<?php echo \AllInOneMinify::CSS('css/stylesheet.css'); ?>">

<!-- LESS file -->
<link rel="stylesheet" href="<?php echo \AllInOneMinify::CSS('css/stylesheet.less'); ?>">
~~~

Minimize multiple files into one file. You can even mix stylesheet and LESS files in the parsing/combining process!

~~~html
<link rel="stylesheet" href="<?php echo \AllInOneMinify::CSS(array('css/file-1.css', 'css/file-2.less', 'css/file-3.css', 'css/file-4.less')); ?>">
~~~

**Tip:** You can also use the short syntax `"AIOM"`. For example, `\AIOM::CSS()`.

<a name='less-variables-access-in-multiple-files'></a>
## LESS variables access in multiple files ##

Do you have a LESS file in which you define colors and another LESS file that defines the actual layout?
Now you need in the layout LESS file access to the variables of the color LESS file?
It's easier than you think. Through a simple referencing of source LESS file. For example: 

~~~html
<link rel="stylesheet" href="<?php echo \AllInOneMinify::CSS('css/color.less'); ?>">
...
<link rel="stylesheet" href="<?php echo \AllInOneMinify::CSS('css/layout.less'); ?>">
~~~

Example content of `color.less`

~~~css
@my-color: #ff0000;
~~~

Example content of `layout.less`

~~~css
@import (reference) "css/color.less";

body {
    background-color: @my-color;
}
~~~

That's all. Pretty, hu? The complete documentation of LESS you can find at www.lesscss.org

<a name='minimize-javascripts'></a>
## Minimize Javascripts manually##

Minimization of a single file.

~~~html
<script src="<?php echo \AllInOneMinify::JS('js/javascript.js'); ?>"></script>
~~~

Minimize multiple files into one file.

~~~html
<script src="<?php echo \AllInOneMinify::JS(array('js/file-1.js', 'js/file-2.js', 'js/file-3.js', 'js/file-4.js')); ?>"></script>
~~~

**Tip:** You can also use the short syntax **"\AIOM"**. For example, `\AIOM::JS()`.

<a name='conditional-loading'></a>
## Conditional loading ##

Since AIOM+ version 3.1.1 javascripts, stylesheets and LESS files can be loaded based on a API selector. 
Here is an example of conditional loading: 

~~~html
<?php $stylesheets  = array('css/reset.css',
                            'css/main.less',
					        array('loadOn'  => 'id|template=1002|1004|sitemap', 
						          'files'   => array('css/special.css', 'css/special-theme.less'))); ?>
						          
<link rel="stylesheet" href="<?php echo \AIOM::CSS($stylesheets); ?>" />
~~~

The same you can do with `\AIOM::JS()`. `loadOn` must be an [ProcessWire API selector](https://processwire.com/docs/selectors/).

<a name='directory-traversal-filter'></a>
## Directory Traversal Filter ##

By default, only files can be included, which are in the ProcessWire template folder. If you wish to
add files outside that folder, you have to activate the backend "Allow Directory Traversal" option. 
Then you can jump back in the path. For example: 

~~~html
\AIOM::CSS('../third-party-packages/package/css/example.css');
~~~
**All paths are still automatically corrected!**

<a name='already-minimized-files-no-longer-minimized'></a>
## Already minimized files no longer minimized ##

To further enhance the performance and to give you the maximum flexibility in the combining process,
you now have the option to exclude certain files from the minimization (since version 2.2). All files
with the abbreviation ".min" or "-min" at the end of the file name and before the file extension 
are no longer minimized. For example, `file-1.js` is minimized. `file-1-min.js` or `file-1.min.js` is
not minimized — the same for CSS. `file-1.css` is minimized. `file-1-min.css` or `file-1.min.css` is not 
minimized.

<a name='minimize-html'></a>
## Minimize HTML automatically ##

The generated HTML source code is automatically minimized when rendering — just enable the option under 
the Minimize tab. No change to the template file is needed. Conditional Comments, textareas, code tags, 
etc., are excluded from the minimization. 

<a name='minimize-css'></a>
## Minimize CSS automatically ##

The HTML source code is searched for **internal** links to Stylesheet (.css) files and they are automatically 
replaced with minimized version. Simply enable the option under the Minimize tab. No change to the source
(template file) is needed. The following line:

~~~html
<link rel="stylesheet" href="<?php echo $config->site->templates . 'file.css'; ?>">
~~~

is replaced with:

~~~html
<link rel="stylesheet" href="/site/assets/aiom/css_81c54f1249c3deab897bb50ba39eaf5.css">
~~~

<a name='minimize-js'></a>
## Minimize JS automatically ##

The HTML source code is searched for **internal** JavaScript files (.js) and they are automatically replaced 
with minimized version. No change to the source (template file) is needed. The following line:

~~~html
<script src="<?php echo $config->site->templates . 'file.js'; ?>">
~~~

is replaced with:

~~~html
<script src="/site/assets/aiom/js_81c54f1249c3deab897bb50ba39eaf5.js">
~~~

<a name='development-mode'></a>
## Development mode ##

If you are currently in the development of the site, caching can be a problem. For this, you can enable
the development mode. The files will be combined (when using CSS() or JS() in your template files)
but not minimized and re-generated at each call. Also, a no-cache GET parameter is appended
with a timestamp to prevent browser caching. For example:
`css_031ea978b0e6486c828ba444c6297ca5_dev.css?no-cache=1335939007`

<a name='changelog'></a>
## Changelog ##

See CHANGELOG.md

<a name='questions-or-comments'></a>
## Questions or comments? ##

For any questions, suggestions or bugs, please create a ticket on [GitHub](https://github.com/matjazpotocnik/ProcessWire-AIOM-All-In-One-Minify). 
