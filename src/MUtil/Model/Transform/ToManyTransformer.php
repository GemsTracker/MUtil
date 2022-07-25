<?php

namespace MUtil\Model\Transform;

class ToManyTransformer extends \MUtil\Model\Transform\NestedTransformer
{
    protected $savable;

    public function __construct($savable = false)
    {
        $this->savable = $savable;
    }

    public function transformFilterSubModel(\MUtil\Model\ModelAbstract $model, \MUtil\Model\ModelAbstract $sub,
                                            array $filter, array $join)
    {
        $itemNames = $sub->getItemNames();
        $subFilter = array_intersect(array_keys($filter), $itemNames);

        $child = reset($join);
        $parent = key($join);

        if (isset($filter[\MUtil\Model::TEXT_FILTER])) {
            $subFilter += $sub->getTextSearchFilter($filter[\MUtil\Model::TEXT_FILTER]);
            $mainFilter = $model->getTextSearchFilter($filter[\Mutil_model::TEXT_FILTER]);
            
        }

        if (count($subFilter)) {
            $results = $sub->load($subFilter);
            if ($results) {
                $subFilterValues = array_column($results, $child);
                $addFilter = ' OR ' . $parent . ' IN ('.join(',', $subFilterValues).')';
                unset($filter[\MUtil\Model::TEXT_FILTER]);
                foreach($mainFilter as $mainFilterSub) {
                    $filter[] = $mainFilterSub . $addFilter;
                }
            }
        }


        return $filter;
    }

    /**
     * Function to allow overruling of transform for certain models
     *
     * @param \MUtil\Model\ModelAbstract $model Parent model
     * @param \MUtil\Model\ModelAbstract $sub Sub model
     * @param array $data The nested data rows
     * @param array $join The join array
     * @param string $name Name of sub model
     * @param boolean $new True when loading a new item
     * @param boolean $isPostData With post data, unselected multiOptions values are not set so should be added
     */
    protected function transformLoadSubModel(
        \MUtil\Model\ModelAbstract $model, \MUtil\Model\ModelAbstract $sub, array &$data, array $join,
        $name, $new, $isPostData)
    {
        $child = reset($join);
        $parent = key($join);

        $filter = [];
        $parentIds = array_column($data, $parent);

        foreach ($data as $key => $row) {

            $rows = null;
            // E.g. if loaded from a post
            if (isset($row[$name])) {
                $rows = $sub->processAfterLoad($row[$name], $new, $isPostData);
                unset($parentIds[$key]);
            } elseif ($new) {
                $rows = $sub->loadAllNew();
                unset($parentIds[$key]);
            }

            if ($rows !== null && isset($rows[$child])) {
                $data[$key][$name] = $rows[$child];
            }
        }

        $parentIndexes = array_flip($parentIds);
        $filter[$child] = $parentIds;

        $combinedResult = $sub->load($filter);

        foreach($combinedResult as $key => $result) {
            if (isset($result[$child]) && isset($parentIndexes[$result[$child]])) {
                $data[$parentIndexes[$result[$child]]][$name][] = $result;
            }
        }
    }

    /**
     * Function to allow overruling of transform for certain models
     *
     * @param \MUtil\Model\ModelAbstract $model
     * @param \MUtil\Model\ModelAbstract $sub
     * @param array $data
     * @param array $join
     * @param string $name
     */
    protected function transformSaveSubModel(
        \MUtil\Model\ModelAbstract $model, \MUtil\Model\ModelAbstract $sub, array &$row, array $join, $name)
    {
        if (!$this->savable) {
            return;
        }

        if (! isset($row[$name])) {
            return;
        }

        $data = $row[$name];

        $child = reset($join);
        $parent = key($join);

        $parentId = $row[$parent];
        $filter = [$child => $parentId];
        $oldResults = $sub->load($filter);

        $newResults = [];
        $insertResults = [];
        $deletedResults = [];

        $keys = $sub->getKeys();
        $key = reset($keys);

        $dataKeys = array_column($data, $key);

        foreach($oldResults as $oldResult) {
            $index = array_search($oldResult[$key], $dataKeys);
            if ($index !== false) {
                $saveRows[] = $oldResult;
                unset($data[$index]);
            } else {
                $deletedResults[] = $oldResult;
            }
        }

        foreach($data as $newValue) {
            $saveRows[] = [
                $child => $parentId,
                $this->singleColumn => $newValue,
            ];
        }

        $newResults = [];
        if (!empty($saveRows)) {
            $newResults = $sub->saveAll($saveRows);
        }

        $deleteIds = array_column($deletedResults, $key);
        if (!empty($deleteIds)) {
            $sub->delete([$key => $deleteIds]);
        }

        $row[$name] = $newResults;
    }
}
