<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/automl/v1beta1/image.proto

namespace Google\Cloud\AutoMl\V1beta1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Model metadata specific to image object detection.
 *
 * Generated from protobuf message <code>google.cloud.automl.v1beta1.ImageObjectDetectionModelMetadata</code>
 */
class ImageObjectDetectionModelMetadata extends \Google\Protobuf\Internal\Message
{
    /**
     * Optional. Type of the model. The available values are:
     * *   `cloud-high-accuracy-1` - (default) A model to be used via prediction
     *               calls to AutoML API. Expected to have a higher latency, but
     *               should also have a higher prediction quality than other
     *               models.
     * *   `cloud-low-latency-1` -  A model to be used via prediction
     *               calls to AutoML API. Expected to have low latency, but may
     *               have lower prediction quality than other models.
     *
     * Generated from protobuf field <code>string model_type = 1;</code>
     */
    private $model_type = '';
    /**
     * Output only. The number of nodes this model is deployed on. A node is an
     * abstraction of a machine resource, which can handle online prediction QPS
     * as given in the qps_per_node field.
     *
     * Generated from protobuf field <code>int64 node_count = 3;</code>
     */
    private $node_count = 0;
    /**
     * Output only. An approximate number of online prediction QPS that can
     * be supported by this model per each node on which it is deployed.
     *
     * Generated from protobuf field <code>double node_qps = 4;</code>
     */
    private $node_qps = 0.0;
    /**
     * Output only. The reason that this create model operation stopped,
     * e.g. `BUDGET_REACHED`, `MODEL_CONVERGED`.
     *
     * Generated from protobuf field <code>string stop_reason = 5;</code>
     */
    private $stop_reason = '';
    /**
     * The train budget of creating this model, expressed in milli node
     * hours i.e. 1,000 value in this field means 1 node hour. The actual
     * `train_cost` will be equal or less than this value. If further model
     * training ceases to provide any improvements, it will stop without using
     * full budget and the stop_reason will be `MODEL_CONVERGED`.
     * Note, node_hour  = actual_hour * number_of_nodes_invovled.
     * For model type `cloud-high-accuracy-1`(default) and `cloud-low-latency-1`,
     * the train budget must be between 20,000 and 2,000,000 milli node hours,
     * inclusive. The default value is 216, 000 which represents one day in
     * wall time.
     * For model type `mobile-low-latency-1`, `mobile-versatile-1`,
     * `mobile-high-accuracy-1`, `mobile-core-ml-low-latency-1`,
     * `mobile-core-ml-versatile-1`, `mobile-core-ml-high-accuracy-1`, the train
     * budget must be between 1,000 and 100,000 milli node hours, inclusive.
     * The default value is 24, 000 which represents one day in wall time.
     *
     * Generated from protobuf field <code>int64 train_budget_milli_node_hours = 6;</code>
     */
    private $train_budget_milli_node_hours = 0;
    /**
     * Output only. The actual train cost of creating this model, expressed in
     * milli node hours, i.e. 1,000 value in this field means 1 node hour.
     * Guaranteed to not exceed the train budget.
     *
     * Generated from protobuf field <code>int64 train_cost_milli_node_hours = 7;</code>
     */
    private $train_cost_milli_node_hours = 0;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $model_type
     *           Optional. Type of the model. The available values are:
     *           *   `cloud-high-accuracy-1` - (default) A model to be used via prediction
     *                         calls to AutoML API. Expected to have a higher latency, but
     *                         should also have a higher prediction quality than other
     *                         models.
     *           *   `cloud-low-latency-1` -  A model to be used via prediction
     *                         calls to AutoML API. Expected to have low latency, but may
     *                         have lower prediction quality than other models.
     *     @type int|string $node_count
     *           Output only. The number of nodes this model is deployed on. A node is an
     *           abstraction of a machine resource, which can handle online prediction QPS
     *           as given in the qps_per_node field.
     *     @type float $node_qps
     *           Output only. An approximate number of online prediction QPS that can
     *           be supported by this model per each node on which it is deployed.
     *     @type string $stop_reason
     *           Output only. The reason that this create model operation stopped,
     *           e.g. `BUDGET_REACHED`, `MODEL_CONVERGED`.
     *     @type int|string $train_budget_milli_node_hours
     *           The train budget of creating this model, expressed in milli node
     *           hours i.e. 1,000 value in this field means 1 node hour. The actual
     *           `train_cost` will be equal or less than this value. If further model
     *           training ceases to provide any improvements, it will stop without using
     *           full budget and the stop_reason will be `MODEL_CONVERGED`.
     *           Note, node_hour  = actual_hour * number_of_nodes_invovled.
     *           For model type `cloud-high-accuracy-1`(default) and `cloud-low-latency-1`,
     *           the train budget must be between 20,000 and 2,000,000 milli node hours,
     *           inclusive. The default value is 216, 000 which represents one day in
     *           wall time.
     *           For model type `mobile-low-latency-1`, `mobile-versatile-1`,
     *           `mobile-high-accuracy-1`, `mobile-core-ml-low-latency-1`,
     *           `mobile-core-ml-versatile-1`, `mobile-core-ml-high-accuracy-1`, the train
     *           budget must be between 1,000 and 100,000 milli node hours, inclusive.
     *           The default value is 24, 000 which represents one day in wall time.
     *     @type int|string $train_cost_milli_node_hours
     *           Output only. The actual train cost of creating this model, expressed in
     *           milli node hours, i.e. 1,000 value in this field means 1 node hour.
     *           Guaranteed to not exceed the train budget.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Automl\V1Beta1\Image::initOnce();
        parent::__construct($data);
    }

    /**
     * Optional. Type of the model. The available values are:
     * *   `cloud-high-accuracy-1` - (default) A model to be used via prediction
     *               calls to AutoML API. Expected to have a higher latency, but
     *               should also have a higher prediction quality than other
     *               models.
     * *   `cloud-low-latency-1` -  A model to be used via prediction
     *               calls to AutoML API. Expected to have low latency, but may
     *               have lower prediction quality than other models.
     *
     * Generated from protobuf field <code>string model_type = 1;</code>
     * @return string
     */
    public function getModelType()
    {
        return $this->model_type;
    }

    /**
     * Optional. Type of the model. The available values are:
     * *   `cloud-high-accuracy-1` - (default) A model to be used via prediction
     *               calls to AutoML API. Expected to have a higher latency, but
     *               should also have a higher prediction quality than other
     *               models.
     * *   `cloud-low-latency-1` -  A model to be used via prediction
     *               calls to AutoML API. Expected to have low latency, but may
     *               have lower prediction quality than other models.
     *
     * Generated from protobuf field <code>string model_type = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setModelType($var)
    {
        GPBUtil::checkString($var, True);
        $this->model_type = $var;

        return $this;
    }

    /**
     * Output only. The number of nodes this model is deployed on. A node is an
     * abstraction of a machine resource, which can handle online prediction QPS
     * as given in the qps_per_node field.
     *
     * Generated from protobuf field <code>int64 node_count = 3;</code>
     * @return int|string
     */
    public function getNodeCount()
    {
        return $this->node_count;
    }

    /**
     * Output only. The number of nodes this model is deployed on. A node is an
     * abstraction of a machine resource, which can handle online prediction QPS
     * as given in the qps_per_node field.
     *
     * Generated from protobuf field <code>int64 node_count = 3;</code>
     * @param int|string $var
     * @return $this
     */
    public function setNodeCount($var)
    {
        GPBUtil::checkInt64($var);
        $this->node_count = $var;

        return $this;
    }

    /**
     * Output only. An approximate number of online prediction QPS that can
     * be supported by this model per each node on which it is deployed.
     *
     * Generated from protobuf field <code>double node_qps = 4;</code>
     * @return float
     */
    public function getNodeQps()
    {
        return $this->node_qps;
    }

    /**
     * Output only. An approximate number of online prediction QPS that can
     * be supported by this model per each node on which it is deployed.
     *
     * Generated from protobuf field <code>double node_qps = 4;</code>
     * @param float $var
     * @return $this
     */
    public function setNodeQps($var)
    {
        GPBUtil::checkDouble($var);
        $this->node_qps = $var;

        return $this;
    }

    /**
     * Output only. The reason that this create model operation stopped,
     * e.g. `BUDGET_REACHED`, `MODEL_CONVERGED`.
     *
     * Generated from protobuf field <code>string stop_reason = 5;</code>
     * @return string
     */
    public function getStopReason()
    {
        return $this->stop_reason;
    }

    /**
     * Output only. The reason that this create model operation stopped,
     * e.g. `BUDGET_REACHED`, `MODEL_CONVERGED`.
     *
     * Generated from protobuf field <code>string stop_reason = 5;</code>
     * @param string $var
     * @return $this
     */
    public function setStopReason($var)
    {
        GPBUtil::checkString($var, True);
        $this->stop_reason = $var;

        return $this;
    }

    /**
     * The train budget of creating this model, expressed in milli node
     * hours i.e. 1,000 value in this field means 1 node hour. The actual
     * `train_cost` will be equal or less than this value. If further model
     * training ceases to provide any improvements, it will stop without using
     * full budget and the stop_reason will be `MODEL_CONVERGED`.
     * Note, node_hour  = actual_hour * number_of_nodes_invovled.
     * For model type `cloud-high-accuracy-1`(default) and `cloud-low-latency-1`,
     * the train budget must be between 20,000 and 2,000,000 milli node hours,
     * inclusive. The default value is 216, 000 which represents one day in
     * wall time.
     * For model type `mobile-low-latency-1`, `mobile-versatile-1`,
     * `mobile-high-accuracy-1`, `mobile-core-ml-low-latency-1`,
     * `mobile-core-ml-versatile-1`, `mobile-core-ml-high-accuracy-1`, the train
     * budget must be between 1,000 and 100,000 milli node hours, inclusive.
     * The default value is 24, 000 which represents one day in wall time.
     *
     * Generated from protobuf field <code>int64 train_budget_milli_node_hours = 6;</code>
     * @return int|string
     */
    public function getTrainBudgetMilliNodeHours()
    {
        return $this->train_budget_milli_node_hours;
    }

    /**
     * The train budget of creating this model, expressed in milli node
     * hours i.e. 1,000 value in this field means 1 node hour. The actual
     * `train_cost` will be equal or less than this value. If further model
     * training ceases to provide any improvements, it will stop without using
     * full budget and the stop_reason will be `MODEL_CONVERGED`.
     * Note, node_hour  = actual_hour * number_of_nodes_invovled.
     * For model type `cloud-high-accuracy-1`(default) and `cloud-low-latency-1`,
     * the train budget must be between 20,000 and 2,000,000 milli node hours,
     * inclusive. The default value is 216, 000 which represents one day in
     * wall time.
     * For model type `mobile-low-latency-1`, `mobile-versatile-1`,
     * `mobile-high-accuracy-1`, `mobile-core-ml-low-latency-1`,
     * `mobile-core-ml-versatile-1`, `mobile-core-ml-high-accuracy-1`, the train
     * budget must be between 1,000 and 100,000 milli node hours, inclusive.
     * The default value is 24, 000 which represents one day in wall time.
     *
     * Generated from protobuf field <code>int64 train_budget_milli_node_hours = 6;</code>
     * @param int|string $var
     * @return $this
     */
    public function setTrainBudgetMilliNodeHours($var)
    {
        GPBUtil::checkInt64($var);
        $this->train_budget_milli_node_hours = $var;

        return $this;
    }

    /**
     * Output only. The actual train cost of creating this model, expressed in
     * milli node hours, i.e. 1,000 value in this field means 1 node hour.
     * Guaranteed to not exceed the train budget.
     *
     * Generated from protobuf field <code>int64 train_cost_milli_node_hours = 7;</code>
     * @return int|string
     */
    public function getTrainCostMilliNodeHours()
    {
        return $this->train_cost_milli_node_hours;
    }

    /**
     * Output only. The actual train cost of creating this model, expressed in
     * milli node hours, i.e. 1,000 value in this field means 1 node hour.
     * Guaranteed to not exceed the train budget.
     *
     * Generated from protobuf field <code>int64 train_cost_milli_node_hours = 7;</code>
     * @param int|string $var
     * @return $this
     */
    public function setTrainCostMilliNodeHours($var)
    {
        GPBUtil::checkInt64($var);
        $this->train_cost_milli_node_hours = $var;

        return $this;
    }

}

