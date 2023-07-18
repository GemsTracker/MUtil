<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Model
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Model;

use Zalt\Model\MetaModelInterface;

/**
 * Generic model for data storage that does not come with it's own
 * storage engine; e.g. text/xml files, directories, session arrays.
 *
 * The basics are: create an iterable item to walk through the content
 * and then filer / sort that content one row at the time.
 *
 * @package    MUtil
 * @subpackage Model
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
abstract class ArrayModelAbstract extends \MUtil\Model\ModelAbstract
{
    /**
     * When set to true in a subclass, then the model should be able to
     * save itself.
     *
     * @var boolean
     */
    protected $_saveable = false;

    /**
     * The sort for the current load.
     *
     * @var array fieldname => SORT_ASC | SORT_DESC
     */
    private $_sorts;

    /**
     * Returns true if the passed row passed through the filter
     *
     * @param array $row A row of data
     * @param array $filters An array of filter statements
     * @param boolean $logicalAnd When true this is an AND filter, otherwise OR (switches at each array nesting level)
     * @return boolean
     */
    protected function _applyFiltersToRow(array $row, array $filters, $logicalAnd)
    {
        foreach ($filters as $name => $value) {
            if (is_callable($value)) {
                if (is_numeric($name)) {
                    $val = $row;
                } else {
                    $val = $row[$name] ?? null;
                }
                $result = call_user_func($value, $val);

            } elseif (is_array($value)) {
                $subFilter = true;
                if (1 == count($value)) {
                    if (isset($value[MetaModelInterface::FILTER_CONTAINS])) {
                        $result = str_contains($row[$name], $value[MetaModelInterface::FILTER_CONTAINS]);
                        $subFilter = false;
                    } elseif (isset($value[MetaModelInterface::FILTER_CONTAINS_NOT])) {
                        $result = ! str_contains($row[$name], $value[MetaModelInterface::FILTER_CONTAINS_NOT]);
                        $subFilter = false;
                    }
                } elseif (2 == count($value)) {
                    if (isset($value[MetaModelInterface::FILTER_BETWEEN_MAX], $value[MetaModelInterface::FILTER_BETWEEN_MIN])) {
                        $result = ($row[$name] >= $value[MetaModelInterface::FILTER_BETWEEN_MIN]) && ($row[$name] <= $value[MetaModelInterface::FILTER_BETWEEN_MAX]);
                        $subFilter = false;
                    }
                }
                if ($subFilter) {
                    if (is_numeric($name)) {
                        $result = $this->_applyFiltersToRow($row, $value, !$logicalAnd);
                    } elseif (MetaModelInterface::FILTER_NOT == $name) {
                        // Check here as NOT can be part of the main filter
                        $result = ! $this->_applyFiltersToRow($row, $value, ! $logicalAnd);
                    } else {
                        $rowVal = $row[$name] ?? null;
                        $result = false;
                        foreach ($value as $filterVal) {
                            if ($rowVal == $filterVal) {
                                $result = true;
                                break;
                            }
                        }
                    }
                }

            } else {
                if (is_numeric($name)) {
                    // Allow literal value interpretation
                    $result = (boolean) $value;
                } else {
                    $val = isset($row[$name]) ? $row[$name] : null;

                    if (is_string($value) || is_string($val)) {
                        $result = ($val === $value) || (0 === strcasecmp((string) $value, (string) $val));
                    } else {
                        $result = ($val === $value);
                    }
                }
                // \MUtil\EchoOut\EchoOut::r($value . '===' . $value . '=' . $result);
            }

            if ($logicalAnd xor $result) {
                return $result;
            }
        }

        // If $logicalAnd is true:
        //   => all filters must have triggered true to arrive here
        //   => the result is true,
        // If $logicalAnd is false:
        //   => all filters must have triggered false to arrive here
        //   => the result is false.
        return $logicalAnd;
    }

    /**
     * Filters the data array using a model filter
     *
     * @param \Traversable $data
     * @param array $filters
     * @return \Traversable
     */
    protected function _filterData($data, array $filters)
    {
        if ($data instanceof \IteratorAggregate) {
            $data = $data->getIterator();
        }

        // If nothing to filter
        if (! $filters) {
            return $data;
        }

        if ($data instanceof \Iterator) {
            return new \MUtil\Model\Iterator\ArrayModelFilterIterator($data, $this, $filters);
        }

        $filteredData = array();
        foreach ($data as $key => $row) {
            if ($this->_applyFiltersToRow($row, $filters, true)) {
                // print_r($row);
                $filteredData[$key] = $row;
            }
        }

        return $filteredData;
    }

    /**
     * Returns a nested array containing the items requested.
     *
     * @param array $filter Filter array, num keys contain fixed expresions, text keys are equal or one of filters
     * @param array $sort Sort array field name => sort type
     * @return array Nested array or false
     */
    protected function _load(array $filter, array $sort)
    {
        $data = $this->_loadAllTraversable();

        if ($filter) {
            $data = $this->_filterData($data, $filter);
        }

        if (! is_array($data)) {
            $data = iterator_to_array($data);
        }

        if ($sort) {
            $data = $this->_sortData($data, $sort);
        }

        return $data;
    }

    /**
     * An ArrayModel assumes that (usually) all data needs to be loaded before any load
     * action, this is done using the iterator returned by this function.
     *
     * @return \Traversable Return an iterator over or an array of all the rows in this object
     */
    abstract protected function _loadAllTraversable();


    /**
     * Save a single model item.
     *
     * @param array $newValues The values to store for a single model item.
     * @param array $filter If the filter contains old key values these are used
     * to decide on update versus insert.
     * @return array The values as they are after saving (they may change).
     */
    protected function _save(array $newValues, array $filter = null)
    {
        if ($this->_saveable) {
            $data = $this->_loadAllTraversable();
            if ($data instanceof \Traversable) {
                $data = iterator_to_array($this->_loadAllTraversable());
            }

            if ($keys = $this->getKeys()) {
                $search = array();
                if (is_array($filter)) {
                    $newValues = $newValues + $filter;
                }

                foreach ($keys as $key) {
                    if (isset($newValues[$key])) {
                        $search[$key] = $newValues[$key];
                    } else {
                        // Crude but hey
                        throw new \MUtil\Model\ModelException(sprintf('Key value "%s" missing when saving data.', $key));
                    }
                }

                $rowId = \MUtil\Ra::findKeys($data, $search);

                if ($rowId) {
                    // Overwrite to new values
                    $data[$rowId] = $newValues + $data[$rowId];
                } else {
                    $data[] = $newValues;
                }


            } else {
                $data[] = $newValues;
            }

            $this->_saveAllTraversable($data);

            return $newValues;
        } else {
            throw new \MUtil\Model\ModelException(sprintf('Save not implemented for model "%s".', $this->getName()));
        }
    }

    /**
     * When $this->_saveable is true a child class should either override the
     * delete() and save() functions of this class or override _saveAllTraversable().
     *
     * In the latter case this class will use _loadAllTraversable() and remove / add the
     * data to the data in the delete() / save() functions and pass that data on to this
     * function.
     *
     * @param array $data An array containing all the data that should be in this object
     * @return void
     */
    protected function _saveAllTraversable(array $data)
    {
        throw new \MUtil\Model\ModelException(
                sprintf('Function "%s" should be overriden for class "%s".', __FUNCTION__, __CLASS__)
                );
    }

    /**
     * Sorts the output
     *
     * @param array $data
     * @param mixed $sorts
     * @return array
     */
    protected function _sortData(array $data, $sorts)
    {
        $this->_sorts = array();

        foreach ($sorts as $key => $order) {
            if (is_numeric($key) || is_string($order)) {
                if (strtoupper(substr($order,  -5)) == ' DESC') {
                    $order     = substr($order,  0,  -5);
                    $direction = SORT_DESC;
                } else {
                    if (strtoupper(substr($order,  -4)) == ' ASC') {
                        $order = substr($order,  0,  -4);
                    }
                    $direction = SORT_ASC;
                }
                $this->_sorts[$order] = $direction;

            } else {
                switch ($order) {
                    case SORT_DESC:
                        $this->_sorts[$key] = SORT_DESC;
                        break;

                    case SORT_ASC:
                    default:
                        $this->_sorts[$key] = SORT_ASC;
                        break;
                }
            }
        }

        usort($data, array($this, 'sortCmp'));

        return $data;
    }

    /**
     * Returns true if the passed row passed through the filter
     *
     * @param array $row A row of data
     * @param array $filters An array of filter statements
     * @return boolean
     */
    public function applyFiltersToRow(array $row, array $filters)
    {
        return $this->_applyFiltersToRow($row, $filters, true);
    }

    /**
     * Delete items from the model
     *
     * @param mixed $filter True to use the stored filter, array to specify a different filter
     * @return int The number of items deleted
     */
    public function delete($filter = null): int
    {
        if ($this->_saveable) {
            // TODO: implement
        } else {
            throw new \MUtil\Model\ModelException(sprintf('Delete not implemented for model "%s".', $this->getName()));
        }
    }

    /**
     * Creates a filter for this model for the given wildcard search text.
     *
     * @param string $searchText
     * @return array An array of filter statements for wildcard text searching for this model type
     */
    public function getTextSearchFilter($searchText)
    {
        $filter = array();

        if ($searchText) {
            $fields = array();
            foreach ($this->getItemNames() as $name) {
                // TODO: multiOptions integratie
                if ($this->get($name, 'label')) {
                    $fields[] = $name;
                }
            }

            if ($fields) {
                foreach ($this->getTextSearches($searchText) as $searchOn) {
                    $textFilter = array();

                    // Almost always use, this allows reuse
                    $textFunction = function ($value) use ($searchOn) {
                        // \MUtil\EchoOut\EchoOut::track($value . ' - ' . $searchOn . ' = ' . \MUtil\StringUtil\StringUtil::contains($value, $searchOn));
                        return \MUtil\StringUtil\StringUtil::contains($value, $searchOn, true);
                    };

                    foreach ($fields as $name) {
                        if ($options = $this->get($name, 'multiOptions')) {
                            $items = array();
                            foreach ($options as $value => $label) {
                                if (\MUtil\StringUtil\StringUtil::contains($label, $searchOn)) {
                                    $items[$value] = $value;
                                }
                            }
                            if ($items) {
                                if (count($items) == count($options)) {
                                    // This filter always returns true, do not add this filter
                                    // \MUtil\EchoOut\EchoOut::track('Always true');
                                    $textFilter = false;
                                    break;
                                }
                                // Function is different for each multiOptions
                                $textFilter[$name] = function ($value) use ($items) {
                                    return array_key_exists($value, $items);
                                };
                            }
                        } else {
                            $textFilter[$name] = $textFunction;
                        }
                    }
                    if ($textFilter) {
                        $filter[] = $textFilter;
                    }
                }
            }
        }

        return $filter;
    }

    /**
     * True if this model allows the creation of new model items.
     *
     * @return boolean
     */
    public function hasNew(): bool
    {
        // We assume this to be the case, unless the child model overrules this method.
        return $this->_saveable;
    }

    /**
     * True when the model supports general text filtering on all
     * labelled fields.
     *
     * This must be implemented by each sub model on it's own.
     *
     * @return boolean
     */
    public function hasTextSearchFilter(): bool
    {
        return true;
    }

    /**
     * Returns a \Traversable spewing out arrays containing the items requested.
     *
     * @param mixed $filter True to use the stored filter, array to specify a different filter
     * @param mixed $sort True to use the stored sort, array to specify a different sort
     * @return \Traversable
     */
    public function loadIterator($filter = null, $sort = null, $columns = null)
    {
        $data = $this->_loadAllTraversable();

        if ($data && $filter) {
            $data = $this->_filterData($data, $this->_checkFilterUsed($filter));
        }

        if ($this->_checkSortUsed($sort)) {
            throw new \MUtil\Model\ModelException("You cannot sort an array iterator.");
        }

        return $data;
    }

    /**
     * Sort function for sorting array on defined sort order
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    public function sortCmp(array $a, array $b)
    {
        foreach ($this->_sorts as $key => $direction) {
            if ($a[$key] !== $b[$key]) {
                // \MUtil\EchoOut\EchoOut::r($key . ': [' . $direction . ']' . $a[$key] . '-' . $b[$key]);
                if (SORT_ASC == $direction) {
                    return $a[$key] > $b[$key] ? 1 : -1;
                } else {
                    return $a[$key] > $b[$key] ? -1 : 1;
                }
            }
        }

        return 0;
    }
}
