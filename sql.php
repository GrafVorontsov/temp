<?php
	function getAmortizators($id, $lang){
		$con = dbConnect();
		$language = $lang;
		$qry = "SELECT t1.marka_name, t1.model_name, t1.car_name, t1.correction, t1.search, t1.year, t1.range_type, 
				ins.install, t1.art_number, i.info, il.info_lowering, t1.jpg, t1.pdf, 
				(SELECT  GROUP_CONCAT(DISTINCT CONCAT('<h3><p><b>', name_ru, ':</b></p></h3><p>', description_ru, '</p>') SEPARATOR ';') 
					FROM  info_table_description td
					INNER JOIN  info_table_numbers_description tnd ON tnd.description_id = td.id
					INNER JOIN  info_table_numbers tn ON tn.id = tnd.numbers_id
					WHERE  t1.art_number LIKE CONCAT('', article_number ,'%')) as description,
				CASE WHEN t2.SIMPLE_NUMBER IS NULL AND t3.SIMPLE_NUMBER IS NULL THEN 'под запрос'
					WHEN t2.SIMPLE_NUMBER IS NOT NULL AND t3.SIMPLE_NUMBER IS NOT NULL THEN CONCAT('<font color=\"green\">', 'наличие', '</font>') 
					WHEN t2.SIMPLE_NUMBER IS NULL AND t3.SIMPLE_NUMBER IS NOT NULL THEN CONCAT('<font color=\"red\">', 'заказ', '</font>')
					WHEN t2.SIMPLE_NUMBER IS NOT NULL AND t3.SIMPLE_NUMBER IS NULL THEN CONCAT('<font color=\"green\">', 'наличие', '</font>')
				ELSE 'неправильный номер'
				END status,
				IF(t2.SIMPLE_NUMBER IS NOT NULL AND t3.SIMPLE_NUMBER IS NOT NULL 
					OR t2.SIMPLE_NUMBER IS NOT NULL AND t3.SIMPLE_NUMBER IS NULL, t2.PRICE_EURO, t3.PRICE_EURO) AS PRICE_EURO    
				FROM amortizators t1
				LEFT JOIN price t2 ON t1.search = t2.SIMPLE_NUMBER 
				LEFT JOIN zakaz t3 ON t1.search = t3.SIMPLE_NUMBER
				JOIN info_lowering_t il ON t1.info_lowering_id = il.info_lowering_id
				JOIN info_t i ON t1.info_id = i.info_id
				JOIN install_t ins ON t1.install_id = ins.install_id
				JOIN languages l ON i.lang_id = l.id AND il.lang_id = l.id AND ins.lang_id = l.id
				WHERE car_id IN ('$id') AND l.code = '$language'
				ORDER BY t1.id";
		if(!$result = $con->query($qry)){
			echo $con->error;
			return false;
		}
		$result = formatOutput($result);
		$con->kill($con->thread_id);
		$con->close();
		return $result;
	}