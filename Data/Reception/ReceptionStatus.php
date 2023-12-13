<?php
/**
 * Created by Vincent
 * Email: vincent@pixodeo.net
 */

namespace WMS\Data\Reception;

use WMS\Contracts\AbstractObjectData;


/**
 * @property string _MetaId_
 * @property string Available
 * @property int Free
 * @property int Storage
 * @property string Description
 * @property string Id
 */
class ReceptionStatus extends AbstractObjectData
{
    const VALUES = [
        0 => "en Attente", //on hold
        1 => "Planifié (en cours réception/préparation )", //Planned (currently receiving/preparing)
        2 => "Validée", //Validated
        3 => "Intermédiaire", //Intermediate
        5 => "à Quai", //at the Quay
        6 => "Supprimée", //Deleted
        7 => "Réception RF en cours" // RF reception in progress
    ];

}