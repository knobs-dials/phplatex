
Renders LaTeX into images by calling LaTeX itself from PHP (I'm no fan of PHP, but this does make it pretty portable)

See also http://phplatex.scarfboy.com/


Requirements
- PHP                                    (>=4.3.0, as it uses sha1())
- imagemagick                            (for convert)
- ghostscript, and TeX Live (or teTeX),  (for latex and dvips)
- TeX packages: color, amsmath, amsfonts, amssymb, and the extarticle document class
  Most are standard.   You may need relatively recent versions of some.



Setup/Installation
- Put phplatex.php somewhere from which you can include it
- Have the requirements installed, and check they are where phplatex.php expects them to be (/usr/bin, you can edit this if you want)
- Create subdirecties 'tmp' and 'images' in each directory you will be *calling* the script from, with write permissions for the effective user, for example `mkdir tmp images; chown apache:apache tmp images`
-- TODO: allow for a single global settable tmp and images directories (easier in dynamic sites and such)
- *Optional: configure aoache to serve the images with a far-future Expires (since they won't change)*


Use
- Include the code:
    `include('path/to/phplatex.php');`
- To render some TeX:
    `echo texify("TeX");`
  Due to PHP parsing, you will need to double all your backslahes, and escape your dollar signs

- For advanced use, the function definition is actually:
    `texify(texstring, dpi, r,g,b, br,bg,bb, extraprelude);`
  So, for example:
    `print texify('Times in TeX', 160, 0.2,0.0,0.0, 1.0,1.0,1.0, '\\usepackage{pslatex}');`

Maintenance
- Empty the tmp directory at will  (may sometimes have some leftovers)
- Empty the img directory to clean up old images  (the rest will be regenerated on demand)


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
- Won't work on safe-mode PHP  (which is common on various cheap shared hosting)
- Fails on TeX that is more than one page.
  Should not bother you for most things that are inline.
  TODO: fix that. (e.g. check whether it's in landscape)
  Workaround: use \small or \footnotesize and a larger DPI setting.
- Image conversion can fail for very large images  (hence the DPI cap)
- I cannot guarantee this is safe. That is, if you open it up for nearby people to use, keep in mind that TeX is a full-fledged language.


Arguables
- Initial generation takes a while, perhaps a second per image
  On pages with a lot of texify() calls you will hit the PHP time limit, so you'll need a few refreshes before everything is built and cached.
- Uses \nonstopmode, meaning latex will fix errors it can, so you can get away with some bad TeX.
  But it'll guess, and since we delete the log you won't know what it did.
- You can use arbitrary TeX, which you can see as a feature and as a security issue.
  Know what this means - and use at your own risk.
  Mostly (like any other PHP script not running in safe mode), the processes can do everything 
  that the effective web server user can, so you should have secured your filesystem against that.
  (if you trust everyone on the server and don't let users input TeX, you're safe enough)
- Requires recent TeX version as it uses extarticle and includes color, amsmath, amsfont, and amssymb.
  This may be bothersome for things that aren't recent or not tetex / texlive
- On low resolutions, the (default) Computer Modern fonts don't render as well as, say, pslatex fonts 
  (Times, Helvatica, Courier), due to thickness and antialiasing, so you can change fontset to taste.
  Sharpening helps only a little, and can look worse when colours or shades are involved 
  (I tried convert -unsharp 1x1+1+0).


