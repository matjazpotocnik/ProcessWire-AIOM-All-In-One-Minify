LESS compiler library v5.5.1
============================
https://github.com/wikimedia/less.php
require_once(/*NoCompile*/ __DIR__ . '/lib/wikimedia/less.php/lessc.inc.php');
"php": ">=8.1"


URL rewriting library - rewrite relative to root-relative URIs
==============================================================
https://github.com/mrclay/minify/
only this file is needed: https://github.com/mrclay/minify/blob/master/lib/Minify/CSS/UriRewriter.php
require_once(/*NoCompile*/ __DIR__ . '/lib/mrclay/minify/lib/Minify/CSS/UriRewriter.php'); //unchanged
composer require mrclay/minify
"php": "^8.1",
"ext-pcre": "*",
"intervention/httpauth": "^2.0|^3.0",
"marcusschwarz/lesserphp": "^0.5.5",
"monolog/monolog": "~1.1|~2.0|~3.0",
"mrclay/jsmin-php": "~2",
"mrclay/props-dic": "^4",
"tubalmartin/cssmin": "~4"


JShrink minification library for javascript files
=================================================
https://github.com/tedious/JShrink/
only this file is needed: https://github.com/tedious/JShrink/blob/master/src/JShrink/Minifier.php
require_once(/*NoCompile*/ __DIR__ . '/lib/tedivm/jshrink/src/JShrink/Minifier.php'); //unchanged
composer require tedivm/jshrink
"php": "^7.0|^8.0"


