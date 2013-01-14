<?php
/* @var $this SiteController */

$this->pageTitle=Yii::app()->name;



$googlePlaces = new googlePlaces(helper::yiiparam('googleApiKey'));

// Set the longitude and the latitude of the location you want to search the surronds of
$latitude   = '-33.8804166';
$longitude = '151.2107662';
$googlePlaces->setCurloptSslVerifypeer(false);
$googlePlaces->setLocation($latitude . ',' . $longitude);
$googlePlaces->setRadius(5000);
$results = $googlePlaces->Search();

?>
<h1>Welcome to <i><?php echo CHtml::encode(Yii::app()->name); ?></i></h1>

<p>Congratulations! You have successfully created your Yii application.</p>

<p>You may change the content of this page by modifying the following two files:</p>
<ul>
	<li>View file: <code><?php echo __FILE__; ?></code></li>
	<li>Layout file: <code><?php echo $this->getLayoutFile('main'); ?></code></li>
        <li>Link to <a href="<?php echo Yii::app()->createAbsoluteUrl('gii');?>">Gii</a></li>
        <li>Link to <a href="<?php echo Yii::app()->createAbsoluteUrl('//user/rest/list');?>">Users</a></li>
</ul>

<p>For more details on how to further develop this application, please read
the <a href="http://www.yiiframework.com/doc/">documentation</a>.
Feel free to ask in the <a href="http://www.yiiframework.com/forum/">forum</a>,
should you have any questions.</p>
