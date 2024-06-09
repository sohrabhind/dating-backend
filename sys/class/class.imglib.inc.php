<?php



class imglib extends db_connect
{
    private $profile_id = 0;
    private $request_from = 0;

    public function __construct($dbo = null, $profile_id = 0)
    {
        parent::__construct($dbo);
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
            if($w > 800) {
                $this->img_resize($new_file_name, $temp_file_name, 800, 0);
            } else {
                $this->img_resize($new_file_name, $temp_file_name, $w, 0);    
            }
        } else {
            unlink($temp_file_name);
            return $result;
        }

        $cdn = new cdn($this->db);
        $response = array();
        $response = $cdn->uploadChatImg($temp_file_name);
        if ($response['error'] === false) {
            $result['error'] = false;
            $result['imageUrl'] = $response['fileUrl'];
        }
        
        @unlink($temp_file_name);
        return $result;
    }


    public function newProfilePhoto($new_file_name, $temp_file_name)
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
            if($w > 800) {
                $this->img_resize($new_file_name, $temp_file_name, 800, 0);
            } else {
                $this->img_resize($new_file_name, $temp_file_name, $w, 0);    
            }
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

        if ($type == IMAGETYPE_JPEG || $type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
            if($w > 800) {
                $this->img_resize($new_file_name, $temp_file_name, 800, 0);
            } else {
                $this->img_resize($new_file_name, $temp_file_name, $w, 0);    
            }
        } else {
            unlink($temp_file_name);
            return $result;
        }

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

        $new_width   = $use_x_ratio ? $width : round($size[0] * $ratio);
        $new_height  = !$use_x_ratio ? $height : round($size[1] * $ratio);
        $new_left    = $use_x_ratio ? 0 : round(($width - $new_width)   / 2);
        $new_top     = !$use_x_ratio ? 0 : round(($height - $new_height) / 2);

        if ($size[0] < $new_width && $size[1] < $new_height) {
            $width = $new_width = round($size[0]);
            $height = $new_height = round($size[1]);
        }

        $isrc  = $icfunc($src);
        $idest = imagecreatetruecolor(round($width), round($height));

        imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0, $new_width, $new_height, $size[0], $size[1]);
        imagejpeg($idest, $dest, $quality);
        imagedestroy($isrc);
        imagedestroy($idest);
        return true;
    }
}
