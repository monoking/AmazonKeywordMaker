<?php
ini_set("max_execution_time", "1800");
date_default_timezone_set("Asia/Shanghai");

function dump($vars, $label = '', $return = false) {
	if (ini_get('html_errors')) {
		$content = "<pre>\n";
		if ($label != '') {
			$content .= "<strong>{$label} :</strong>\n";
		}
		$content .= htmlspecialchars(print_r($vars, true));
		$content .= "\n</pre>\n";
	} else {
		$content = $label . " :\n" . print_r($vars, true);
	}
	if ($return) { return $content; }
	echo $content;
	return null;
}

class amzkw
{
	const DELIMITER = ' ';
	public $seedFilePath = "keywords.csv";
	private $genStr = '';

	function getSeeds($level = '') {
		$single = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
		$doubleA = ['a0', 'a1', 'a2', 'a3', 'a4', 'a5', 'a6', 'a7', 'a8', 'a9'];
		$doubleB = ['bx0', 'bx1', 'bx2', 'bx3', 'bx4', 'bx5', 'bx6', 'bx7', 'bx8', 'bx9'];
		return $level ? ( $level == 's' ? $single : ($level == 'a' ? $doubleA : $doubleB)) : array_merge($single, $doubleA, $doubleB);
	}

	function genarate_sample($seedsGroup, $charsLimit = 20) {
		$rs = [];
		$currentIndex = -1;
		while (strlen($this->genStr) <= $charsLimit) {
			// $currentIndex = $currentIndex + 1 < count($seedsGroup) ? ++$currentIndex : 0;
			$currentIndex = mt_rand(0, count($seedsGroup) - 1);
			$t = $this->randomFindInArr($seedsGroup[$currentIndex], $rs);
			if (!empty($t)) {
				// var_dump($t);
				// var_dump($seedsGroup);
				array_push($rs, $t);
				shuffle($rs);
				$this->genStr = implode(self :: DELIMITER, $rs);
			}
		}
		// var_dump($this->genStr);
		$this->cutArrIfneed($this->genStr, $charsLimit);
		echo $this->genStr;
		// return $rs;
		// $this->exportExcel($rs);
		// var_dump($rs);
	}

	function randomFindInArr(&$seeds = []) {
		if (empty($seeds)) {
			return;
		}
		$index = mt_rand(0, count($seeds)-1);
		$temp = $seeds[$index];
		unset($seeds[$index]);
		$seeds = $this->rebuildArr($seeds);
		return $temp;		
	}

	function cutArrIfneed(&$str, $limit) {
		if (strlen($str) <= $limit) {
			return $str;
		}
		$arr = explode(self :: DELIMITER, $str);
		array_pop($arr);
		$str = implode(self :: DELIMITER, $arr);
		if (strlen($str) > $limit) {
			$this->cutArrIfneed($str, $limit);
		}
	}

	function rebuildArr($arr) {
		$rs = [];
		foreach ($arr as $v) {
			array_push($rs, $v);
		}
		return $rs;
	}

	function generate($charsLimit = 20, $columnNum = 100) {
        $data = $this->readSeedsFromExcel($this->seedFilePath);
        if (!$data) exit('some error occured!');

  //       $rs = [];
		// $currentIndex = -1;
		// while (strlen($this->genStr) <= $charsLimit) {
		// 	$t = $this->randomFindInArr($data, $rs);
		// 	if (!empty($t)) {
		// 		// var_dump($t);
		// 		// var_dump($seedsGroup);
		// 		array_push($rs, $t);
		// 		shuffle($rs);
		// 		$this->genStr = implode(self :: DELIMITER, $rs);
		// 	}
		// }
		// // var_dump($this->genStr);
		// $this->cutArrIfneed($this->genStr, $charsLimit);
		// // echo $this->genStr;

  //       $this->exportExcel(explode(self :: DELIMITER, $this->genStr));
  		$rs = [];
  		for ($i = 0; $i < $columnNum; $i++) { 
  			$tempData = $this->randomThem($data, $charsLimit);
  			if (count($tempData) > 0) {
  				array_push($rs, $tempData);
  			}
  		}
  		// dump($rs);
  		$this->exportExcel($rs);
    }

    function randomThem($data, $charsLimit) {
    	$rs = [];
		$currentIndex = -1;
		while (strlen($this->genStr) <= $charsLimit) {
			$t = $this->randomFindInArr($data, $rs);
			if (!empty($t)) {
				// var_dump($t);
				// var_dump($seedsGroup);
				array_push($rs, $t);
				shuffle($rs);
				$this->genStr = implode(self :: DELIMITER, $rs);
			}
		}
		$this->cutArrIfneed($this->genStr, $charsLimit);
		// dump($this->genStr);
		return explode(self :: DELIMITER, $this->genStr);
    }

	function readSeedsFromExcel($seedsFile) {
        header('Content-type: text/html; charset=UTF-8');
        $seedsFile = fopen($seedsFile, 'r');
        $rs = array();
        while ($line = fgetcsv($seedsFile, 1000, ",")){
            foreach ($line as $key => $value) {
                $rs[] = iconv('gb2312', 'utf-8', trim($value));
            }
        }
        if ($rs) {
            return $rs;
        }
        return false;
    }

	public function exportExcel($data=array(),$title=array(),$filename='random_keywords'){
	        header("Content-type:application/octet-stream");
	        header("Accept-Ranges:bytes");
	        header("Content-type:application/vnd.ms-excel");
	        header("Content-Disposition:attachment;filename=".$filename.".xls");
	        header("Pragma: no-cache");
	        header("Expires: 0");
	        //start export excel
	        if (!empty($title)){
	            foreach ($title as $k => $v) {
	                $title[$k]=iconv("UTF-8", "GB2312",$v);
	            }
	            $title= implode("\t", $title);
	            echo "$title\n";
	        }
	        // if (!empty($data)){
	        //     foreach($data as $key=>$val){
	        //         foreach ($val as $ck => $cv) {
	        //             $data[$key][$ck]=iconv("UTF-8", "GB2312", $cv);
	        //         }
	        //         $data[$key]=implode("\t", $data[$key]);

	        //     }
	        //     echo implode("\n",$data);
	        // }
	        // if (!empty($data)){
	        //     foreach($data as $key=>$val){
         //            $data[$key]=iconv("UTF-8", "GB2312", $val);
	        //     }
         //        $data = implode("\t", $data);
	        //     echo $data;
	        // }
	        if (!empty($data)){
	        	foreach($data as $key=>$val){
	        		foreach ($val as $ck => $cv) {
	                    $data[$key][$ck]=iconv("UTF-8", "GB2312", $cv);
	                }
                    $data[$key]=implode(" ", $data[$key]);
	            }
	            echo implode("\n",$data);
	        }
	    }
}

$class = new amzkw();
// $seeds = [$class->getSeeds('s'), $class->getSeeds('b'), $class->getSeeds('a')];
// $class->genarate_sample($seeds, 100);
$class->generate(800);

?>