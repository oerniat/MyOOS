<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/firestore/admin/v1/firestore_admin.proto

namespace Google\Cloud\Firestore\Admin\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The request for [FirestoreAdmin.UpdateField][google.firestore.admin.v1.FirestoreAdmin.UpdateField].
 *
 * Generated from protobuf message <code>google.firestore.admin.v1.UpdateFieldRequest</code>
 */
class UpdateFieldRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * The field to be updated.
     *
     * Generated from protobuf field <code>.google.firestore.admin.v1.Field field = 1;</code>
     */
    private $field = null;
    /**
     * A mask, relative to the field. If specified, only configuration specified
     * by this field_mask will be updated in the field.
     *
     * Generated from protobuf field <code>.google.protobuf.FieldMask update_mask = 2;</code>
     */
    private $update_mask = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Cloud\Firestore\Admin\V1\Field $field
     *           The field to be updated.
     *     @type \Google\Protobuf\FieldMask $update_mask
     *           A mask, relative to the field. If specified, only configuration specified
     *           by this field_mask will be updated in the field.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Firestore\Admin\V1\FirestoreAdmin::initOnce();
        parent::__construct($data);
    }

    /**
     * The field to be updated.
     *
     * Generated from protobuf field <code>.google.firestore.admin.v1.Field field = 1;</code>
     * @return \Google\Cloud\Firestore\Admin\V1\Field
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * The field to be updated.
     *
     * Generated from protobuf field <code>.google.firestore.admin.v1.Field field = 1;</code>
     * @param \Google\Cloud\Firestore\Admin\V1\Field $var
     * @return $this
     */
    public function setField($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Firestore\Admin\V1\Field::class);
        $this->field = $var;

        return $this;
    }

    /**
     * A mask, relative to the field. If specified, only configuration specified
     * by this field_mask will be updated in the field.
     *
     * Generated from protobuf field <code>.google.protobuf.FieldMask update_mask = 2;</code>
     * @return \Google\Protobuf\FieldMask
     */
    public function getUpdateMask()
    {
        return $this->update_mask;
    }

    /**
     * A mask, relative to the field. If specified, only configuration specified
     * by this field_mask will be updated in the field.
     *
     * Generated from protobuf field <code>.google.protobuf.FieldMask update_mask = 2;</code>
     * @param \Google\Protobuf\FieldMask $var
     * @return $this
     */
    public function setUpdateMask($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\FieldMask::class);
        $this->update_mask = $var;

        return $this;
    }

}

