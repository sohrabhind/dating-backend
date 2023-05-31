<?php

/*!
 * ifsoft.co.uk
 *
 * http://ifsoft.com.ua, https://ifsoft.co.uk, https://hindbyte.com
 * hindbyte@gmail.com
 *
 * Copyright 2012-2020 Demyanchuk Dmitry (hindbyte@gmail.com)
 */

class imglib extends db_connect
{
    private $profile_id = 0;
    private $request_from = 0;

    public function __construct($dbo = null, $profile_id = 0)
    {
        parent::__construct($dbo);
    }

    public function setCorrectImageOrientation($filename)
    {

        $imgInfo = getimagesize($filename);

        if ($imgInfo[2] == IMAGETYPE_JPEG) {

            if (function_exists('exif_read_data')) {

                $exif = @exif_read_data($filename);

                if ($exif && isset($exif['Orientation'])) {

                    $orientation = $exif['Orientation'];

                    if (!empty($orientation)) {

                        $img = imagecreatefromjpeg($filename);

                        switch ($orientation) {

                            case 3:

                                $img = imagerotate($img, 180, 0);

                                break;

                            case 6:

                                $img = imagerotate($img, -90, 0);

                                break;

                            case 8:

                                $img = imagerotate($img, 90, 0);

                                break;
                        }

                        imagejpeg($img, $filename, 100); // rewrite rotated image back to $filename
                        imagedestroy($img);
                    }
                }
            }
        }
    }


    public function createChatImg($new_file_name, $temp_file_name)
    {
        $result = array("error" => true);

        list($w, $h, $type) = getimagesize($new_file_name);
        if ($w < 1 || $h < 1) {
            unlink($new_file_name);
            return $result;
        }
    
        if (rename($new_file_name, $temp_file_name)) {
            $new_file_name = $temp_file_name;
        } else {
            unlink($new_file_name);
            return $result;
        }

        if ($type == IMAGETYPE_JPEG || $type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
            $this->img_resize($new_file_name, $temp_file_name, 800, 0);
        } else {
            unlink($temp_file_name);
            return $result;
        }

        $cdn = new cdn($this->db);
        $response = array();
        $response = $cdn->uploadChatImg($temp_file_name);
        if ($response['error'] === false) {
            $result['error'] = false;
            $result['imgUrl'] = $response['fileUrl'];
        }
        
        @unlink($temp_file_name);
        return $result;
    }


    public function newProfilePhoto($new_file_name, $temp_file_name)
    {
        $result = array("error" => true);

        $this->setCorrectImageOrientation($new_file_name);

        list($w, $h, $type) = getimagesize($new_file_name);
        if ($w < 1 || $h < 1) {
            unlink($new_file_name);
            return $result;
        }
    
        if (rename($new_file_name, $temp_file_name)) {
            $new_file_name = $temp_file_name;
        } else {
            unlink($new_file_name);
            return $result;
        }

        if ($type == IMAGETYPE_JPEG) {
            $photo = new photo($this->db, $new_file_name, 512);
            imagejpeg($photo->getImgData(), $temp_file_name, 80);
            unset($photo);
        } elseif ($type == IMAGETYPE_PNG) {
            //PNG
            $photo = new photo($this->db, $new_file_name, 512);
            imagepng($photo->getImgData(), $temp_file_name, 80);
            unset($photo);
        } else {
            unlink($temp_file_name);
            return $result;
        }

        $cdn = new cdn($this->db);
        $response = array();
        $response = $cdn->uploadPhoto($temp_file_name);
        if ($response['error'] === false) {
            $result['error'] = false;
            $result['bigPhotoUrl'] = $response['fileUrl'];
        }

        @unlink($temp_file_name);
        return $result;
    }


    public function createMyImage($new_file_name, $temp_file_name)
    {
        $result = array("error" => true);

        list($w, $h, $type) = getimagesize($new_file_name);
        if ($w < 1 || $h < 1) {
            unlink($new_file_name);
            return $result;
        }

        if (rename($new_file_name, $temp_file_name)) {
            $new_file_name = $temp_file_name;
        } else {
            unlink($new_file_name);
            return $result;
        }

        $this->img_resize($new_file_name, $temp_file_name, 800, 0);

        $cdn = new cdn($this->db);
        $response = array();
        $response = $cdn->uploadMyImage($temp_file_name);
        if ($response['error'] === false) {
            $result['error'] = false;
            $result['normalImageUrl'] = $response['fileUrl'];
        }

        @unlink($temp_file_name);
        return $result;
    }


    public function img_resize($src, $dest, $width, $height, $rgb = 0xFFFFFF, $quality = 80) {

        if (!file_exists($src)) {
            return false;
        }

        $size = getimagesize($src);

        if ($size === false) {
            return false;
        }

        $format = strtolower(substr($size['mime'], strpos($size['mime'], '/') + 1));
        $icfunc = 'imagecreatefrom'.$format;

        if (!function_exists($icfunc)) {
            return false;
        }

        $x_ratio = $width  / $size[0];
        $y_ratio = $height / $size[1];

        if ($height == 0) {

            $y_ratio = $x_ratio;
            $height  = $y_ratio * $size[1];

        } elseif ($width == 0) {

            $x_ratio = $y_ratio;
            $width   = $x_ratio * $size[0];
        }

        $ratio       = min($x_ratio, $y_ratio);
        $use_x_ratio = ($x_ratio == $ratio);

        $new_width   = $use_x_ratio ? $width : floor($size[0] * $ratio);
        $new_height  = !$use_x_ratio ? $height : floor($size[1] * $ratio);
        $new_left    = $use_x_ratio ? 0 : floor(($width - $new_width)   / 2);
        $new_top     = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);

        // если не нужно увеличивать маленькую картинку до указанного размера
        if ($size[0] < $new_width && $size[1] < $new_height) {

            $width = $new_width = $size[0];
            $height = $new_height = $size[1];
        }

        $isrc  = $icfunc($src);
        $idest = imagecreatetruecolor($width, $height);

        imagefill($idest, 0, 0, $rgb);
        imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0, $new_width, $new_height, $size[0], $size[1]);

        $i = strrpos($dest, '.');
        if (!$i) {
            return '';
        }
        $l = strlen($dest) - $i;
        $ext = substr($dest, $i+1, $l);

        switch ($ext) {

            case 'jpeg':
            case 'jpg':
                imagejpeg($idest, $dest, $quality);
                break;
            case 'gif':
                imagegif($idest, $dest);
                break;
            case 'png':
                imagepng($idest, $dest);
                break;
        }

        imagedestroy($isrc);
        imagedestroy($idest);
        return true;
    }
}
