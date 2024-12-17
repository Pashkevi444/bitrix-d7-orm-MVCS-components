<?php

/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2021 Bitrix
 */



use Bitrix\Main\ORM\Objectify\Collection;
use Bitrix\Main\ORM\Objectify\IdentityMap;
use Bitrix\Main\ORM\Query\Filter\ConditionTree;

/**
 * 
 * Class helps to handle Query with relations
 *
 * @package    bitrix
 * @subpackage main
 */
class QueryHelper
{
    /**
     * Decomposition for Queries with 1:N and N:M relations
     *
     * @param Query $query
     * @param bool $fairLimit Option to select only ID first, then other data
     * @param bool $separateRelations Option to separate 1:N and N:M relations
     * @return Collection
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function decompose($query, $fairLimit = true, $separateRelations = true, $runtime = [], $order = ['ID' => 'ASC'], int $cache = null)
    {
        $entity = $query->getEntity();
        $primaryNames = $entity->getPrimaryArray();
        $originalSelect = $query->getSelect();

        if ($fairLimit) {
            // select distinct primary
            $query->setSelect($entity->getPrimaryArray());
            $query->setDistinct();
            $query->setOrder($order);

            if ($cache){
                $query->setCacheTtl($cache);
                $query->cacheJoins('Y');
            }

            $rows = $query->fetchAll();

            // return empty result
            if (empty($rows)) {
                return $query->getEntity()->createCollection();
            }

            // reset query
            $query = $entity->getDataClass()::query();
            $query->setSelect($originalSelect);
            $query->setOrder($order);
            $query->where(static::getPrimaryFilter($primaryNames, $rows));
            if ($cache){
                $query->setCacheTtl($cache);
                $query->cacheJoins('Y');
            }
        }

        // more than one OneToMany or ManyToMany
        if ($separateRelations) {
            if($runtime) {
                foreach ($runtime as $item) {
                    $ref = ['=this.' . $item['REFERENCE'][0] => 'ref.' . $item['REFERENCE'][1]];
                    if ($item['REFERENCE'][2]) {
                        $ref['=ref.' . $item['REFERENCE'][2]] = new \Bitrix\Main\DB\SqlExpression($item['REFERENCE'][4] ?? '?i', $item['REFERENCE'][3]);
                    }
                    $query->registerRuntimeField(
                        $item['NAME'], [
                        'data_type' => trim($item['DATA_TYPE'], "'"),
                        'reference' => $ref,
                        'join_type' => $item['JOIN_TYPE']
                    ],
                    );
                }
            }

            $commonSelect = [];
            $dividedSelect = [];


            foreach ($originalSelect as $selectItem) {
                // init query with select item
                $selQuery = $entity->getDataClass()::query();
                if ($cache){
                    $selQuery->setCacheTtl($cache);
                    $selQuery->cacheJoins('Y');
                }
                if($runtime) {
                    foreach ($runtime as $item) {
                        $ref = ['=this.' . $item['REFERENCE'][0] => 'ref.' . $item['REFERENCE'][1]];
                        if ($item['REFERENCE'][2]) {
                            $ref['=ref.' . $item['REFERENCE'][2]] = new \Bitrix\Main\DB\SqlExpression($item['REFERENCE'][4] ?? '?i', $item['REFERENCE'][3]);
                        }
                        $selQuery->registerRuntimeField(
                            $item['NAME'], [
                            'data_type' => trim($item['DATA_TYPE'], "'"),
                            'reference' => $ref,
                            'join_type' => $item['JOIN_TYPE']
                        ],
                        );
                    }
                }
                $selQuery->addSelect($selectItem);
                $selQuery->getQuery(true);

                // check for relations
                foreach ($selQuery->getChains() as $chain) {
                    if ($chain->hasBackReference()) {
                        $dividedSelect[] = $selectItem;
                        continue 2;
                    }
                }

                $commonSelect[] = $selectItem;
            }

            if (empty($commonSelect)) {
                $commonSelect = $query->getEntity()->getPrimaryArray();
            }

            // common query
            $query->setSelect($commonSelect);

        }

        /** @var Collection $collection query data */
        $collection = $query->fetchCollection();

        if (!empty($dividedSelect) && $collection->count()) {
            // custom identity map & collect primaries
            $im = new IdentityMap;
            $primaryValues = [];

            foreach ($collection as $object) {
                $im->put($object);

                $primaryValues[] = $object->primary;
            }

            $primaryFilter = static::getPrimaryFilter($primaryNames, $primaryValues);

            // select relations
            foreach ($dividedSelect as $selectItem) {
                $result = $entity->getDataClass()::query()
                    ->addSelect($selectItem)
                    ->where($primaryFilter)
                    ->exec();

                $result->setIdentityMap($im);
                $result->fetchCollection();
            }
        }

        return $collection;
    }

    /**
     * @param array $primaryNames
     * @param array $primaryValues
     * @return ConditionTree
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function getPrimaryFilter($primaryNames, $primaryValues)
    {
        $commonSubFilter = new ConditionTree();

        if (count($primaryNames) === 1) {
            $values = [];

            foreach ($primaryValues as $row) {
                $values[] = $row[$primaryNames[0]];
            }

            $commonSubFilter->whereIn($primaryNames[0], $values);
        } else {
            $commonSubFilter->logic('or');

            foreach ($primaryValues as $row) {
                $primarySubFilter = new ConditionTree();

                foreach ($primaryNames as $primaryName) {
                    $primarySubFilter->where($primaryName, $row[$primaryName]);
                }
            }
        }

        return $commonSubFilter;
    }
}
