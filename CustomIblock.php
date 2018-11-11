<?php
/**
* Функция получения списка элементов инфоблока 
* @function PrepareParams Функция подготовки параметров
* @function CachedGetList Функция получения списка элементов инфоблока
*/
class CustomIblock
{	
	
	function __construct()
	{
		CModule::IncludeModule("iblock");
	}
	
	private function PrepareParams($arParams){
	
		if (!is_array($arParams["ARSORT"])){
			$return["ARSORT"]=array();
		}else{
			$return["ARSORT"]=$arParams["ARSORT"];
		}
		
		$return["ARFILTER"]=$arParams["ARFILTER"];
		
		if(intval($arParams["NTOPCOUNT"])>0){
			$return["NTOPCOUNT"]=Array("nTopCount"=>$arParams["NTOPCOUNT"]);
		}else{
			$return["NTOPCOUNT"]=10;
		}
		
		if (!is_array($arParams["SELECTFIELDS"])){
			$return["ARSELECT"]=Array("ID", "IBLOCK_ID", "NAME");
		}else{
			$return["ARSELECT"] = array_merge(Array("ID", "IBLOCK_ID", "NAME"),  $arParams["SELECTFIELDS"]);
		}
		
		if (is_array($arParams["SELECTPROPS"])){

			$return["SELECTPROPS"]=$arParams["SELECTPROPS"];
		}
		
		if(intval($arParams["CACHETIME"])>0){
			$return["CACHETIME"]=$arParams["CACHETIME"];
		}else{
			$return["CACHETIME"]=3600;
		}
		
		$return["CACHEID"]="";
		foreach($return as $k=>$v)
		if(strncmp("~", $k, 1))
			$return["CACHEID"] .= ",".$k."=".serialize($v);
		
		return $return;
		
	}
	
	/**
	* Функция получения списка элементов инфоблока 
	* @param array $arParams["ARSORT"] Массив сортировки
	* @param array $arParams["ARFILTER"] Массив фильтра
	* @param int $arParams["NTOPCOUNT"] ограничить количество сверху
	* @param array $arParams["SELECTFIELDS"] Массив выбираемых полей
	* @param array $arParams["SELECTPROPS"] Массив выбираемых свойств
	* @param int $arParams["CACHETIME"] Время кеширования
	* @return array
	*/
	/*
	Пример вызова:
	
		require("CustomIblock.php");
		$InfoBlock=new CustomIblock();
		
		$listElements=$InfoBlock->CachedGetList(array(
			"ARSORT"=>Array("NAME"=>"DESC"),
			"ARFILTER"=>array("IBLOCK_ID"=>98),
			"NTOPCOUNT"=>5,
			"SELECTFIELDS"=>Array("ID", "IBLOCK_ID", "NAME", "PREVIEW_TEXT", "PREVIEW_PICTURE"),
			"SELECTPROPS"=>Array("PERSONAL_ADMIN_COUNT_COMPANY"),
			"CACHETIME"=>3600
			)
		);
	*/
	public function CachedGetList($arParams){
		
		
		if (!isset($arParams["ARFILTER"]["IBLOCK_ID"]) || !(intval($arParams["ARFILTER"]["IBLOCK_ID"])>0)){
			return NULL;
		}
		
		$Params=$this->PrepareParams($arParams);
				
		$cachePath = '/ModifyGetList/';
		$obCache = new CPHPCache();
		 
		if ($obCache->InitCache($Params["CACHETIME"], $Params["CACHEID"], $cachePath))
		{
		   $CacheResult = $obCache->GetVars();
		   $arResult=$CacheResult["ModifyGetList"];
		}
		else
		{	
			$obCache->StartDataCache();
			$arResult=array();
			$res = CIBlockElement::GetList($Params["ARSORT"], $Params["ARFILTER"], false, $Params["NTOPCOUNT"], $Params["ARSELECT"]);
			
			while($ob = $res->GetNextElement())
			{
				$retVars=array();
				$arFields = $ob->GetFields();
				$retVars["FIELDS"]=$arFields;
				
				/*Если в параметрах указано получение хотя бы одного свойства, то возвращаются все свойства */
				if (count($Params["SELECTPROPS"])>0){
					$arProps = $ob->GetProperties();
					$retVars["PROPERTIES"]=$arProps;
				}
				$arResult[]=$retVars;
			}
			
			$obCache->EndDataCache(array("ModifyGetList" => $arResult));
		} 
	
		return $arResult;
	}
	
}