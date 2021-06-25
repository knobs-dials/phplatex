<?php    
# Written by scarfboy@gmail.com. 
# Use at your own risk.
# See README for usage details.

#In case these are elsewhere
$path_to_latex   = '/usr/bin/latex';
$path_to_dvips   = '/usr/bin/dvips';
$path_to_convert = '/usr/bin/convert';

$imgfmt="png"; # used in extensions, and in parameters to convert. Should be either png or gif.


function phplatex_cleantmp($tempfname,$todir) {
  # removes the various files that probably got created for a specific run, based on the run's filename.
  global $imgfmt;
  if (chdir($todir)===FALSE) { return '[directory access error, fix permissions (and empty tmp manually this time)]'; }
  error_reporting(0); #at least one of these probably will not exist, but disable the error reporting related to that.
  unlink($tempfname);     #the longer/cleaner way would be check for existance for each
  unlink($tempfname.".tex");  unlink($tempfname.".log");
  unlink($tempfname.".aux");  unlink($tempfname.".dvi");
  unlink($tempfname.".ps");   unlink($tempfname.".".$imgfmt);
  error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
  #try-catch would have been nice. This is rather overkill too, the way I use it.
  return '';
}

function phplatex_colorhex($r,$g,$b) {
  #there has to be a better way of doing this. It's not even particularly clean.
  $hex=array("","","");
  if(strlen($hex[0]=dechex(min(255*$r,255)))==1){ $hex[0]="0".$hex[0]; }
  if(strlen($hex[1]=dechex(min(255*$g,255)))==1){ $hex[1]="0".$hex[1]; }
  if(strlen($hex[2]=dechex(min(255*$b,255)))==1){ $hex[2]="0".$hex[2]; }
  return implode("",$hex);
}


