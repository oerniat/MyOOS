<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/devtools/cloudtrace/v2/trace.proto

namespace Google\Cloud\Trace\V2;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Represents a string that might be shortened to a specified length.
 *
 * Generated from protobuf message <code>google.devtools.cloudtrace.v2.TruncatableString</code>
 */
class TruncatableString extends \Google\Protobuf\Internal\Message
{
    /**
     * The shortened string. For example, if the original string is 500
     * bytes long and the limit of the string is 128 bytes, then
     * `value` contains the first 128 bytes of the 500-byte string.
     * Truncation always happens on a UTF8 character boundary. If there
     * are multi-byte characters in the string, then the length of the
     * shortened string might be less than the size limit.
     *
     * Generated from protobuf field <code>string value = 1;</code>
     */
    private $value = '';
    /**
     * The number of bytes removed from the original string. If this
     * value is 0, then the string was not shortened.
     *
     * Generated from protobuf field <code>int32 truncated_byte_count = 2;</code>
     */
    private $truncated_byte_count = 0;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $value
     *           The shortened string. For example, if the original string is 500
     *           bytes long and the limit of the string is 128 bytes, then
     *           `value` contains the first 128 bytes of the 500-byte string.
     *           Truncation always happens on a UTF8 character boundary. If there
     *           are multi-byte characters in the string, then the length of the
     *           shortened string might be less than the size limit.
     *     @type int $truncated_byte_count
     *           The number of bytes removed from the original string. If this
     *           value is 0, then the string was not shortened.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Devtools\Cloudtrace\V2\Trace::initOnce();
        parent::__construct($data);
    }

    /**
     * The shortened string. For example, if the original string is 500
     * bytes long and the limit of the string is 128 bytes, then
     * `value` contains the first 128 bytes of the 500-byte string.
     * Truncation always happens on a UTF8 character boundary. If there
     * are multi-byte characters in the string, then the length of the
     * shortened string might be less than the size limit.
     *
     * Generated from protobuf field <code>string value = 1;</code>
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * The shortened string. For example, if the original string is 500
     * bytes long and the limit of the string is 128 bytes, then
     * `value` contains the first 128 bytes of the 500-byte string.
     * Truncation always happens on a UTF8 character boundary. If there
     * are multi-byte characters in the string, then the length of the
     * shortened string might be less than the size limit.
     *
     * Generated from protobuf field <code>string value = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setValue($var)
    {
        GPBUtil::checkString($var, True);
        $this->value = $var;

        return $this;
    }

    /**
     * The number of bytes removed from the original string. If this
     * value is 0, then the string was not shortened.
     *
     * Generated from protobuf field <code>int32 truncated_byte_count = 2;</code>
     * @return int
     */
    public function getTruncatedByteCount()
    {
        return $this->truncated_byte_count;
    }

    /**
     * The number of bytes removed from the original string. If this
     * value is 0, then the string was not shortened.
     *
     * Generated from protobuf field <code>int32 truncated_byte_count = 2;</code>
     * @param int $var
     * @return $this
     */
    public function setTruncatedByteCount($var)
    {
        GPBUtil::checkInt32($var);
        $this->truncated_byte_count = $var;

        return $this;
    }

}

