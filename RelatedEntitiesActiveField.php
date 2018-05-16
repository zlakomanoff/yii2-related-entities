<?php
/**
 * Created by PhpStorm.
 * User: zlakomanoff
 * Date: 5/11/18
 * Time: 12:59 PM
 */

namespace zlakomanoff\relatedentities;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Class ActiveField
 * @package app\services
 */
class RelatedEntitiesActiveField extends \yii\widgets\ActiveField
{

    /**
     * @var int
     */
    private static $counter;

    /**
     * @var array
     */
    private $_options = [
        'modelClass' => null,
        'view' => [
            'wrapper' => [
                'tag' => 'div',
                'options' => [
                    'class' => 'relation-entity row',
                ]
            ],
            'params' => [],
            'context' => null,
        ],
        'plusButton' => [
            'text' => '+',
            'options' => [
                'class' => 'btn btn-info',
            ],
            'wrapper' => [
                'tag' => 'div',
                'options' => ['class' => 'form-group']
            ],
            'renderJs' => true,
        ],
        'minusButton' => [
            'text' => '-',
            'options' => [
                'class' => 'btn btn-danger',
                'onclick' => '$(this).parent().parent().remove()',
            ],
            'wrapper' => [
                'tag' => 'div',
                'options' => ['class' => 'form-group']
            ],
        ],
    ];

    /**
     * @param $template
     * @param array $options
     * @throws \yii\base\InvalidConfigException
     */
    public function relation($template, $options = [])
    {
        $options = ArrayHelper::merge($this->_options, $options);
        $modelClass = $options['modelClass'] ?? '\app\models\\' . ucfirst($this->attribute);
        $attribute = strtolower($this->attribute);

        if (!isset(self::$counter[$attribute])) {
            self::$counter[$attribute] = 0;
        }

        $counter = &self::$counter[$attribute];

        $relatedModels = $this->model->{$this->attribute};

        // have no related models
        if (empty($relatedModels)) {
            $relatedModels[] = new $modelClass();
        }

        // nasOne relation
        if ($relatedModels instanceof ActiveRecord) {
            $buffer = $relatedModels;
            $relatedModels = [];
            $relatedModels[] = $buffer;
        }

        foreach ($relatedModels as $relatedModel) {

            $field = $this->model->formName() . "[{$attribute}][{$counter}]";

            if ($options['view']['wrapper']) {
                $wrOptions = $options['view']['wrapper'];
                $wrOptions['options']['class'] .= ' ' . $attribute;
                $wrOptions['options']['data']['index'] = $counter;
                echo Html::beginTag($wrOptions['tag'], $wrOptions['options']);
            }

            $viewParams = $options['view']['params'];
            $viewParams['form'] = $viewParams['form'] ?? $this->form;
            $viewParams['model'] = $viewParams['model'] ?? new RelatedEntitiesModelWrapper($relatedModel, $field);

            echo $this->form->view->render($template, $viewParams);

            if ($options['minusButton']) {
                $minusOptions = $options['minusButton'];
                echo Html::beginTag($minusOptions['wrapper']['tag'], $minusOptions['wrapper']['options']);
                echo Html::button($minusOptions['text'], $minusOptions['options']);
                echo Html::endTag($minusOptions['wrapper']['tag']);
            }

            if ($options['view']['wrapper']) {
                echo Html::endTag($options['view']['wrapper']['tag']);
            }

            $counter++;
        }

        if ($options['plusButton']) {
            $plusOptions = $options['plusButton'];

            $plusOptions['options']['onclick'] = $plusOptions['options']['onclick'] ?? "relationPlus('{$attribute}')";

            echo Html::beginTag($plusOptions['wrapper']['tag'], $plusOptions['wrapper']['options']);
            echo Html::button($plusOptions['text'], $plusOptions['options']);
            echo Html::endTag($plusOptions['wrapper']['tag']);

            if ($plusOptions['renderJs']) {
                $this->form->view->registerJs('relationPlus = function(attribute) { 
                    $entities = $(".relation-entity." + attribute);
                    $lastEntity = $(".relation-entity." + attribute).last().clone();
                    var index = $lastEntity.data("index");
                    var newIndex = index + 1;
                    $lastEntity.attr("data-index", newIndex);
                    $lastEntity.find("*").each(function(key, value) {
                        var $element = $(value);
                        var elClass = $element.attr("class");
                        if (elClass) {
                            $element.attr("class", elClass.replace("-" + index + "-", "-" + newIndex + "-"));
                        }
                        var elFor = $element.attr("for");
                        if (elFor) {
                            $element.attr("for", elFor.replace("-" + index + "-", "-" + newIndex + "-"));
                        }
                        var elId = $element.attr("id");
                        if (elId) {
                            $element.attr("id", elId.replace("-" + index + "-", "-" + newIndex + "-"));
                        }
                        var elName = $element.attr("name");
                        if (elName) {
                            $element.attr("name", elName.replace("[" + index + "]", "[" + newIndex + "]"));
                        }
                        var elValue = $element.val();
                        if (elValue && $element.prop("tagName") !== "OPTION") {
                            $element.val("");
                        }
                    });
                    $(".relation-entity." + attribute).last().after($lastEntity);
                }');
            }

        }

    }

}
