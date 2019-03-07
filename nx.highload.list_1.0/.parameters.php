<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule('highloadblock')) return;

use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;

$rsLang = \Bitrix\Highloadblock\HighloadBlockTable::getList(array('select' => array('*', 'NAME_LANG' => 'LANG.NAME')));

while ($arLang = $rsLang->fetch()) {
	if($arLang['NAME_LANG']) $arHIBLang[$arLang['ID']] = $arLang['NAME_LANG'];
	else $arHIBLang[$arLang['ID']] = $arLang['NAME'];
}

$rsHIBlock = HL\HighloadBlockTable::getList(array('select'=>array('*'), 'filter'=>array('!=TABLE_NAME' => '')));

while($arHib = $rsHIBlock->Fetch()) {
	$arHIBlock[$arHib['ID']] = $arHIBLang[$arHib['ID']]; 
	$arHIBlocks[$arHib['ID']] = $arHib;
}

if($arCurrentValues['HBLOCK_ID']) {

	$entity = HL\HighloadBlockTable::compileEntity($arHIBlocks[$arCurrentValues['HBLOCK_ID']]);
	$entityDataClass = $entity->getDataClass();

	$fields = $entity->getFields();
	foreach($fields as $code => $filed) {
		if($code != 'ID') {
			$ar_res = CUserTypeEntity::GetList(array('ID'=>'ASC'), array('FIELD_NAME' => $code));

			if($tmp = $ar_res->GetNext()) {
				$res = CUserTypeEntity::GetByID($tmp['ID']); 
				if($res['EDIT_FORM_LABEL'][LANGUAGE_ID]) $arProperty[$res['FIELD_NAME']] = $res['EDIT_FORM_LABEL']['ru'];
				else $arProperty[$res['FIELD_NAME']] = $res['FIELD_NAME'];
			}       
		}
	}
}

$arSorts = Array(
	'ASC' => GetMessage('IP_HIB_DESC_ASC'),
	'DESC' => GetMessage('IP_HIB_DESC_DESC'),
);

$arComponentParameters = array(
	'GROUPS' => array(),

	'PARAMETERS' => array(

		'TITLE' => Array(
			'NAME' =>  GetMessage('IP_HIB_TITLE'),
			'TYPE' => 'TEXT',
			'PARENT' => 'BASE',
			'REFRESH' => 'Y',
		),

		'HBLOCK_ID' => array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('IP_HIB_ID'),
			'TYPE' => 'LIST',
			'VALUES' => $arHIBlock,
			'REFRESH' => 'Y',
		),

		'SORT_BY'  =>  Array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('IP_HIB_SORT_FIELD'),
			'TYPE' => 'LIST',
			'VALUES' =>  $arProperty,
			'DEFAULT' => '',
		),

		'SORT_ORDER'  =>  Array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('IP_HIB_SORT_ORDER'),
			'TYPE' => 'LIST',
			'DEFAULT' => 'DESC',
			'VALUES' => $arSorts,
			'ADDITIONAL_VALUES' => 'Y',
		),

		'FILTER_NAME' => Array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('IP_HIB_FILER'),
			'TYPE' => 'TEXT',
			'DEFAULT' => 'arrHIBFilter',
		),

		'PAGE_ELEMENT_COUNT' => Array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('IP_HIB_EL_CОUNT'),
			'TYPE' => 'TEXT',
			'DEFAULT' => '',
		),

		'USE_PAGINATION' => Array(
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('IP_USE_PAGINATION'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
			'REFRESH' => 'Y',
		),

		'CACHE_TIME'  =>  Array('DEFAULT' => 300),
		'CACHE_GROUPS' => array(
			'PARENT' => 'CACHE_SETTINGS',
			'NAME' => GetMessage('IP_HIB_CACHE_GROUPS'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		),		
	)
);

if($arCurrentValues['USE_PAGINATION'] == 'Y') {
	$arComponentParameters['PARAMETERS']['PAGER_TITLE'] = array(
		'PARENT' => 'DATA_SOURCE',
		'NAME' => GetMessage('IP_HIB_PAGER_TITLE'),
		'TYPE' => 'TEXT',
		'DEFAULT' => GetMessage('IP_HIB_PAGER_TITLE_DEFAULT'),
	);

	$arComponentParameters['PARAMETERS']['PAGER_TEMPLATE'] = array(
		'PARENT' => 'DATA_SOURCE',
		'NAME' => GetMessage('IP_HIB_PAGER_TEMPLATE'),
		'TYPE' => 'TEXT',
		'DEFAULT' => '',
	);
}