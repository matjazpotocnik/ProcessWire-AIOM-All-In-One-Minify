# AIOM+ (All In One Minify) #

#### Simple caching solution with minification and parsing for CSS, LESS, JS and HTML ####
-----------------------------

AIOM+ (All In One Minify) is a ProcessWire module that improves the performance of your website.
Style sheets, LESS, and JavaScript files can be parsed, minified, and combined into a single file 
by a simple function call. This reduces the server requests, loading time, and bandwidth usage. 
Additionally, the generated HTML source code, Style sheets, and JavaScript files can be minified
automatically (without any programming). Even more: AIOM+ can enhance ProcessWire's built-in template
caching to **noticeably** speed up your site.

**NOTE**: This forked version includes several pull requests, fixes, and modifications from the
unmaintained [original AIOM+](https://github.com/FlipZoomMedia/ProcessWire-AIOM-All-In-One-Minify) 
created by [David Karich](https://processwire.com/modules/author/david-karich/) @ [flipzoom.de](https://www.flipzoom.de/). 
Template caching (from version 4.0.0) is developed by [me](https://processwire.com/modules/author/matjazp/).
See CHANGELOG.md or source files for more info. 

**NOTE:** The minification process, especially in automatic mode, can actually increase page rendering time!
Years ago, minification offered a fine improvement for some websites, but browsers and HTTP servers are 
now much better, and minifying assets may offer only a marginal performance benefit in narrow cases. 
Also, both JS and CSS change rapidly, and new syntaxes will likely to lead to broken code.
I recommend minifying your assets in advance, then set up template caching and enable
AIOM+ caching. Or use a commercial product [ProCache](https://processwire.com/store/pro-cache/).

**WARNING:** If you implement manual minifying of CSS/LESS/JS files (by calling API function in your template 
files) and you uninstall this module, your templates relying on AIOM+ may fail! I don't use LESS or Domain sharding, so
I never tested it.

- - -

**Information**

* All paths are relative to the template folder. URLs in css files will be automatically corrected. Nothing needs to be changed. 
* If you change the source CSS/LESS/JS files, AIOM+ automatically regenerates parsed/combined assets and, when template caching is enabled, also clears the page cache.
* During development, you can enable developer mode. Files are parsed and combined but not minified and browser caching is prevented. 
* You can use the short syntax `\AIOM` (or just `AIOM` if your template file is in ProcessWire namespace) or use the full class name `\AllInOneMinify` (`AllInOneMinify` in ProcessWire namespace) in your templates. 
* The generated files can be delivered via a subdomain (Domain sharding / Cookieless domain).
* **NOTE**: There are a few unsupported LESS features: 
    * Evaluation of JavaScript expressions within back-ticks
    * Definition of custom functions

## Table Of Contents ##

* [Installation](#installation)
* [Caching](#caching)
* [Minification](#minification)
* [Minify Stylesheets and parse LESS files](#minify-stylesheets-and-parse-less-files)
* [LESS variables access in multiple files](#less-variables-access-in-multiple-files)
* [Minify Javascripts](#minify-javascripts)
* [Conditional loading](#conditional-loading)
* [Directory Traversal Filter](#directory-traversal-filter)
* [Exclude minified files](#already-minified-files-no-longer-minified)
* [Minify HTML automatically](#minify-html)
* [Minify CSS automatically](#minify-css)
* [Minify JS automatically](#minify-js)
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

In ProcessWire, a template can be configured to cache its output on the front-
end so that it only executes its PHP template-file at specific intervals (cache
time) and delivers cached content the rest of the time. Template caching can
reduce page render time on resource-heavy pages by serving pages from the disk
cache rather than creating pages on every request. But caching is not that
efficient on simple, resource-light pages. AIOM+ uses cached pages but delivers
them more efficiently. For this to work, you must enable template caching AND
also edit /index.php file on your webroot to include the file AIOMcache.php. I
know modifying core ProcessWire files is not a good idea, but I don't see other
options. index.php file rarely changes during ProcessWire upgrade, so it's not a
big deal. Edit `/index.php` located on your webroot folder with a text editor and
add this line **before** any ProcessWire statements:

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
the file names in the Additional cache clear option field. Enter each file name on its own line.
Files are relative to `/site/templates/` folder. Specify template files (alternate/prepend/append)
as `{template_files}` and `{config_template_files}`. This monitoring of files works even if you don't
enable AIOM+ caching.

**NOTE:** AIOM+ template caching works only for guest users (even if you setup template caching for guests
and logged-in users), that is, it won't fire up if _wire_challenge_ or _wires_challenge_ cookie is present in
the page request. It also won't work for POST requests or GET requests with parameters. When a page is served 
from the AIOM+ cache `data-cache='AIOM'` will be added to the `<body>` or `<html>` tag. 

<a name='minification'></a>
## Minification ##

You can minify generated HTML source, CSS/LESS and JS files. CSS and JS files can be minified automatically 
or manually. In automatic mode, AIOM+ parses the generated HTML and replaces the reference to CSS/JS files with
the minified version. In manual mode, you call the API function in your template file to minify files. 
**Don't mix both methods** otherwise on-demand API names might override automatic pipeline!

<a name='minify-stylesheets-and-parse-less-files'></a>
## Minify Stylesheets and parse LESS files manually ##

Minification of a single file:

~~~html
<!-- CSS Stylesheet -->
<link rel="stylesheet" href="<?php echo AllInOneMinify::CSS('css/stylesheet.css'); ?>">

<!-- LESS file -->
<link rel="stylesheet" href="<?php echo AllInOneMinify::CSS('css/stylesheet.less'); ?>">
~~~

Minify multiple files into one file, even mixing CSS and LESS:

~~~html
<link rel="stylesheet" href="<?php echo AllInOneMinify::CSS(['css/file-1.css', 'css/file-2.less', 'css/file-3.css', 'css/file-4.less']); ?>">
~~~

**Tip:** You can also use the short syntax `"AIOM"`. For example, `AIOM::CSS()`.

<a name='less-variables-access-in-multiple-files'></a>
## LESS variables access in multiple files ##

Do you have a LESS file in which you define colors and another LESS file that defines the actual layout?
Now you need in the layout LESS file access to the variables of the color LESS file?
It's easier than you think. Through a simple referencing of source LESS file. For example: 

~~~html
<link rel="stylesheet" href="<?php echo AIOM::CSS('css/color.less'); ?>">
...
<link rel="stylesheet" href="<?php echo AIOM::CSS('css/layout.less'); ?>">
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

Find the complete documentation of LESS at www.lesscss.org

<a name='minify-javascripts'></a>
## Minify Javascripts manually##

Minification of a single file:

~~~html
<script src="<?php echo AIOM::JS('js/javascript.js'); ?>"></script>
~~~

Minify multiple files into one file:

~~~html
<script src="<?php echo AIOM::JS(['js/file-1.js', 'js/file-2.js', 'js/file-3.js', 'js/file-4.js']); ?>"></script>
~~~

<a name='conditional-loading'></a>
## Conditional loading ##

Since AIOM+ version 3.1.1 javascripts, stylesheets and LESS files can be loaded based on a API selector. 
Here is an example of conditional loading: 

~~~html
$stylesheets  = [
    'css/reset.css',
    'css/main.less',
    [
        'loadOn'  => 'id|template=1002|1004|sitemap', 
        'files'   => ['css/special.css', 'css/special-theme.less']
    ]
];
          
<link rel="stylesheet" href="<?php echo AIOM::CSS($stylesheets); ?>" />
~~~

The same you can do with `AIOM::JS()`. `loadOn` must be an [ProcessWire API selector](https://processwire.com/docs/selectors/).

<a name='directory-traversal-filter'></a>
## Directory Traversal Filter ##

By default, only files can be included, which are in the ProcessWire template folder. If you wish to
add files outside that folder, you have to activate the backend "Allow Directory Traversal" option. 
Then you can jump back in the path. For example: 

~~~html
AIOM::CSS('../third-party-packages/package/css/example.css');
~~~
**All paths are still automatically corrected!**

<a name='already-minified-files-no-longer-minified'></a>
## Already minified files excluded from minification ##

Files with the abbreviation ".min" or "-min" at the end of the file name and before the file extension 
are excluded from minification. 

<a name='minify-html'></a>
## Minify HTML automatically ##

The generated HTML source code is automatically minified when rendering — just enable the option under 
the Minifying tab. No change to the template file is needed. Conditional Comments, textareas, code tags, 
etc., are excluded from the minification. 

<a name='minify-css'></a>
## Minify CSS automatically ##

The HTML source code is searched for **internal** links to Stylesheet (.css) files and they are automatically 
replaced with minified version. Simply enable the option under the Minifying tab. No change to the source
(template file) is needed. The following line:

~~~html
<link rel="stylesheet" href="<?php echo $config->site->templates . 'file.css'; ?>">
~~~

is replaced with:

~~~html
<link rel="stylesheet" href="/site/assets/aiom/css_81c54f1249c3deab897bb50ba39eaf5.css">
~~~

<a name='minify-js'></a>
## Minify JS automatically ##

The HTML source code is searched for **internal** JavaScript files (.js) and they are automatically replaced 
with minified version. No change to the source (template file) is needed. The following line:

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
but not minified and re-generated at each call. Also, a no-cache GET parameter is appended
with a timestamp to prevent browser caching. For example:
`css_031ea978b0e6486c828ba444c6297ca5_dev.css?no-cache=1335939007`

<a name='changelog'></a>
## Changelog ##

See CHANGELOG.md

<a name='questions-or-comments'></a>
## Questions or comments? ##

For any questions, suggestions or bugs, please create a ticket on [GitHub](https://github.com/matjazpotocnik/ProcessWire-AIOM-All-In-One-Minify). 
