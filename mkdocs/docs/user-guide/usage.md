Usage
=====

The plugin should be activated for the blog (see main [setting page](/user-guide/settings) of this plugin) before using it.


Replacements
------------

The standard space character are replaced by a non-breaking space:

 * before **:** character
 * before **;** character
 * before **!** and **?** characters
 * after **¡** and **¿** characters
 * after the opening french quote **«** and before the closing french quote **»**
 * as thousands separator
 * before a unit (example 12 kg)

The computing quotes, and double commas are processed as:

 * **"** (double-quote), **‘‘** (couple of curved single-quote) and **''** (couple of straight single-quote) enclosing a word or a group of words are replaced by curved double-quotes **“** (before) and **”** (after)
 * __,,__ (couple of commas) before or after a word or a group of words are replaced by **„** (curved, commonly used in combination with a **“** or a **”**)
 * **'** (straight single-quote) enclosing a word or a group of word are replaced by curved single-quotes **‘** (before) and **’** (after)

The **<<** and **>>** are replaced respectively by **«** and **»** (french pattern « word », finnish pattern »word» and german pattern »word«)

The couple of single dashes **\--** are replaced by a **—** (in wiki mode edition, insert an escape character **\\** before the double-dash as it has a specific signification for the wiki syntax)

!!! Note
	The dashes replacement (en and/or em) may by tuned specifically (see [options](settings/#options))

Three consecutive point **\...** are replaced by **…**


Processing
----------

These replacements are done for entries (posts, pages, …) when you save them using the backend of Dotclear or using an XML/RPC client. Both of excerpt and content are processed by the plugin.

!!! tip
	The replacements are not applied for the content of the following HTML elements: ```<pre>```, ```<code>```, ```<kbd>```, ```<script>``` and ```<math>```. You can use the **\\** escape character to avoid some transformations.


Development
-----------

You can use the typographic replacement function in your own code if the plugin is installed **and** not disabled (for the platform).

Example:

```php
<?php
// Retrieve current en/em dashes mode setting for the current blog
$dashes_mode = (integer)$core->blog->settings->typo->typo_dashes_mode;

// Apply typographic replacement
$html_text = SmartyPants($html_text [,$dashes_mode = 1]);
```

!!! Note
	```$dashes_mode``` is the en/em dashes mode, may be equal to:  
	1 → "\--" for em-dashes; no en-dash support (default)  
	2 → "\-\--" for em-dashes; "\--" for en-dashes  
	3 → "\--" for em-dashes; "\-\--" for en-dashes
