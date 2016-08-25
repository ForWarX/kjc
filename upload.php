<?php

function upload($file,$prefix,$suffix,$ext,$doc){
	
	$name = iconv('utf-8','gb2312',$prefix);
	if(!is_dir($doc . '/' . $name)){
		mkdir($doc . '/' . $name);
	}
	$saveto = $doc . "/$name/$name" . $suffix . ".$ext";
    move_uploaded_file($file['tmp_name'], $saveto);
    
	$typeok = TRUE;
    switch($file['type'])
    {
        case "image/gif": $src = imagecreatefromgif($saveto); break;
        case "image/jpeg": // Both regular and progressive jpegs
        case "image/pjpeg": $src = imagecreatefromjpeg($saveto); break;
        case "image/png": $src = imagecreatefrompng($saveto); break;
        default: $typeok = FALSE; break;
    }
    if ($typeok)
    {
        list($w, $h) = getimagesize($saveto);
        $max = 1000;
        $tw = $w;
        $th = $h;
        if ($w > $h && $max < $w)
        {
            $th = $max / $w * $h;
            $tw = $max;
        }
        elseif ($h > $w && $max < $h)
        {
            $tw = $max / $h * $w;
            $th = $max;
        }
        elseif ($max < $w)
        {
            $tw = $th = $max;
        }

        $tmp = imagecreatetruecolor($tw, $th);      
        imagecopyresampled($tmp, $src, 0, 0, 0, 0, $tw, $th, $w, $h);
        imagejpeg($tmp, $saveto);
        imagedestroy($tmp);
        imagedestroy($src);
    }
}
?>