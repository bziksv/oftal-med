<?

$pageDir = explode("/",$_SERVER['REQUEST_URI']);

if($pageDir[1] != 'bitrix'){

	$fullLink = $_SERVER['HTTP_X_FORWARDED_PROTO'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

	$fullLink2 = $fullLink;
	$arUri = explode("?",$fullLink2);

	if($arUri[1]){
		$redirect_url = strtolower($arUri[0]).'?'.$arUri[1];
	}else{
		$redirect_url = strtolower($arUri[0]);
	}

	if($fullLink != $redirect_url && $redirect_url != '/'){
		header('Location: '.$redirect_url, true, 301);
		exit();
	}

}