# Typographic replacement plugin for Dotclear 2

!!! tip "Current release"
    [1.9 — 2016-02-14](https://open-time.net/post/2016/02/14/Plugin-Typo-19-pour-Dotclear) (Dotclear 2.6+)

![](img/icon-big.png)

This plugin use the [PHP SmartyPants & Typographer library](https://michelf.ca/projets/php-smartypants/) from Michel Fortin (PHP port of the [Original SmartyPants Perl library](https://daringfireball.net/projects/smartypants/) from John Gruber) that easily translates plain ASCII punctuation characters into “smart” typographic punctuation HTML entities.

## Example

The following text:

> Un jour \-- ou plutôt une nuit \--, je crois cela se déroulait pendant ma lecture de << À la recherche du temps perdu >>, il me semble\... Mais je m\'égare alors reprenons le fil de notre conversation : \"¿ Cómo estás ?\" me demandait le garçon de café auquel j\'avais répondu sans faire attention d\'un >>Ich weiß es nicht<< maladroit.

Will be transformed to this:

> Un jour — ou plutôt une nuit —, je crois que cela se déroulait pendant ma lecture de « À la recherche du temps perdu », il me semble… Mais je m’égare alors reprenons le fil de notre conversation : “¿ Cómo estás ?” me demandait le garçon de café auquel j’avais répondu sans faire attention d’un »Ich weiß es nicht« maladroit.

## Installation

The plugin may be downloaded and installed from following sources:

 * [DotAddict](http://plugins.dotaddict.org/dc2/details/typo)
 * [Open-Time](https://open-time.net/post/2016/02/14/Plugin-Typo-19-pour-Dotclear)

Or directly from the administration plugins page of Dotclear

## Usage

The plugin **must** be activated before being used on your blog[^1]. See [user guide](user-guide/usage.md) for more information.

[^1]: Go to the main page of the Typo plugin to activated it (Blog section of the Dotclear admin menu).
