## Changelog

### 4.1.1
* **Security:** Hardened the page cache against path-traversal attempts and improved automatic detection of the site root.
* **Security:** Assets referenced from the templates folder are now kept inside that folder, blocking attempts to read files outside of it (unless the "directory traversal" option is enabled).
* **Fix:** HTML minify no longer strips the whitespace between adjacent inline elements, so rendered text like "Note: click here" and "Hello World" keeps its separating space (previously collapsed to "Note:click here" / "HelloWorld").
* **Fix:** HTML minify now shields inline `<style>` blocks and restores them verbatim, so CSS embedded in HTML is no longer mangled by whitespace normalization.
* **Fix:** HTML minify replaced the blanket line-break removal (which could join words across lines) with a single rule that collapses whitespace runs to a single space.
* **Fix:** CSS minify shields comments and quoted strings in one pass, so quotes inside comments and comment-like text inside strings are no longer treated as CSS syntax.
* **Fix:** CSS minify preserves whitespace before a colon, so descendant selectors like `a :hover` are no longer collapsed into the semantically different pseudo-class `a:hover`.
* **Fix:** A broken or unreadable CSS/JS file is now skipped instead of breaking the whole bundle.
* **Fix:** If all assets end up unreadable, no empty cache file is written, so the page can retry on the next request.
* **Fix:** Cache writes that fail are now detected and logged instead of leaving a broken cache entry.
* **Improvement:** HTML minify protects the content of `<script>`, `<style>`, `<textarea>`, `<pre>` and `<code>` elements plus framework markers (Vue, Alpine, Svelte, Knockout, etc.) from minification.
* **Improvement:** CSS minify strips ordinary comments while keeping important comments (`/*! ... */`).
* **Improvement:** More robust asset path handling — Windows absolute paths, web-root-relative and template-relative paths resolve correctly, cache-busting parameters are stripped, and paths are normalized for consistent cache filenames.
* **Improvement:** In normal mode, remote/CDN availability checks are skipped while the current generated bundle remains cached.
* **Upgrade note:** Clear the AIOM+ asset cache after upgrading. Normalized asset paths may produce new cache filenames; previously generated files otherwise remain until their normal expiration.

### 4.1.0
* **Refactor:** Switched from Lars Moelleken's voku HTML parser to internal script/style manipulation.
* **Update:** Switched from Douglas Crockford's JSmin to Robert Hafner's JShrink.
* **Update:** Switched from Josh Schmidt to the Wikimedia Less parser.
* **Breaking Change:** Raised minimum PHP version to 8.1.
* **Architecture:** Module is now namespaced.
* **Improvement:** General code quality and performance enhancements.

### 4.0.8
* **Fix:** Updates for PHP 8.4 compatibility in `symfony/css_selectors`.
* **Fix:** General bug fixes and internal code improvements.

### 4.0.7
* **Docs:** Updated README and fixed a typo in `_getDirContents()`.

### 4.0.6
* **Compatibility:** Added support for PHP 8.4.
* **Fix:** Corrected various typos.

### 4.0.5
* **Feature:** Added support for config/template prepend/append files in caching.
* **Fix:** Resolved PHP 8.3 issues in the LESS parser.

### 4.0.4
* **Optimization:** Reduced logging verbosity.

### 4.0.3
* **Fix:** External assets are now skipped in auto-minimize mode without throwing errors.
* **Docs:** Updated README and Changelog.

### 4.0.2
* **Fix:** Resolved bugs occurring during module installation.

### 4.0.1
* **Fix:** General bug fixes.

### 4.0.0
* **Feature:** Added AIOM+ template caching.
* **UI:** Introduced a new tabbed interface in the backend.
* **Docs:** Major updates to README, usage guides, and changelog.

---

### 3.2.9
* **Compatibility:** Fixed `less.php` compatibility for PHP 7.4.
* **Update:** Updated `html-min`, `simple_html_dom`, and `css-selector` libraries.

### 3.2.8
* **Fix:** Resolved minification issues when CSS/JS files contain query strings in the source.

### 3.2.6
* **Feature:** Added options to automatically minimize JS and CSS files.
* **Optimization:** Reverted to the original HTML minimize algorithm; it provides better performance than voku.

### 3.2.5
* **Update:** Integrated `voku/HtmlMin` for HTML minimization.

