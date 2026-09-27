<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @global CMain $APPLICATION
 */

global $APPLICATION;


//delayed function must return a string
if(empty($arResult))
	return "";

$strReturn = '';

$strReturn .= '<div class="breadcrumb"><div class="container"><ol class="breadcrumb__list" itemscope itemtype="https://schema.org/BreadcrumbList">';

$itemSize = count($arResult);
for($index = 0; $index < $itemSize; $index++)
{
	$title = htmlspecialcharsex($arResult[$index]["TITLE"]);
	$visibleTitle = ($index > 0) ? $title : "";
	$icon = (!$index) ? "icon-home" : "";
	$position = $index + 1;

	if($arResult[$index]["LINK"] <> "" && $index != $itemSize-1)
	{
		$homeNoindex = ($index === 0) ? array('<!--noindex-->', '<!--/noindex-->', ' rel="nofollow"') : array('', '', '');
		$nameMarkup = ($index === 0)
			? '<span></span>'
			: '<span itemprop="name">'.$visibleTitle.'</span>';
		$strReturn .= '
			<li class="breadcrumb__item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				'.$homeNoindex[0].'<a href="'.$arResult[$index]["LINK"].'" title="'.$visibleTitle.'" itemprop="item" class="'.$icon.'"'.$homeNoindex[2].'>
					'.$nameMarkup.'
				</a>'.$homeNoindex[1].'
				'.($index === 0 ? '<meta itemprop="name" content="'.$title.'" />' : '').'
				<meta itemprop="position" content="'.$position.'" />
			</li>';
	}
	else
	{
		$strReturn .= '
			<li class="breadcrumb__item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<span itemprop="name">'.$visibleTitle.'</span>
				<meta itemprop="position" content="'.$position.'" />
			</li>';
	}
}

$strReturn .= '</ol></div></div>';

return $strReturn;

?>
