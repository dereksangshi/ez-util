<?php

namespace Ez\Util;

/**
 * Class UrlParam
 * @package Ez\Util
 * @author Derek Li
 */
class UrlParam
{
    /**
     * The delimiter used in the url to represent array values.
     *
     */
    const URL_DELIMITER = '|';

    /**
     * Encode URL param from given filter attributes.
     * The encoded URL param is mainly used by paging.
     *
     * @param array $attrs : array containing filter attributes with values.
     * @param null|string $name : given paramName for param.
     * @return string $param: URL param string.
     */
    public static function encode(array $attrs, $name = null)
    {
        $param = '';
        if (isset($name)) {
            $param .= "&" . $name . "=" . urlencode(self::serialize($attrs));
        } else {
            foreach ($attrs as $k => $v) {
                if (is_array($v)) {
                    $param .= "&" . self::encode($v, $k);
                } else {
                    $param .= "&" . $k . "=" . urlencode($v);
                }
            }
        }
        return substr($param, 1);
    }

    /**
     * Decode url param.
     * It receives an array like $_REQUEST (not a string) as param. If a value contains delimiter defined, unserialize the value into an array.
     *
     * @param array $get : the array to decode.
     * @return array $param: the decoded array.
     */
    public static function decode(array $get)
    {
        $param = array();
        foreach ($get as $k => $v) {
            if (is_array($v)) {
                $param[$k] = self::decode($v);
            } else {
                $param[$k] = urldecode(self::unserialize($v));
            }
        }
        return $param;
    }

    /**
     * Serialize an array using given delimiter.
     *
     * @param array $arr : the array to serialize.
     * @param string $delimiter : the delimiter to use.
     * @return string: serialized string.
     */
    public static function serialize(array $arr, $delimiter = self::URL_DELIMITER)
    {
        return implode($delimiter, $arr);
    }

    /**
     * Unserialize a string by given delimiter.
     *
     * @param string $str : the string to unserialize.
     * @param string @delimiter: the delimiter to use.
     * @return array: the unserialized array.
     */
    public static function unserialize($str, $delimiter = self::URL_DELIMITER)
    {
        // strpos is faster than strstr
        if (strpos($str, $delimiter) === false) {
            return array($str);
        }
        return explode($delimiter, trim($str));
    }
}