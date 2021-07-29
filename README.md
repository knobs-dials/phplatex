
Renders LaTeX into images by calling LaTeX itself from PHP.

See also http://phplatex.scarfboy.com/ and example use on http://latex.knobs-dials.com/


## Requirements
- PHP                                    (>=4.3.0, as it uses sha1())
- imagemagick                            (for convert)
- ghostscript, and TeX Live (or teTeX),  (for latex and dvips)
- TeX packages: color, amsmath, amsfonts, amssymb, and the extarticle document class.
  Most are standard.   You may need non-ancient versions of some.


## Installation
- Put `phplatex.php` somewhere from which you can include it
- Have the requirements installed, and check they are where phplatex.php expects them to be (/usr/bin), or edit it as needed
- Create subdirecties 'tmp' and 'images' in each directory you will be *calling* the script from, with write permissions for the effective user, for example `mkdir tmp images; chown apache:apache tmp images`

- If you get "convert: not authorized" this is likely due to an 2018 ImageMagick update that disable PDF/PS conversions by default, apparently for security, and you need to tweak its policy.xml to re-enable it.


## Use
- Include the code:
    `include('path/to/phplatex.php');`
- To render some TeX:
    `echo texify("TeX");`

Due to PHP parsing, you will need to double all your backslahes, and escape your dollar signs, like `\$\\sqrt[3]{2}\$`.
PHP offers no alternatives to this. Yes, you can selectively get away with not doing it (e.g. if dollar signs aren't followed by text so can't name a variable, like in this  example), but it's probably less confusing if you are consistent with this.


For advanced/creadive (ab)use, the function definition is actually:
-  `texify(texstring, dpi, r,g,b, br,bg,bb, extraprelude);`
So, for example:
-  `print texify('Times in TeX', 160, 0.2,0.0,0.0, 1.0,1.0,1.0, '\\usepackage{pslatex}');`


## Maintenance
- Remove leftovers in the tmp directory at will
- You can empty the img directory to remove unused images (still-used one will be regenerated)


## Features
- Will cache generated images, based on a hash of the document string.
  Meaning leaving the texify() calls on your page is cheap as successive runs will not run LaTeX at all.
- CSS lowering to compensate for descenders, so TeX text used inline in HTML should look halfway decent.
- Tweakable size. The default (90) is approximately the same size as HTML text. Capped at 300 for memory reasons.
- Allows inclusion of extra TeX packages, via extraprelude.
- Allows coloring of page background and default text color   (default is black on white, 0.,0.,0. on 1.,1.,1.)
- Generates PNGs with transparency (note: consider antialiasing to that background)
- Relies on image trimming (instead of e.g. trusting dvips' bounding box)


## Caveats
- Won't work on safe-mode PHP  (common enough on cheap shared hosting)
- I cannot guarantee this is safe from a security standpoint -- in theory it's mostly fine, but TeX *is* a full-fledged language.
  There is no input filter on what TeX is allowed. *Know what this means security-wise - USE AT YOUR OWN RISK*.
  In particular, the processes can do everything the effective user (of the apache process) can.
- Fails on TeX that is more than one page.
  Should not bother you for most things that are inline.
  Sometimes-workaround: use \small or \footnotesize and a larger DPI setting.
  TODO: think about better fixes.
- Image conversion can fail for very large images  (hence the DPI cap)
- the relative tmp and images directories are a little awkward. But the alternative (having a configurable path, to e.g. share the cache) would involve more thinking of how that is exposed URL-wise


## Arguables
- Uses latex's `\nonstopmode`, meaning it willbest-guess-fix errors it can, rather than complain and stop. You can get away with some bad TeX
- On low resolutions, the (default) Computer Modern fonts don't render as nicely as, say, pslatex fonts 
  (Times, Helvatica, Courier), due to thickness and antialiasing. Change fontset to taste.
- Image generation can take a second per image. You may hit your configured PHP max_execution_time limit a few times before
  a page with a lot of TeX images is all built and cached.
- I'm no particular fan of PHP, but this does make it pretty portable.



## See also

This project was made to get a real TeX environment, to compile arbitrary TeX.

If you care only about formulae on webpages, you can avoid that heavy depdendency and its security issues, by considering options like like:

- [mathJax](https://www.mathjax.org/)
  - takes LaTeX, MathML, and AsciiMath 
  - code is pure JS
  - produces HTML+CSS (and experimental SVG), or MathML where browser supports

- [latex.js](https://latex.js.org/)
  - takes TeX
  - code is pure JS
  - produces HTML and svg
  - seems to do [a little more than just math](https://latex.js.org/playground.html)


Less interesting:
- embedded [MathML](https://en.wikipedia.org/wiki/MathML) / <math> element 
  - has been standardized for a while, but [only a few browsers allow direct use in webpages](https://caniuse.com/?search=math)

- [ASCIIMathML.js](https://mathcs.chapman.edu/~jipsen/mathml/asciimath.html)
  - takes [asciimath](https://en.wikipedia.org/wiki/AsciiMath), makes MathML
  - integrated into mathJax

- [jsMath](http://www.math.union.edu/~dpvc/jsmath/)
  - takes LaTeX, MathML (XML based), and asciimath
  - code is pure JS
  - produces HTML+CSS 
  - succeeded by mathJax


