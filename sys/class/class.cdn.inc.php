<?php



class cdn extends db_connect
{
    private $ftp_url = "";
    private $ftp_server = "";
    private $ftp_user_name = "";
    private $ftp_user_pass = "";
    private $cdn_server = "";
    private $conn_id = false;

    public function __construct($dbo = NULL)
    {
        if (strlen($this->ftp_server) > 0) {

            $this->conn_id = @ftp_connect($this->ftp_server);
        }

        parent::__construct($dbo);
    }

    public function upload($file, $remote_file)
    {
        $remote_file = $this->cdn_server.$remote_file;

        if ($this->conn_id) {

            if (@ftp_login($this->conn_id, $this->ftp_user_name, $this->ftp_user_pass)) {

                // upload a file
                if (@ftp_put($this->conn_id, $remote_file, $file, FTP_BINARY)) {

                    return true;

                } else {

                    return false;
                }
            }
        }
    }

    public function uploadMyImage($imgFilename)
    {
        rename($imgFilename, MY_PHOTOS_PATH.basename($imgFilename));

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "fileUrl" => APP_URL."/".MY_PHOTOS_PATH.basename($imgFilename));

        return $result;
    }

    public function uploadPhoto($imgFilename)
    {
        rename($imgFilename, PROFILE_PHOTO_PATH.basename($imgFilename));

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "fileUrl" => APP_URL."/".PROFILE_PHOTO_PATH.basename($imgFilename));

        return $result;
    }


    public function uploadChatImg($imgFilename)
    {
        rename($imgFilename, CHAT_IMAGE_PATH.basename($imgFilename));

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "fileUrl" => APP_URL."/".CHAT_IMAGE_PATH.basename($imgFilename));

        return $result;
    }
    
}
