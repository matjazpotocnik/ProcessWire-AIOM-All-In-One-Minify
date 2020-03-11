# AIOM+ (All In One Minify) #

#### Simple caching solution with minifying and parsing for CSS, LESS, JS and HTML ####
-----------------------------

AIOM+ (All In One Minify) is a ProcessWire module to easily improve the performance of your website.
By a simple function call Style sheets, LESS and JavaScript files can be parsed, minimized and
combined into one single file. This reduces the server requests, loading time and minimizes the
traffic. Besides, the generated HTML source code, Style sheets and JavaScript files can be minimized
automatically (without any programming) and all generated files can be loaded over a Cookieless
domain (domain sharding). Even more: AIOM+ can enhance ProcessWire's builtin template caching and
**noticable** speed up your site.

**NOTE**: This forked version includes several pull requests, fixes and modifications. See the
changelog at the end of this document or CHANGELOG.md or sources for more info. Also, from version 4.0.0
AIOM+ template caching is added by Matjaž Potočnik.

**NOTE:** Minimizing process slightly increases the page rendering time. I recommend that you
minimize your css and js assets in advance (and let web server and browser cache them), then set up
template caching and enable AIOM+ caching. Or use a commercial product
[ProCache](https://processwire.com/store/pro-cache/).

- - -

#### Information ####

* All paths are relative to the template folder. URLs in css files will be automatically corrected. Nothing needs to be changed. 
* If you make changes to the source stylesheet, LESS or javascript files, a new parsed and combined version is created automatically. 
* If you make changes to the source stylesheet, LESS or javascript files and AIOM+ template caching is enabled, the template cache is cleared.
* During development, you can enable developer mode. Files are parsed and combined, but not minimized and browser caching is prevented. 
* You can use the short syntax `\AIOM` or use the full class name `\AllInOneMinify` in your templates. 
* The generated files can be delivered via a subdomain (Domain sharding / Cookieless domain).
[comment]: # (* LESS files can directly server-side generated on the fly, without plugins. AIOM+ has a complete, high-performance PHP ported LESS library of the official LESS processor included!) 
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
2. In admin: Modules > Check for new modules.
3. Install Module "AIOM+ (All In One Minify) for CSS, LESS, JS and HTML".

<a name='caching'></a>
## Caching ##

In ProcessWire, a template can be configured to cache its output on the front-end so that it only
executes its PHP template-file at certain intervals (cache time), and delivers cached content the
rest of the time. Templates caching can help with page render time on resource-heavy pages by
serving pages from disk cache rather than creating pages on every request. But caching is not that
efficient on simple, resource-light pages. AIOM+ use cached pages but delivers them more
efficiently. For this to work, you have to set up template caching in admin and also edit
`/index.php` file on your webroot and include file AIOMcache.php. I know that modifying core
ProcessWire files is not a good idea, but I don't see other options. index.php file is very rarely
changed during ProcessWire upgrade so I think it's not a big deal. Edit `index.php` located on your
webroot folder with a text editor and add this line *before* any ProcessWire statements:

~~~html
@include('site/modules/AllInOneMinify/AIOMcache.php')
~~~

As an alternative, if you don't want or can't change index.php, next option is to edit `/site/config.php`
and add this at the end of the config:

~~~html
@include($config->paths->siteModules . 'AllInOneMinify/AIOMcache.php');
~~~

This option is not as fast as inclusion in index.php since ProcessWire is already halfway done booting,
but, it's better than nothing.

Next, set up template caching: enter the number of seconds in the _Cache time_ input field,
select _Clear cache for the saved page only_ for _Page save / Cache expiration_
and _Guests only_ for _Cache when rendering pages for these users_. When a page is served from the
AIOM+ cache `data-cache='AIOM'` will be added to the `<body>` or `<html>` tag. Finaly, 
enable AIOM+ template caching in this module settings under Caching tab.

When you use template caching (with or without AIOM+) and you modify the primary template file (for
example basic-page.php), prepend file (for example _init.php) or append file, the modifications made
to those files are not reflected on cached page. AIOM+ can be instructed to monitor those files and
if they are changed, it clears the cache for the page. You can specify files to monitor by entering
the file names in the Aditional cache clear option field. Enter each file name on its own line.
Files are relative to `/site/templates/` folder. Specify template files (alternate/prepend/append)
as `{template_files}` and `{config_template_files}`. This works even if you don't enable AIOM+ caching.

**NOTE:** AIOM+ template caching works only for guest users, that is, it won't fire up if
wire_challenge or wires_challenge cookie is present. It also won't work for POST requests or GET requests
with parameters.

<a name='minimizing'></a>
## Minimizing ##

You can minimize generated HTML source, CSS/LESS files and JS files. CSS and JS files can be minimized automatically or
manually. In automatic mode, AIOM+ parse the generated HTML and replace the reference to css/js files with
the minified version. In manual mode, you call API function in your template file to minimize js/css file. 

<a name='minimize-stylesheets-and-parse-less-files'></a>
## Minimize Stylesheets and parse LESS files ##

Minimization of a single file.

~~~html
<!-- CSS Stylesheet -->
<link rel="stylesheet" type="text/css" href="<?php echo \AllInOneMinify::CSS('css/stylesheet.css'); ?>">

<!-- LESS file -->
<link rel="stylesheet" type="text/css" href="<?php echo \AllInOneMinify::CSS('css/stylesheet.less'); ?>">
~~~

Minimize multiple files into one file. You can even mix stylesheet and LESS files in parsing and combining process!

~~~html
<link rel="stylesheet" href="<?php echo \AllInOneMinify::CSS(array('css/file-1.css', 'css/file-2.less', 'css/file-3.css', 'css/file-4.less')); ?>">
~~~

**Tip:** You can also use the short syntax `"AIOM"`. For example, `\AIOM::CSS()`.

<a name='less-variables-access-in-multiple-files'></a>
## LESS variables access in multiple files ##

You have a LESS file in which you are defining, for example, all colors and another LESS file that defines the actual layout? Now you need in the layout LESS file access to the variables of the color LESS file? It's easier than you think. Through a simple referencing of source LESS file. For example: 

~~~html
<link rel="stylesheet" type="text/css" href="<?php echo \AllInOneMinify::CSS('css/color.less'); ?>">
...
<link rel="stylesheet" type="text/css" href="<?php echo \AllInOneMinify::CSS('css/layout.less'); ?>">
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

That's all. Pretty, hu? The full documentation of LESS you can find at www.lesscss.org

<a name='minimize-javascripts'></a>
## Minimize Javascripts ##

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

Since AIOM+ version 3.1.1 javascripts, stylesheets and LESS files can be loaded based on a API selector. Here is an example of conditional loading: 

~~~html
<?php $stylesheets  = array('css/reset.css',
                            'css/main.less',
					        array('loadOn'  => 'id|template=1002|1004|sitemap', 
						          'files'   => array('css/special.css', 'css/special-theme.less'))); ?>
						          
<link rel="stylesheet" type="text/css" href="<?php echo \AIOM::CSS($stylesheets); ?>" />
~~~

The same you can do with `\AIOM::JS()`. `loadOn` must be an [ProcessWire API selector](https://processwire.com/docs/selectors/).

<a name='directory-traversal-filter'></a>
## Directory Traversal Filter ##

By default, only files can be included, which are in the ProcessWire template folder. If you wish to add files outside that folder, you have to activate the backend "Allow Directory Traversal" option. Then you can jump back in the path. For example: 
~~~html
\AIOM::CSS('../third-party-packages/package/css/example.css');
~~~
**All paths are still automatically corrected!**

<a name='already-minimized-files-no-longer-minimized'></a>
## Already minimized files no longer minimized ##

To further enhance the performance and to give you the maximum flexibility in the combining process, you now have the option to exclude certain files from the minimization (since version 2.2). All files that have the abbreviation ".min" or "-min" at the end of the file name and before the file extension, are no longer minimized. For example, `file-1.js` is minimized. `file-1-min.js` or `file-1.min.js` is not minimized. The same for CSS. `file-1.css` is minimized. `file-1-min.css` or `file-1.min.css` is not minimized.

<a name='minimize-html'></a>
## Minimize HTML automatically ##

The generated HTML source code is automatically minimized when rendering, just enable the option under the Minimize tab. No change to the template file is needed. Conditional Comments, textareas, code tags, etc. are excluded from the minimization. 

<a name='minimize-css'></a>
## Minimize CSS automatically ##

The HTML source code is searched for links to Stylesheet (.css) files and automatically replaced with minimized version, just enable the option under the Minimize tab. No change to the template file is needed. The following line:

~~~html
<link rel="stylesheet" href="<?php echo $config->site->templates . 'file.css'; ?>">
~~~

is replaced with:

~~~html
<link rel="stylesheet" href="/site/assets/aiom/css_81c54f1249c3deab897bb50ba39eaf5.css">
~~~

<a name='minimize-js'></a>
## Minimize JS automatically ##

The HTML source code is searched for JavaScript files (.js) and automatically replaced with minimized version. No change to the template file is needed. The following line:

~~~html
<script src="<?php echo $config->site->templates . 'file.js'; ?>">
~~~

is replaced with:

~~~html
<script src="/site/assets/aiom/js_81c54f1249c3deab897bb50ba39eaf5.js">
~~~

<a name='development-mode'></a>
## Development mode ##

If you are currently in development of the site, caching can be a problem. For this, you can enable
the development mode. The files will be combined (when using CSS() or JS() in your template files),
but not minimized and re-generated at each call. In addition, a no-cache GET parameter is appended
with a timestamp to prevent the browser caching. For example:
`css_031ea978b0e6486c828ba444c6297ca5_dev.css?no-cache=1335939007`

<a name='changelog'></a>
## Changelog ##

See CHANGELOG.md

<a name='questions-or-comments'></a>
## Questions or comments? ##

For any questions, suggestions or bugs please create a ticket on [GitHub](https://github.com/matjazpotocnik/ProcessWire-AIOM-All-In-One-Minify). 
