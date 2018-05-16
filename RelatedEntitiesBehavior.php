<?php
/**
 * Created by PhpStorm.
 * User: zlakomanoff
 * Date: 5/15/18
 * Time: 11:22 AM
 */

namespace zlakomanoff\relatedentities;

use yii\db\ActiveRecord;

/**
 * Class RelatedModelsBehavior
 * @package app\services
 */
class RelatedEntitiesBehavior extends \yii\base\Behavior
{

    /**
     * @var array
     */
    public $attributes;

    /**
     * @var array
     */
    public $values;

    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
        ];
    }

    /**
     * @param string $name
     * @param bool $checkVars
     * @return bool
     */
    public function canSetProperty($name, $checkVars = true)
    {
        foreach ($this->attributes as $attribute => $className) {
            if ($attribute == $name) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->values[$name] = $value;
    }

    /**
     *
     */
    public function afterInsert($event)
    {
        /** @var ActiveRecord $owner */
        $owner = &$this->owner;

        foreach ($this->attributes as $attribute => $modelClass) {
            foreach ($this->values[$attribute] as $relatedModelData) {
                /** @var ActiveRecord $relatedModel */
                $relatedModel = new $modelClass($relatedModelData);
                $owner->link($attribute, $relatedModel);
            }
        }
    }

    /**
     *
     */
    public function afterUpdate($event)
    {
        /** @var ActiveRecord $owner */
        $owner = &$this->owner;

        foreach ($this->attributes as $attribute => $modelClass) {

            //@todo do not delete all related models on save
            $owner->unlinkAll($attribute, true);

            foreach ($this->values[$attribute] as $relatedModelData) {
                /** @var ActiveRecord $relatedModel */
                $relatedModel = new $modelClass($relatedModelData);
                $owner->link($attribute, $relatedModel);
            }
        }
    }

}