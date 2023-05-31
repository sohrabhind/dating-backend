<?php

/*!
 * ifsoft.co.uk
 *
 * http://ifsoft.com.ua, https://ifsoft.co.uk, https://hindbyte.com
 * hindbyte@gmail.com
 *
 * Copyright 2012-2020 Demyanchuk Dmitry (hindbyte@gmail.com)
 */

class spam extends db_connect
{
	private $requestFrom = 0;
    private $language = 'en';

	public function __construct($dbo = NULL)
    {
		parent::__construct($dbo);
	}

	// Get created chats count for last 30 minutes

    public function getChatsCount()
    {
        $testTime = time() - 1800; // 30 minutes

        $stmt = $this->db->prepare("SELECT count(*) FROM chats WHERE fromUserId = (:profileId) AND removeAt = 0 AND createAt > (:testTime)");
        $stmt->bindParam(":profileId", $this->requestFrom, PDO::PARAM_INT);
        $stmt->bindParam(":testTime", $testTime, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }


    // Get created comments count for last 30 minutes

    public function getCommentsCount()
    {
        $testTime = time() - 1800; // 30 minutes

        $stmt = $this->db->prepare("SELECT count(*) FROM images_comments WHERE fromUserId = (:profileId) AND removeAt = 0 AND createAt > (:testTime)");
        $stmt->bindParam(":profileId", $this->requestFrom, PDO::PARAM_INT);
        $stmt->bindParam(":testTime", $testTime, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }


    // Get user like gallery items count for last 30 minutes

    public function getGalleryLikesCount()
    {
        $testTime = time() - 1800; // 30 minutes

        $stmt = $this->db->prepare("SELECT count(*) FROM images_likes WHERE fromUserId = (:profileId) AND removeAt = 0 AND createAt > (:testTime)");
        $stmt->bindParam(":profileId", $this->requestFrom, PDO::PARAM_INT);
        $stmt->bindParam(":testTime", $testTime, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function setLanguage($language)
    {
        $this->language = $language;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setRequestFrom($requestFrom)
    {
        $this->requestFrom = $requestFrom;
    }

    public function getRequestFrom()
    {
        return $this->requestFrom;
    }
}
