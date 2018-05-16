<?php
/**
 * Created by PhpStorm.
 * User: zlakomanoff
 * Date: 5/16/18
 * Time: 10:20 AM
 */

namespace zlakomanoff\relatedentities;

use yii\db\ActiveRecord;

/**
 * Class RelationModelWrapper
 * @package app\services
 */
class RelatedEntitiesModelWrapper
{
    /**
     * @var ActiveRecord
     */
    private $model;

    /**
     * @var string
     */
    private $field;

    /**
     *  constructor.
     * @param $model
     * @param $field
     */
    public function __construct($model, $field)
    {
        $this->model = $model;
        $this->field = $field;
    }

    /**
     * @param $name string
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->model->$name)) {
            return $this->model->$name;
        }

        return '';
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->model, $name)) {
            if (in_array($name, ['getAttributeLabel', 'getAttributeHint'])) {
                $arguments = $arguments[0];
            }
            return $this->model->$name($arguments);
        }

        return '';
    }

    /**
     * @return string
     */
    public function formName()
    {
        return $this->field;
    }
}