### 3.2.4
* **Fix:** Comprehensive bug fixes and stability improvements.

### 3.2.3
* **Fix:** Corrected a variable typo (Thanks @SteveB).

### 3.2.2
* **Security:** Fixed CHMOD security vulnerability ([#44](https://github.com/conclurer/ProcessWire-AIOM-All-In-One-Minify/issues/44)).
* **Fix:** Resolved MD5 hash issues when processing a high volume of files ([#46](https://github.com/conclurer/ProcessWire-AIOM-All-In-One-Minify/issues/46)).
* **Improvement:** Enhanced detection of file changes.

### 3.2.1
* **Fix:** Corrected `$config->scripts` inclusion logic.
* **Feature:** Added support for `@-webkit-keyframes`.

### 3.2.0
* **Update:** Switched to YUI Compressor for CSS (Thanks @hwmaier).
* **Feature:** Added support for `$config->scripts` and `$config->styles` ([#31](https://github.com/conclurer/ProcessWire-AIOM-All-In-One-Minify/issues/31)).
* **Fix:** Improved handling of empty `{}` brackets and corrected CSS pseudo-class compression.

### 3.1.5
* **Fix:** Resolved broken image links in CSS when `DOCUMENT_ROOT` differs from ProcessWire root.

### 3.1.4
* **Fix:** Automatic cache deletion for pages when new minimized files are generated.
* **Fix:** Resolved errors when document root differs from ProcessWire base path.
* **Note:** AIOM development is now supported by **Conclurer**.

### 3.1.3
* **Update:** Updated LESS parser to v1.7.1 (Improved exception handling, fixed `preg_match` errors).
* **Update:** Updated CSSMin to v1.1.2 (Fixes for Firefox animation rules).

### 3.1.2
* **Feature:** Added backend toggle for the directory traversal filter ([#12](https://github.com/conclurer/ProcessWire-AIOM-All-In-One-Minify/issues/12)).
* **Update:** Updated LESS parser to v1.7.

### 3.1.1
* **Feature:** Added support for conditional loading.
* **Docs:** Updated documentation.

---

### 3.0.1
* **Fix:** Resolved incorrect class order in the Less.php parser ([#11](https://github.com/conclurer/ProcessWire-AIOM-All-In-One-Minify/issues/11)).

### 3.0.0
* **Compatibility:** Tested and verified with ProcessWire 2.4.
* **Localization:** Module is now multilingual.
* **Feature:** Added LESS support (direct parsing and on-the-fly server-side minimization).

---

### 2.2.2
* **Fix:** Resolved errors occurring when `debug` mode is active ([#8](https://github.com/conclurer/ProcessWire-AIOM-All-In-One-Minify/issues/8)).
* **Improvement:** Better error handling and HTML white-space minimization logic.

### 2.2.1
* **Fix:** General code corrections and class name normalization.

### 2.2.0
* **Feature:** Files ending in `.min` or `-min` are now excluded from minification by default.
* **Update:** Updated CSSMin (v1.1) and JSMin (v2.7.1) for security and performance.
* **Performance:** Improvements to the initial minification process.

### 2.1.1
* **Cleanup:** Removed deprecated code.

### 2.1.0
* **Performance:** Integrated a new CSS minimization library and improved execution speed.

### 2.0.0
* **Feature:** Fully configurable backend (Prefix, lifetime, development mode, and tips).
* **Feature:** Added manual cache clearing via the backend.
* **Feature:** Support for domain sharding and cookieless domains (including SSL support).
* **UI:** Added quick-start introduction and `.htaccess` instructions to the backend interface.

---

### 1.1.1
* **Update:** Updated CSS filters.

### 1.1.0
* **Fix:** Resolved `RecursiveDirectoryIterator` permission errors ([#1](https://github.com/conclurer/ProcessWire-AIOM-All-In-One-Minify/issues/1)).
* **Feature:** Introduced short-syntax: `AIOM::CSS();` and `AIOM::JS();`.
* **Feature:** Added toggles for HTML minification and development mode (combine but no minifying).

### 1.0.0
* **Initial Release:** Core minification functionality for CSS, JS, and LESS.

---

> For a full list of community contributors and historical patches, please see [CONTRIBUTORS.md](./CONTRIBUTORS.md).
