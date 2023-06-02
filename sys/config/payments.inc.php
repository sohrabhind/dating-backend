<?php

/*!
 * ifsoft.co.uk
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk, https://hindbyte.com
 * hindbyte@gmail.com
 *
 * Copyright 2012-2019 Demyanchuk Dmitry (hindbyte@gmail.com)
 */

// amount must be in cents | $1 = 100 cents

$payments_packages = array();

$payments_packages[] = array(
    "id" => 0,
    "amount" => 300,
    "level" => 1,
    "name" => "Silver");

$payments_packages[] = array(
    "id" => 1,
    "amount" => 600,
    "level" => 2,
    "name" => "Gold");

$payments_packages[] = array(
    "id" => 2,
    "amount" => 900,
    "level" => 3,
    "name" => "Diamond");