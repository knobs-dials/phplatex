
Renders LaTeX into images by calling LaTeX itself from PHP.

Requirements
- PHP                                    (>=4.3.0, as it uses sha1())
- imagemagick                            (for convert)
- ghostscript, and TeX Live (or teTeX),  (for latex and dvips)
- TeX packages: color, amsmath, amsfonts, amssymb, and the extarticle document class
  Most are standard.   You may need relatively recent versions of some.



Setup/Installation
- Have the requirements installed
  all assumed to be in /usr/bin. You can change the script's reference to them if necessary
- Put phplatex.php somewhere from which you can include it.
- In each directory you will be *calling* the script from, create subdirecties 'tmp' and 'images' 
  with write permissions for the effective user, for example:
    mkdir tmp images; chown apache:apache tmp images
- Optional: configure aoache to serve the images with a far-future Expires (since they won't change)


Use
- Include the code:
    include('path/to/phplatex.php');
- Each use:
    echo texify("TeX");
  This function will return an string containing <img src="...">,
  which you'll probably PHP-print into the document to show the image.

  Because of PHP, you'll need to double blackslashes, and escape dollar signs

  The function definition is actually:
    texify(texstring, dpi, r,g,b, br,bg,bb, extraprelude); 
Maintenance
- You can empty the tmp directory at will (may sometimes have some leftovers)
- You can empty the img directory to clean up old images (and have the rest be regenerated on demand)


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
  (Times, Helvatica, Courier), due to thickness and antialiasing, so change fontset to taste.
  Sharpening helps only a little, and can look worse when colours or shades are involved 
  (I tried convert -unsharp 1x1+1+0).

