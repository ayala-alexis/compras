<?php 

function is_cheque_file($corre){
	$file_src = 'public/upload/'.$corre;
	if(is_file($file_src.'.pdf')){
		return $file_src.'.pdf';
	}elseif(is_file($file_src.'.xlsx')){
		return $file_src.'.xlsx';
	}elseif(is_file($file_src.'.xls')){
		return $file_src.'.xls';
	}elseif(is_file($file_src.'.docx')){
		return $file_src.'.docx';
	}elseif(is_file($file_src.'.png')){
		return $file_src.'.png';
	}elseif(is_file($file_src.'.jpeg')){
		return $file_src.'.jpeg';
	}elseif(is_file($file_src.'.jpg')){
		return $file_src.'.jpg';
	}
	return '';
}

$is_files = is_cheque_file('2238');
echo "File: ".$is_files;
?>