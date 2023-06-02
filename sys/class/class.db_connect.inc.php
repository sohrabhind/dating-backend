<?php

/*!
 * https://hindbyte.com
 * hindbyte@gmail.com
 *
 * Copyright 2012-2021 Demyanchuk Dmitry (hindbyte@gmail.com)
 */


class db_connect {
    protected $db;
    protected function __construct($db = NULL) {
        if (is_object($db)) {

            $this->db = $db;

        }  else  {

            $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4;collation=utf8mb4_unicode_ci;";

            try  {
                
                $this->db = new PDO($dsn, DB_USER, DB_PASS);
                
            } catch (Exception $e) {

                die ($e->getMessage());
            }
        }
    }
}
