
Renders LaTeX into images by calling LaTeX itself from PHP.

See also http://phplatex.scarfboy.com/ and example use on http://latex.knobs-dials.com/


## Requirements
- PHP                                    (>=4.3.0, as it uses sha1())
- imagemagick                            (for convert)
- ghostscript, and TeX Live or teTeX     (for latex and dvips)
- TeX packages: color, amsmath, amsfonts, amssymb, and the extarticle document class.
  Most are standard.   You may need non-ancient versions of some.


## Installation
- Put `phplatex.php` somewhere from which your PHP process can include it
- Have the requirements installed, and check they are where phplatex.php expects them to be (/usr/bin), or edit it as needed
- Create subdirecties `tmp` and `images` in each directory you will be *calling* the script from, with write permissions for the effective user, for example `mkdir tmp images; chown apache:apache tmp images`

- If in use you get an error like "convert: not authorized" or "attempt to perform an operation not allowed by the security policy", this is likely due to ImageMagick updates (2018) that disabled PDF and/or PS conversions by default, apparently for security flaws in GhostScript [which were soon fixed](https://www.kb.cert.org/vuls/id/332928/), meaning that if you have updates since then you can [tweak imagemagick's `policy.xml` to re-enable it](https://www.google.com/search?q=convert%3A+not+authorized+policy.xml).


## Use
- Include the code:   `include('path/to/phplatex.php');`
- Render some TeX: `echo texify("TeX");`

Due to PHP parsing, you will need to double all your backslahes, and escape your dollar signs, like `\$\\sqrt[3]{2}\$`.
PHP offers no alternatives to this. 
Yes, you can selectively get away with not doing it, e.g. if dollar signs aren't followed by text and so can't name a variable, but it's probably less confusing if you are consistent with this.


For advanced/creative (ab)users, the function definition is actually

        texify(texstring, dpi, r,g,b, br,bg,bb, extraprelude)
so you can do things like

        print texify('Times in TeX', 160, 0.2,0.0,0.0, 1.0,1.0,1.0, '\\usepackage{pslatex}')


## Maintenance
- Remove leftovers in the tmp directory at will
- You can empty the img directory to remove unused images (still-used one will be regenerated)


## Features
- Will cache generated images, based on a hash of the document string.
  This means leaving texify() on your page is cheap, as later calls will not run LaTeX at all.
- TeX text used inline in HTML is lowered somewhat (via CSS) when it contains characters that descend below the average.
- Tweakable size. The default (90) is approximately the same size as typical page text. Capped at 300 for memory reasons.
- Allows inclusion of extra TeX packages, via extraprelude.
- Allows coloring of page background and default text color   (default is black on white, 0.,0.,0. on 1.,1.,1.)
- Generates PNGs with transparency (note: consider antialiasing to that background)
- Relies on image trimming (instead of e.g. trusting dvips' bounding box)


## Caveats
- I cannot guarantee this is safe from a security standpoint -- in theory it's mostly fine, but TeX *is* a full-fledged language.
  There is no input filter on what TeX is allowed, because that wouldn't even work. *Know what this means security-wise - USE AT YOUR OWN RISK*.
  In particular, the processes can do everything the effective user (of the apache process) can.
- Won't work on safe-mode PHP, which was common on cheap oldschool shared hosting (though it was removed in PHP5.3)
- Fails on TeX that is more than one page.
  Should not bother you for most things that are inline (and for documents, there are better solutions than this).
  Sometimes-workaround: use `\small` or `\footnotesize` and a larger DPI setting.
  TODO: think about better fixes.
- Image conversion can fail for very large images  (hence the DPI cap)
- the relative tmp and images directories are a little awkward. But the alternative (having a configurable path, to e.g. share the cache) would involve more thinking of how that is exposed URL-wise


## Arguables
- Uses latex's `\nonstopmode`, meaning TeX will best-guess-fix errors it can, rather than complain and stop. You can get away with some bad TeX and never know it.
- On low resolutions, the (default) Computer Modern fonts don't render as nicely as, say, pslatex fonts 
  (Times, Helvatica, Courier), due to thickness and antialiasing. Change fontset to taste.
- Image generation can take a second per image. You may hit your configured PHP max_execution_time limit a few times before
  a page with a lot of TeX images is all built and cached.
- I'm no particular fan of PHP, but this does make it pretty portable.



## When to use something else: Just math with fewer requirements

This project was made to get a real TeX environment, to compile arbitrary TeX.

If you care only about formulae on webpages, and don't care about the precise behaviour of compiled TeX, then you can get 95% of the way there, get _better_ indexability and copyability for simple things, and/or avoid a heavy depdendency and server-side anything and their security issues, by considering options such as:

- [mathJax](https://www.mathjax.org/)
  - takes LaTeX, [MathML](https://en.wikipedia.org/wiki/MathML), and [AsciiMath](https://en.wikipedia.org/wiki/AsciiMath) 
  - produces HTML+CSS (and experimental SVG), or MathML where browser supports
  - code is pure JS

- [latex.js](https://latex.js.org/)
  - takes TeX
  - produces HTML and SVG
  - code is pure JS
  - seems to do [a little more than just math](https://latex.js.org/playground.html)


You may also care about live editing, like [latex.js's](https://latex.js.org/playground.html), [mathurl's](http://mathurl.com/), and others


If using mediawiki: the The [mediawiki math extension](https://www.mediawiki.org/wiki/Extension:Math) delivers MathML where supported, and falls back to SVG and PNG via [mathoid](https://github.com/wikimedia/mathoid). This only takes its own TeX dialect, texvc, which seems to be safer restricted set of commands, run through [a validator](https://github.com/wikimedia/mediawiki-services-texvcjs).


Less _directly_ interesting but worth mentioning:
- embedded MathML / <math> element 
  - MathML has existed for 20+ years, but [browser support allowing direct use in webpages _quite_ recent](https://caniuse.com/mathml)
  - useful as one rendering option among others

- [ASCIIMathML.js](https://mathcs.chapman.edu/~jipsen/mathml/asciimath.html)
  - takes AsciiMath, makes MathML
  - useful as a component, integrated into mathJax

- [jsMath](http://www.math.union.edu/~dpvc/jsmath/)
  - takes LaTeX, MathML (XML based), and asciimath
  - produces HTML+CSS 
  - code is pure JS
  - succeeded by mathJax

- components such as iTeX for TeX to MathML, svgmath for MathML to SVG
  - useful for projects but not end users directly

