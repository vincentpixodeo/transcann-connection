<?php

require __DIR__ . '/../../../master.inc.php';
include_once __DIR__ . '/autoloader.php';


dd((new \WMS\Xtent\DolibarrConvert\Product())->fetch(4)->getMappingInstance()->save(['transcann_id' => 222222, 'transcann_client_id' => 2222222]));

/** @var DoliDBMysqli $db */
if ($result = $db->query('SELECT * FROM llx_entrepot')) {
    while ($data = $db->fetch_object($result)) {
        dump((new \WMS\Xtent\DolibarrConvert\Warehouse((array)$data))->save());
    }
} else {
    dd($db->lasterror());
}

