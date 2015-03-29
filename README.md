
Renders LaTeX into images by calling LaTeX itself from PHP.

(I'm no fan of PHP, but I figured this would make it nicely portable)


Requirements
- PHP                                    (>=4.3.0, as it uses sha1())
- imagemagick                            (for convert)
- ghostscript, and TeX Live (or teTeX),  (for latex and dvips)
- TeX packages: color, amsmath, amsfonts, amssymb, and the extarticle document class
  Most are standard.   You may need relatively recent versions of some.



Setup/Installation
- Put phplatex.php somewhere from which you can include it.
- Have the requirements installed. 
-- Binaries are assumed to be in /usr/bin. If they are not, edit phplatex.php
- Create subdirecties 'tmp' and 'images' in each directory you will be *calling* the script from, with write permissions for the effective user, for example `mkdir tmp images; chown apache:apache tmp images`
-- TODO: allow for a global settable tmp and images directories (easier in dynamic sites and such)
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
- Will cache generated images (based on document string), so asking for the same TeX is 
  trivial (a filesystem check and read). This means you can leave the texify() calls in your code.
- TeX text used inline in HTML text should show up somewhat decently 
  (there is some logic looking at character descenders and using CSS lowering to compensate)
- Allows inclusion of extra TeX packages (via extraprelude)
- Tweakable size. The default (90) is usually the same size as HTML text.
  Capped at 300 to avoid resource hogging
- Allows coloring of page background and default text color
  (default is black on white, specifically 0.,0.,0. on 1.,1.,1.)
- Generates PNGs with transparency (note: consider antialiasing to that background)
- Relies on image trimming instead of trusting dvips' bounding box.


Caveats
- Probably won't work on safe-mode PHP (which is common on much of the cheap shared hosting)
- Initial generation takes a while, perhaps a second per image
  On pages with a lot of images you may hit the PHP time limit, 
  and you'll need a few refreshes before everything is built and cached.
- Fails when you include TeX that typesets as two pages or more (try \small)
  (for some reason I'm using landscape. Unless I remember why I'll be removing that,
   because that just makes things more likely to layout onto two pages)
- Image conversion fails for very large images  (hence the resolution cap; >300dpi causes easily causes this)


Arguables
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


See also
- http://phplatex.scarfboy.com/

