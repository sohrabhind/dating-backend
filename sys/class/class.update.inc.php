<?php



class update extends db_connect
{
    public function __construct($dbo = NULL)
    {
        parent::__construct($dbo);

        // off all pdo errors when column in table exists

        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    }

}
