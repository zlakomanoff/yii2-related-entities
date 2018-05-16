relatedentities
===============
relatedentities

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist zlakomanoff/yii2-relatedentities "*"
```

or add

```
"zlakomanoff/yii2-relatedentities": "*"
```

to the require section of your `composer.json` file.


Usage
-----

view

```
<?php $form = ActiveForm::begin([
        'fieldClass' => 'zlakomanoff\relatedentities\RelatedEntitiesActiveField',
        'layout' => 'inline'
    ]); ?>

<?= $form->field($model, 'videos')->relation('//videos/_related_form') ?>
```

model

```
public function rules()
{
    return [
        [['videos', 'titles'], 'safe'],
    ];
}

public function behaviors()
{
    return [
        [
            'class' => \zlakomanoff\relatedentities\RelatedEntitiesBehavior::class,
            'attributes' => [
                'videos' => Videos::class,
                'titles' => Titles::class,
            ]
        ]
    ];
}
```