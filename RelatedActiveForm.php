<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 5/22/18
 * Time: 2:32 PM
 */

namespace zlakomanoff\relatedentities;

use yii\widgets\ActiveForm;

/**
 * Class RelatedActiveForm
 * @package zlakomanoff\relatedentities
 */
class RelatedActiveForm extends ActiveForm
{

    /**
     * @var string
     */
    public $fieldClass = 'zlakomanoff\relatedentities\RelatedEntitiesActiveField';

}