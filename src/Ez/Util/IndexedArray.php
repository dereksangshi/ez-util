<?php

namespace Ez\Util;

use Ez\Util\Exception\IndexNotExistException;

/**
 * Class IndexedArray
 *
 * @package Ez\Util
 * @author Derek Li
 */
class IndexedArray
{
    /**
     * @var string
     */
    protected $delimiter = '/';

    /**
     * @var array
     */
    protected $array = array();

    /**
     * Each array can be passed through as many as needed
     * and will be recursively merged together.
     *
     */
    public function __construct()
    {
        // Take over whatever array passed and merge them in sequence.
        $args = func_get_args();
        if (count($args) > 0) {
            foreach ($args as $a) {
                if (is_array($a)) {
                    $this->array = $this->deepMerge($this->array, $a);
                }
            }
        }
    }

    /**
     * Recursively merge array1 with array 2 in a distinct way.
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    protected function deepMerge(array $array1, array $array2)
    {
        $merged = $array1;
        foreach ($array2 as $key => $value) {
            if (is_array($value) &&
                isset($merged[$key]) &&
                is_array($merged[$key])
            ) {
                $merged[$key] = $this->deepMerge($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }
        return $merged;
    }

    /**
     * @param string $delimiter
     * @return $this
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
        return $this;
    }

    /**
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * @param array $array
     * @return $this
     */
    public function addArray(array $array)
    {
        $this->array = $this->deepMerge($this->array, $array);
        return $this;
    }

    /**
     * @return array
     */
    public function getArray()
    {
        return $this->array;
    }

    /**
     * Break the index into keys.
     * e.g. 'Foo/Bar' => array('Foo', 'Bar').
     *
     * @param string $index Index (in text) to convert (e.g. 'Foo/SubFoo').
     * @return array Array of nodes (an empty array will be returned if the index is empty or empty-like).
     */
    protected function indexToKeys($index)
    {
        $index = trim(trim($index), $this->getDelimiter());
        // Return an empty array if the index is empty ('') or empty-like ('/','-',...).
        if (empty($index)) {
            return array();
        }
        return explode($this->getDelimiter(), $index);
    }

    /**
     * Glue the keys into an index.
     * e.g. array('Foo', 'Bar') => 'Foo/Bar'.
     *
     * @param array $keys The keys to glue.
     * @return string
     */
    protected function keysToIndex(array $keys)
    {
        if (count($keys) === 0) {
            return '';
        }
        return implode($this->getDelimiter(), $keys);
    }

    /**
     * Recursive method to get the value (or a sub tree).
     *
     * @param array $array Value(s) of the tree (e.g. array('node12'=>array(....), 'node22'=>'val',...)).
     * @param array $keys Index(s) (in array) in the tree. Could be an array, e.g. array('category','subcategory',...), or a string 'category', or null.
     * @param array $index Current index used.
     * @return boolean|mixed False if the node does not exist in the tree, or the value of the node in the true.
     * @throws IndexNotExistException
     */
    protected function iterate(array $array, array $keys, $index)
    {
        $currentKey = array_shift($keys);
        if (is_array($keys) && count($keys) > 0) {
            if (!is_array($array[$currentKey])) {
                throw new IndexNotExistException($index);
            }
            return $this->iterate($array[$currentKey], $keys, $index);
        } else {
            if (!array_key_exists($currentKey, $array)) {
                throw new IndexNotExistException($index);
            }
            return $array[$currentKey];
        }
    }

    /**
     * Get value based on a certain index.
     *
     * @param string $index Index to check.
     * @return mixed
     */
    public function get($index)
    {
        $keys = $this->indexToKeys($index, $this->getDelimiter());
        if (count($keys) === 0) {
            return $this->array;
        }
        return $this->iterate($this->array, $keys, $index);
    }
}