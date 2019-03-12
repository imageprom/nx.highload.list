<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
// Если поле UF_FILE содержит файл

foreach ($arResult['ITEMS'] as $code => &$arItem) {
	if($arItem['~UF_FILE']) {
		$file = CFile::GetFileArray($arItem['~UF_FILE']);
		$file['FORMAT'] = pathinfo($file['FILE_NAME'], PATHINFO_EXTENSION);
		$file['SIZE'] = round($file['FILE_SIZE'] / 1024, 2);
		$file['UNIT'] = 'кб';
		 if($file['SIZE'] >= 1000 ) {
			$file['SIZE'] = round($file['FILE_SIZE']/(1024*1024),2);
			$file['UNIT'] = 'мб';
		 }

		$arItem['FILE'] = $file;
	}
}
?>