<?php

namespace Models;

use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;

/**
 * Class FavoritesTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> USER_ID int mandatory
 * <li> ITEM_ID int mandatory
 * </ul>
 *
 * @package Bitrix\Favorites
 **/
class FavoritesTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'favorites_table';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            new IntegerField(
                'ID',
                [
                    'primary' => true,
                    'autocomplete' => true,
                    'title' => Loc::getMessage('FAVORITES_ENTITY_ID_FIELD'),
                ]
            ),
            new IntegerField(
                'ITEM_ID',
                [
                    'title' => Loc::getMessage('FAVORITES_ENTITY_JKKP_ID_FIELD'),
                ]
            ),

            new StringField(
                'USER_ID',
                [
                    'required' => true,
                    'title' => Loc::getMessage('FAVORITES_ENTITY_USER_ID_FIELD'),
                ]
            ),

        ];
    }
}
