relatedentities
===============
relatedentities

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist zlakomanoff/yii2-related-entities "*"
```

or add

```
"zlakomanoff/yii2-related-entities": "*"
```

to the require section of your `composer.json` file.


Usage
-----

view

```
<?php $form = \zlakomanoff\relatedentities\RelatedActiveForm::begin(); ?>

or

<?php $form = ActiveForm::begin([
    'fieldClass' => 'zlakomanoff\relatedentities\RelatedEntitiesActiveField'
]); ?>

<?= $form->field($model, 'videos')->relation('//videos/_form_related') ?>
```

model

```
class MyRecord extends \zlakomanoff\relatedentities\RelatedActiveRecord
{

    public function rules()
    {
        return [
            [['field1', 'field2'], 'relation']
        ];
    }

    public function getField1()
    {
        return $this->hasMany(MyField1::class, ['id' => 'field1_id']);
    }

    public function getField2()
    {
        return $this->hasMany(MyField2::class, ['id' => 'field2_id']);
    }

}
```