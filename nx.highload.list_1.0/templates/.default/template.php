<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
$this->setFrameMode(true);
?>
<?if(count($arResult['ITEMS']) > 0):?>
	<section class="nx-hib-section">
		<?if($arParams['TITLE']):?><h2><?=$arParams['TITLE']?><h2><?endif;?>

		<?foreach ($arResult['ITEMS'] as $code => $arItem):?>
		  <div class="nx-hib-element">
		  	<b>Элемент <?=$arItem['ID']?></b>
		   	<pre>
		   		<?print_r($arItem)?>
			</pre>
		  </div>
		<?endforeach?>
		<?if($arParams['USE_PAGINATION'] == 'Y'):?><?=$arResult['NAV_STRING']?><?endif;?>
	<section>
<?endif;?>