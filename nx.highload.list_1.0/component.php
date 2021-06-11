<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if(!CModule::IncludeModule('highloadblock')) {
	ShowError(GetMessage('F_NO_MODULE'));
	return 0;
}

use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;

if(!$arParams['HBLOCK_ID']) return 0;
$arParams['AJAX'] = isset($_REQUEST['nx_ajax_hibl_action']) && $_REQUEST['nx_ajax_hibl_action'] == 'Y';

if (isset($arParams['FILTER_NAME']) && !empty($arParams['FILTER_NAME']) && preg_match('/^[A-Za-z_][A-Za-z01-9_]*$/', $arParams['FILTER_NAME'])) {
	global ${$arParams['FILTER_NAME']};
	$arrFrilter = ${$arParams['FILTER_NAME']};
}

if($arParams['USE_PAGINATION'] == 'Y') {

	$arParams['PAGE_ELEMENT_COUNT'] = intval($arParams['PAGE_ELEMENT_COUNT']);
	$arParams['PAGER_SHOW_ALL'] = false;
	$arParams['PAGER_TEMPLATE'] = trim($arParams['PAGER_TEMPLATE']);
	$arParams['PAGER_TITLE'] = trim($arParams['PAGER_TITLE']);

	$arNavParams = array(
		'nPageSize' => $arParams['PAGE_ELEMENT_COUNT'],
		'iNumPage' => is_set($_GET['PAGEN_1']) ? $_GET['PAGEN_1'] : 1,
		'bShowAll' => $arParams['PAGER_SHOW_ALL'],
	);
}

else $arNavParams = false;

if($this->StartResultCache(false, array($arrFilter, ($arParams['CACHE_GROUPS']==='N'? false: $USER->GetGroups()), $arNavParams))) {

	$hlblock_id = $arParams['HBLOCK_ID'];

	$hlblock = HL\HighloadBlockTable::getById($hlblock_id)->fetch();

	if (empty($hlblock)) {
		ShowError('404');
		return 0;
	}

	$entity = HL\HighloadBlockTable::compileEntity($hlblock);

	// USER FIELD INFO

	$fields = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields('HLBLOCK_'.$hlblock['ID'], 0, LANGUAGE_ID);

	foreach ($fields as &$field) {
		if($field['USER_TYPE_ID'] == 'enumeration') {

			$obEnum = new \CUserFieldEnum;
			$rsEnum = $obEnum->GetList(array(), array('USER_FIELD_ID' => $field['ID']));

			while($arEnum = $rsEnum->Fetch()) {
			    $field['VALUES'][$arEnum["ID"]] = $arEnum;
			}
		}
	}

	// SORT

	$sortId = 'ID';
	$sortType = 'DESC';

	if (!empty($arParams['SORT_BY']) ) $sortId = $arParams['SORT_BY'];
	if (!empty($arParams['SORT_ORDER']) ) $sortType = $arParams['SORT_ORDER'];

	$main_query = new Entity\Query($entity);
	$main_query->setSelect(array('*'));
	$main_query->setOrder(array($sortId => $sortType));

	// FILTER

	if (is_array($arrFrilter)){
		$main_query->setFilter($arrFrilter);
	}

	// PAGINATION

	if(is_array($arNavParams)) {
		if (isset($arNavParams['nPageTop'])) {
			$main_query->setLimit($arNavParams['nPageTop']);
		}
		else {
			$main_query->setLimit($arNavParams['nPageSize']);
			$main_query->setOffset(($arNavParams['iNumPage']-1) * $arNavParams['nPageSize']);
		}
	}
	elseif($arParams['PAGE_ELEMENT_COUNT'] > 0) {
		$main_query->setLimit($arParams['PAGE_ELEMENT_COUNT']);	
	}

	$arHibResult = $main_query->exec();
	$arHibResult = new CDBResult($arHibResult);

	while ($row = $arHibResult->GetNext()) {
		foreach ($row as $k => $v) {
			if ($k == 'ID') {
				$tableColumns['ID'] = true;
				continue;
			}

			$arUserField = $fields[$k];

			if ($arUserField['SHOW_IN_LIST'] != 'Y') {
				continue;
			}

			$html = call_user_func_array(
				array($arUserField['USER_TYPE']['CLASS_NAME'], 'getadminlistviewhtml'),
				array(
					$arUserField,
					array(
						'NAME' => 'FIELDS['.$row['ID'].']['.$arUserField['FIELD_NAME'].']',
						'VALUE' => htmlspecialcharsbx($v)
					)
				)
			);

			if($html == '') {
				$html = '&nbsp;';
			}

			$tableColumns[$k] = true;

			$row['~'.$k] = $row[$k];
			$row[$k] = $html;
			
		}

		$rows[] = $row;
	}

	$arResult['ITEMS'] = $rows;
	$arResult['FIELDS'] = $fields;
	$arResult['COLUMNS'] = $tableColumns;

	if(is_array($arNavParams)) {
		$countQuery = new Entity\Query($entity);

		$countQuery->registerRuntimeField('cnt', array(
			'data_type' => 'integer',
			'expression' => array('count(*)')
			)
		);

		$countQuery->setSelect(array('cnt'));

		if (is_array($arrFrilter)){
			$countQuery->setFilter($arrFrilter);
		}

		$totalCount = new CDBResult($countQuery->exec());
		
		if($counElement = $totalCount->GetNext()) $totalCount = $counElement['cnt'];
		        
		$totalPage = ceil($totalCount/$arNavParams['nPageSize']);

		$arHibResult->NavRecordCount = $totalCount;
		$arHibResult->NavPageCount = $totalPage;
		$arHibResult->NavPageNomer = $arNavParams['iNumPage'];
		$arHibResult->NavNum = 1;
				
		$arResult['NAV_STRING'] = $arHibResult->GetPageNavStringEx($navComponentObject, $arParams['PAGER_TITLE'], (is_set($arParams['PAGER_TEMPLATE'])) ? $arParams['PAGER_TEMPLATE'] : '.default', false);
		$arResult['NAV_CACHED_DATA'] = $navComponentObject->GetTemplateCachedData();
		$arResult['NAV_RESULT'] = $arHibResult;
		$arResult['NAV_PARAMS'] = $arHibResult->GetNavParams();
	}

	$this->IncludeComponentTemplate(); 
}

if($arParams['AJAX']) {
	$this->setFrameMode(false);
	define("BX_COMPRESSION_DISABLED", true);
	ob_start();
	$this->IncludeComponentTemplate("ajax");
	$json = ob_get_contents();
	$APPLICATION->RestartBuffer();
	while(ob_end_clean());
	header('Content-Type: application/json; charset='.LANG_CHARSET);
	echo $json;
	CMain::FinalActions();
	die();
}
?>