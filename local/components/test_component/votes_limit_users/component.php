<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Loader,
	Bitrix\Iblock;

if(!Loader::includeModule("iblock"))
{
	ShowError(GetMessage("IBLOCK_MODULE_NONE"));
	return;
}

if (!isset($arParams['CACHE_TIME'])) {
    $arParams['CACHE_TIME'] = 3600;
}
$arParams['IBLOCK_TYPE'] = trim($arParams['IBLOCK_TYPE']);
$arParams['IBLOCK_ID'] = intval($arParams['IBLOCK_ID']);
$arParams['ELEMENT_COUNT'] = intval($arParams['ELEMENT_COUNT']);
if ($arParams['ELEMENT_COUNT'] <= 0) {
    $arParams['ELEMENT_COUNT'] = 10;
}

	$arNavParams = array(
		"nPageSize" => $arParams["ELEMENTS_PER_PAGE"],
	);

	$arNavigation = CDBResult::GetNavParams($arNavParams);

if ($this->StartResultCache(false, $arNavigation)) {

// выборка данных из инфоблока
	$obNews = CIBlockElement::GetList(
		 array("ID" => "desc"),
		 array(
 			"IBLOCK_ID" => $arParams['IBLOCK_ID'],
			"ACTIVE" => "Y"
		 ),
		 false,
		 $arNavParams,
		 array(
		 	"ID",
		 	"PREVIEW_PICTURE",
		 	"PREVIEW_TEXT",
		 	"NAME",
		 	"PROPERTY_RATING",
		 )
	);

	while ($resNews = $obNews->Fetch()) {
		$arResult["ELEMENTS"][] = $resNews;
	}


  if (isset($_POST["id"])) {
    $ELEMENT_ID = $_POST["id"]; 
  }

	// создаём свойство для рейтинга
	$PROPERTY_CODE = "RATING"; 

	$dbRes = CIBlockProperty::GetList(["ID" => "ASC"], [
		"IBLOCK_ID" => $arParams['IBLOCK_ID'], 
		"CODE" => $PROPERTY_CODE
	]);

	if (!$prop = $dbRes->GetNext()) {
		$arFields = Array(
			"NAME" => "Текущий рейтинг",
			"CODE" => $PROPERTY_CODE,
			"DEFAULT_VALUE" => 0,
			"PROPERTY_TYPE" => 'N',
			"IBLOCK_ID" => $arParams['IBLOCK_ID'],
		);
		$ibp = new CIBlockProperty();
		$propID = $ibp->Add($arFields);   
	}


	// создаём свойство для айди пользователей
	$PROPERTY_CODE = "USERS_ID"; 

	$dbRes = CIBlockProperty::GetList(["ID" => "ASC"], [
		"IBLOCK_ID" => $arParams['IBLOCK_ID'], 
		"CODE" => $PROPERTY_CODE
	]);

	if (!$prop = $dbRes->GetNext()) {
		$arFields = Array(
			"NAME" => "ID проголосовавшего пользователя",
			"CODE" => $PROPERTY_CODE,
			"DEFAULT_VALUE" => 0,
			"PROPERTY_TYPE" => 'N',
			"MULTIPLE" => 'Y',
			"IBLOCK_ID" => $arParams['IBLOCK_ID'],
		);
		$ibp = new CIBlockProperty();
		$propID = $ibp->Add($arFields);   
	}


// кастомная пагинация system.pagenavigation с шаблоном show_more_v2
  $arResult['NAV_STRING'] = $obNews->GetPageNavString(array(), "show_more_v2");

	$this->SetResultCacheKeys(array(
	 	/*"ID",
	 	"PREVIEW_PICTURE",
	 	"PREVIEW_TEXT",
	 	"NAME",
	 	"PROPERTY_RATING",*/
	 ));

	$this->includeComponentTemplate();	
	
} else {
	$this->AbortResultCache();
}

