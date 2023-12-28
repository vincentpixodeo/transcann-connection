<?php

require __DIR__ . '/../../../master.inc.php';
include_once __DIR__ . '/autoloader.php';
/** @var DoliDBMysqli $db */
if ($result = $db->query('SELECT * FROM llx_commande')) {
    while ($data = $db->fetch_object($result)) {
        (new \WMS\Xtent\DolibarrConvert\SaleOrder((array)$data))->save();
    }
} else {
    dd($db->lasterror());
}

