<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 5/22/18
 * Time: 12:17 PM
 */

namespace zlakomanoff\relatedentities;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class RelatedActiveRecord
 * @package zlakomanoff\relatedentities
 */
class RelatedActiveRecord extends ActiveRecord
{
    /**
     * @var array
     */
    private $_relations = [];

    /**
     * @var array
     */
    private $_params = [];

    /**
     * @param $attribute
     * @param $validationParams
     * @param $validator
     */
    public function relation($attribute, $validationParams, $validator)
    {
        $params = &$this->_params;
        $relations = &$this->_relations;

        $relations[$attribute] = $relations[$attribute] ?? [];
        $params[$attribute] = ArrayHelper::merge($validationParams ?? [], $params[$attribute] ?? []);

        foreach ($relations[$attribute] ?? [] as $model) {
            /** @var ActiveRecord $model */
            if (!$model->validate()) {
                //\Yii::$app->session->addFlash('danger', json_encode([get_class($model) => $model->errors]));
                $this->addError($attribute, json_encode($model->errors));
            }
        }
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {

        foreach ($this->_relations as $relationName => $relationModels) {

            $method = 'get' . ucfirst($relationName);

            if (!method_exists($this, $method)) {
                // skip non-declarated relations
                continue;
            }

            $activeQuery = $this->$method();

            if (!$activeQuery instanceof ActiveQuery) {
                // skip non-declarated relations
                continue;
            }

            // many to many ?
            $saveModelBeforeLink = empty($activeQuery->via) ? false : true;

            $modelClass = $activeQuery->modelClass;
            $primaryKeyFields = $modelClass::primaryKey();

            unset($method, $modelClass);

            if ($insert) {
                foreach ($relationModels as $relationModel) {
                    $this->relationCreate($relationName, $relationModel, $saveModelBeforeLink);
                }
                continue;
            }

            $relationModelsOrigin = [];
            $relationModelsNew = [];

            foreach ($activeQuery->all() as $relationModel) {
                $keys = $this->getPrimaryKeyValues($primaryKeyFields, $relationModel);

                if (!empty($keys)) {
                    $key = 'S' . implode('.', $keys);
                    $relationModelsOrigin[$key] = $relationModel;
                }
            }

            foreach ($relationModels as $relationModel) {
                $keys = $this->getPrimaryKeyValues($primaryKeyFields, $relationModel);

                if (empty($keys)) {
                    $this->relationCreate($relationName, $relationModel, $saveModelBeforeLink);
                } else {
                    $key = 'S' . implode('.', $keys);
                    $relationModelsNew[$key] = $relationModel;
                }
            }

            foreach (array_intersect_key($relationModelsOrigin, $relationModelsNew) ?? [] as $key => $model) {
                $model->load([$model->formNAme() => $relationModelsNew[$key]->attributes]);
                $this->relationUpdate($relationName, $model);
            }

            /*foreach ($relationModelsOrigin as $key => $originModel) {
                if (array_key_exists($key, $relationModelsNew)) {
                    $this->relationUpdate($relationName, $relationModelsNew[$key]);
                }
            }*/

            foreach (array_diff_key($relationModelsOrigin, $relationModelsNew) ?? [] as $key => $model) {
                $this->relationDelete($relationName, $model);
            }

        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $method = 'get' . ucfirst($name);

        if (method_exists($this, $method) and $this->$method() instanceof ActiveQuery) {
            $activeQuery = $this->$method();
            $modelClass = $activeQuery->modelClass;
            foreach ($value as $attributes) {
                $this->_relations[$name][] = new $modelClass($attributes);
            }
            return;
        }

        parent::__set($name, $value);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (method_exists($this, 'get' . ucfirst($name))) {
            if (!empty($this->_relations[$name])) {
                return $this->_relations[$name];
            }
        }

        return parent::__get($name);
    }

    /**
     * @param array $fields
     * @param ActiveRecord $model
     * @return array
     */
    public function getPrimaryKeyValues(array $fields, ActiveRecord $model): array
    {
        $keys = [];
        foreach ($fields as $field) {
            if ($model->canGetProperty($field) and !empty($model->$field)) {
                $keys[] = $model->$field;
            }
        }
        return $keys;
    }

    /**
     * @param $relation
     * @param $model
     */
    public function relationCreate(string $relation, ActiveRecord $model, bool $saveBefore)
    {
        if ($model->validate()) {

            // many to many ?
            if ($saveBefore) {
                $model->save(false);
            }

            $this->link($relation, $model);
            return;
        }
        $this->addError($relation, 'related model have error');
    }

    /**
     * @param string $relation
     * @param ActiveRecord $model
     */
    public function relationUpdate(string $relation, ActiveRecord $model)
    {
        if ($model->validate()) {
            $model->save(false);
            return;
        }
        $this->addError($relation, 'related model have error');
    }

    /**
     * @param string $relation
     * @param ActiveRecord $model
     */
    public function relationDelete(string $relation, ActiveRecord $model)
    {
        $this->unlink($relation, $model, $this->_params[$relation]['delete'] ?? true);
    }

}