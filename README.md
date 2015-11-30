
Renders LaTeX into images by calling LaTeX itself from PHP (I'm no fan of PHP, but this does make it pretty portable)

See also http://phplatex.scarfboy.com/


Requirements
- PHP                                    (>=4.3.0, as it uses sha1())
- imagemagick                            (for convert)
- ghostscript, and TeX Live (or teTeX),  (for latex and dvips)
- TeX packages: color, amsmath, amsfonts, amssymb, and the extarticle document class.
  Most are standard.   You may need relatively recent versions of some.



Installation
- Put phplatex.php somewhere from which you can include it
- Have the requirements installed, and check they are where phplatex.php expects them to be (/usr/bin, you can edit this if you want)
- Create subdirecties 'tmp' and 'images' in each directory you will be *calling* the script from, with write permissions for the effective user, for example `mkdir tmp images; chown apache:apache tmp images`
-- TODO: allow for a single global settable tmp and images directories (easier in dynamic sites and such)
- *Optional: configure apache to serve these images with a far-future Expires: header*


Use
- Include the code:
    `include('path/to/phplatex.php');`
- To render some TeX:
    `echo texify("TeX");`
Due to PHP parsing, you will need to double all your backslahes, and escape your dollar signs.
PHP offers no alternatives to that.


For advanced use, the function definition is actually:
-  `texify(texstring, dpi, r,g,b, br,bg,bb, extraprelude);`
So, for example:
-  `print texify('Times in TeX', 160, 0.2,0.0,0.0, 1.0,1.0,1.0, '\\usepackage{pslatex}');`

Maintenance
- Remove leftovers in the tmp directory at will
- You can empty the img directory to remove unused images (still-used one will be regenerated)


Features
- Will cache generated images. 
  Based on a hash of the document string, using the same TeX uses the cached image via a filesystem check+read). This means leaving the texify() calls in your code is cheap.
- CSS lowering to compensate for descenders, so TeX text used inline in HTML should look halfway decent.
- Tweakable size. The default (90) is approximately the same size as HTML text. Capped at 300.
- Allows inclusion of extra TeX packages, via extraprelude.
- Allows coloring of page background and default text color   (default is black on white, 0.,0.,0. on 1.,1.,1.)
- Generates PNGs with transparency (note: consider antialiasing to that background)
- Relies on image trimming (instead of e.g. trusting dvips' bounding box)


Caveats
- Won't work on safe-mode PHP  (common enough on cheaper shared hosting)
- Fails on TeX that is more than one page.
  Should not bother you for most things that are inline.
  Workaround: use \small or \footnotesize and a larger DPI setting.
  TODO: think about better fixes.
- Image conversion can fail for very large images  (hence the DPI cap)
- I cannot guarantee this is safe from a security standpoint -- in theory it's mostly fine, but TeX *is* a full-fledged language.


Arguables
- Image generation can take a second per image. You may hit the PHP time limit a few times before
  a page with a lot of TeX images is all built and cached.
- Uses \nonstopmode, meaning latex will fix errors it can rather than complain. You can get away with some bad TeX
- No input filter on what TeX is allowed. Know what this means security-wise - USE AT YOUR OWN RISK.
  Basically, the processes can do everything the effective user (of the apache process) can.
- On low resolutions, the (default) Computer Modern fonts don't render as well as, say, pslatex fonts 
  (Times, Helvatica, Courier), due to thickness and antialiasing. Change fontset to taste.


