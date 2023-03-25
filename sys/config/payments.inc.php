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
    "credits" => 100,
    "name" => "100 credits");

$payments_packages[] = array(
    "id" => 1,
    "amount" => 600,
    "credits" => 300,
    "name" => "300 credits");

$payments_packages[] = array(
    "id" => 2,
    "amount" => 900,
    "credits" => 600,
    "name" => "600 credits");