function texify($string,$dpi='90', $r=0.0,$g=0.0,$b=0.0, $br=1.0,$bg=1.0,$bb=1.0,$extraprelude="", $trans=FALSE) {
  global $imgfmt,$path_to_latex,$path_to_dvips,$path_to_convert;
  if ($dpi>300) $dpi=300;
  
  $back = phplatex_colorhex($br,$bg,$bb);
  $fore = phplatex_colorhex($r,$g,$b);
  
  # Figure out TeX string, either to get the right cache entry, or to compile
  # Adds semi-common symbol packages (ams)
  # used to include ,landscape in documentclass to avoid page wrapping, but it seems this sometimes implies 90 degree rotation
  $totex = "\\documentclass[14pt]{extarticle}\n".
           "\\usepackage{color}\n".
           "\\usepackage{amsmath}\n\\usepackage{amsfonts}\n\\usepackage{amssymb}\n".
           $extraprelude."\n".
           "\\pagestyle{empty}\n".  #removes header/footer; necessary for trim
           "\\begin{document}\n".
           "\\color[rgb]{".$r.",".$g.",".$b."}\n".
           "\\pagecolor[rgb]{".$br.",".$bg.",".$bb."}\n".
           $string."\n".
           "\\end{document}\n";
  $hashfn = sha1($totex).".".$dpi.".".$fore.".".$back.".".intval($trans);  #file cache entry string:  40-char hash string plus size
  $stralt = str_replace("&","&amp;", preg_replace("/[\"\n]/","",$string)); # stuck in the alt and title attributes
                                                                           # May need some extra safety.
  $heredir = getcwd();
  
  # Experiment: Tries to adjust vertical positioning, so that rendered TeX text looks natural enough inline with HTML text
  #  Only descenders are really a problem since HTML's leeway is upwards.
  #  Some things vary per font, e.g. the slash. In the default CM it is a descender, in Times and others it isn't.
  #  TODO: This can always use more work. 
  #  TODO: Avoid using characters that are part of TeX commands.
  $ascenders ="/(b|d|f|h|i|j|k|l|t|A|B|C|D|E|F|G|H|I|J|L|K|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z|\[|\]|\\{|\\}|\(|\)|\/|0|1|2|3|4|5|6|7|8|9|\\#|\*|\?|'|\\\\'|\\\\`|\\\\v)/";
  $monoliners="/(a|c|e|m|n|o|r|s|u|v|w|x|z|-|=|\+|:|.)/";
  $descenders="/(g|j|p|\/|q|y|Q|,|;|\[|\]|\\{|\\}|\(|\)|\#|\\\\LaTeX|\\\\TeX|\\\\c\{)/";
  $deepdescenders="/(\[|\]|\\{|\\}|\(|\)|\\int)/";
  
  $ba = preg_match_all($ascenders,  $string,$m); 
  $bm = preg_match_all($monoliners, $string,$m); 
  $bd = preg_match_all($descenders, $string,$m); 
  $dd = preg_match_all($deepdescenders, $string,$m); 
  if      ($dd>0)            $verticalalign="vertical-align: -25%";   # deep descenders: move down
  else if ($bd>0 && $ba==0)  $verticalalign="vertical-align: -15%";   # descenders:  move down
  else if ($bd==0 && $ba>0)  $verticalalign="vertical-align: 0%";     # ascenders only: move up/do nothing?
  else if ($bd==0 && $ba==0) $verticalalign="vertical-align: 0%";     # neither    vertical-align: 0%
  else                       $verticalalign="vertical-align: -15%";   # both ascender and regular descender

  
  # check if image for that TeX in the cache, return img HTML if it exists
  if (file_exists($heredir.'/images/'.$hashfn.'.'.$imgfmt)) 
    return '<img style="'.$verticalalign.'" title="'.$stralt.'" alt="'.$stralt.'" src="images/'.$hashfn.'.'.$imgfmt.'">';

  # otherwise try to make and store:
  
  # chdir to have superfluous files be created in just one place, tmp/  (you probably want to occasionally clean this yourself)
  error_reporting(0); # TODO: fetch current value so we can restore it
  if (chdir("tmp")===FALSE) { return '[tmp directory access error, please fix permissions]'; } #I should think about some more specific errors, e.g. check whether file creation is allowed
  error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE); 
  
  $tfn = tempnam(getcwd(), 'PTX'); # unique base path in tmp dir
  
  #write temporary .tex file
  if ( ($tex = fopen($tfn.'.tex', "w"))==FALSE) { return '[tex file access error] '.phplatex_cleantmp($tfn,$heredir); }
  fwrite($tex, $totex); 
  fclose($tex);

  
  # Run latex to create a .dvi.  Have it try to fix minor errors instead of breaking/pausing on them.
  exec($path_to_latex.' --interaction=nonstopmode '.$tfn.'.tex');
  if (!file_exists($tfn.".dvi")) {
    $log = file_get_contents($tfn.'.log'); #The log always exists, but now it's actually interesting since it'll contain an error
    return '[latex error, code follows]<pre>'.$totex.'</pre><p><b>Log file:</b><pre>'.$log.'</pre></p> '.phplatex_cleantmp($tfn,$heredir);
  }
  
  
  # DVI -> PostScript.   Since dvips uses lpr, which may be configured to actually print by default, force writing to a file with -o
  exec($path_to_dvips.' '.$tfn.'.dvi -o '.$tfn.'.ps');
  if ( !file_exists($tfn.'.ps'))  {
    return '[dvi2ps error] '.phplatex_cleantmp($tfn,$heredir);
  }
  
  
  # PostScript -> image.  Also trim based on corner pixel and set transparent color.
  $convert_cmd = $path_to_convert;
  if ($trans) {
    $convert_cmd .= ' -transparent-color "#'.$back.'" -transparent "#'.$back.'"';
  }
  $convert_cmd .= ' -colorspace RGB -density '.$dpi.' -trim +page '.$tfn.'.ps '.$tfn.'.'.$imgfmt;
  
  exec($convert_cmd);  
  #Note: +page OR -page +0+0 OR +repage moves the image to the cropped area (kills offset)
  #Older code tried: exec('/usr/bin/mogrify -density 90 -trim +page -format $imgfmt '.$tfn.'.ps');
  # It seems some versions of convert may not have -trim. Old versions?
  
  if (!file_exists($tfn.'.'.$imgfmt))  {
    return '[image convert error] '.phplatex_cleantmp($tfn,$heredir);
  }
  
  # Copy result image to chache.
  copy($tfn.'.'.$imgfmt, $heredir.'/images/'.$hashfn.'.'.$imgfmt);
  
  # Clean up temporary files, and return link to just-created image
  return phplatex_cleantmp($tfn,$heredir).'<img style="'.$verticalalign.'" title="'.$stralt.'" alt="LaTeX formula: '.$stralt.'" src="images/'.$hashfn.'.'.$imgfmt.'">';
} 
?>
