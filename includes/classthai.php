<?php
class thai {

    /*==================Datetime=====================*/
    public static function date_format(DateTime $date, $format) {
        $thaidate = array(
            'Sun' => array('l' => 'อาทิตย์', 'D' => 'อา.'),
            'Mon' => array('l' => 'จันทร์', 'D' => 'จ.'),
            'Tue' => array('l' => 'อังคาร', 'D' => 'อ.'),
            'Wed' => array('l' => 'พุธ', 'D' => 'พ.'),
            'Thu' => array('l' => 'พฤหัสบดี', 'D' => 'พฤ.'),
            'Fri' => array('l' => 'ศุกร์', 'D' => 'ศ.'),
            'Sat' => array('l' => 'เสาร์', 'D' => 'ส.'),
            'Jan' => array('F' => 'มกราคม', 'M' => 'ม.ค.'),
            'Feb' => array('F' => 'กุมภาพันธ์', 'M' => 'ก.พ.'),
            'Mar' => array('F' => 'มีนาคม', 'M' => 'มี.ค.'),
            'Apr' => array('F' => 'เมษายน', 'M' => 'เม.ย.'),
            'May' => array('F' => 'พฤษภาคม', 'M' => 'พ.ค.'),
            'Jun' => array('F' => 'มิถุนายน', 'M' => 'มิ.ย.'),
            'Jul' => array('F' => 'กรกฎาคม', 'M' => 'ก.ค.'),
            'Aug' => array('F' => 'สิงหาคม', 'M' => 'ส.ค.'),
            'Sep' => array('F' => 'กันยายน', 'M' => 'ก.ย.'),
            'Oct' => array('F' => 'ตุลาคม', 'M' => 'ต.ค.'),
            'Nov' => array('F' => 'พฤศจิกายน', 'M' => 'พ.ย.'),
            'Dec' => array('F' => 'ธันวาคม', 'M' => 'ธ.ค.'));

        $chrarray = str_split($format);
        $keys = 'roYyFMlD';
        $previous = '';
        $newformat = array();

        foreach ($chrarray as $chr) {
            $match = strpos($keys, $chr);

            if ($match !== FALSE && $previous !== '\\') {
                $default = $date->format($chr);

                switch ($chr) {
                    case 'r':
                        $year = strval(intval($date->format('Y')) + 543);
                        $thai = "{$thaidate[$date->format('D')]['D']} d {$thaidate[$date->format('M')]['M']} $year H:i:s O";
                        array_push($newformat, $thai);
                        break;
                    case 'o':
                    case 'Y':
                        $thai = strval(intval($default) + 543);
                        array_push($newformat, $thai);
                        break;
                    case 'y':
                        $thai = substr(strval(intval($default) + 543), -2);
                        array_push($newformat, $thai);
                        break;
                    default:
                        $thai = $thaidate[substr($default, 0, 3)][$chr];
                        array_push($newformat, $thai);
                        break;
                }

                $previous = $chr;
            }
            else {
                array_push($newformat, $chr);
                $previous = $chr;
            }
        }

        return self::thainum($date->format(implode($newformat)));
     }

     private static function thainum($string) {
        $chrarray = str_split($string);
        $num = array('๐', '๑', '๒', '๓', '๔', '๕', '๖', '๗', '๘', '๙');
        $thai = array();

        foreach ($chrarray as $chr) {
            array_push($thai, (is_numeric($chr)) ? $num[intval($chr)] : $chr);
        }

        return implode($thai);
    }

    /*==================Number=====================*/
    public static function number($number) {
        if (!is_numeric($number)) {
            return 'Cann\'t convert.';
        }

        return self::thainum($number);
    }

    public static function number_format($number, $decimals = 0, $dec_point = '.', $thousands_sep = ',') {
        if (!is_numeric($number)) {
            return 'Cann\'t convert.';
        }

        return self::thainum(number_format($number, $decimals, $dec_point, $thousands_sep));
    }

    public static function number_totext($number) {
        if (!is_numeric($number)) {
            return 'Cann\'t convert.';
        }

        $num = explode('.', strval($number));
        $numtext = self::numtotext($num[0]);
        $dectext = (count($num) > 1) ? self::dectotext($num[1]) : '';

        return (count($num) == 1) ? $numtext : "{$numtext}จุด{$dectext}";
    }

    public static function number_tobaht($number) {
        if (!is_numeric($number)) {
            return 'Cann\'t convert.';
        }

        $num = explode('.', strval($number));
        $numtext = self::numtotext($num[0]);
        $dectext = (count($num) > 1) ? (strlen($num[1]) <= 2) ? 'บาท' . self::numtotext((strlen($num[1]) == 1) ? $num[1] . '0' : $num[1]) . 'สตางค์' : 'จุด' . self::dectotext($num[1]) . 'บาท' : '';

        return (count($num) > 1) ? "{$numtext}{$dectext}" : "{$numtext}บาทถ้วน";
    }

    private static function numtotext($string) {
        $num = self::splitstr($string, 6);

        $loop = count($num);
        $thai = array();

        for ($i = 0; $i < $loop; $i++) {
            $numthai = self::numtothai($num[$i]);

            for ($m = 0; $m < $i; $m++) {
                $numthai .= 'ล้าน';
            }

            array_push($thai, $numthai);
        }

        return implode(array_reverse($thai));
    }

    private static function numtothai($string) {
        $len = strlen($string);
        $chrarray = array_reverse(str_split($string));
        $pos = array('', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน');
        $num = array('ศูนย์', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า');
        $thai = array();

        for ($i = 0; $i < $len; $i++) {
            if ($chrarray[$i] != '0') {
                if ($chrarray[$i] == '1' && $i == 0) {
                     array_push($thai, ($len > 1) ? 'เอ็ด' : 'หนึ่ง' . $pos[$i]);
                }
                else if ($chrarray[$i] == '1' && $i == 1) {
                    array_push($thai, $pos[$i]);
                }
                else if ($chrarray[$i] == '2' && $i == 1) {
                    array_push($thai, 'ยี่' . $pos[$i]);
                }
                else {
                    array_push($thai, $num[intval($chrarray[$i])] . $pos[$i]);
                }
            }
        }

        return implode(array_reverse($thai));
    }

    private static function dectotext($string) {
        $chrarray = str_split($string);
        $num = array('ศูนย์', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า');
        $thai = array();

        foreach ($chrarray as $chr) {
            array_push($thai, (is_numeric($chr)) ? $num[intval($chr)] : $chr);
        }

        return implode($thai);
    }

    private static function splitstr($string, $length) {
        $len = strlen($string);
        $floor = floor($len / $length);
        $mod = $len % $length;
        $result = array();

        if ($floor > 0) {
            for($i = 0; $i < $floor; $i++) {
                array_push($result, substr($string, 0 - $length * ($i + 1), $length));
            }
        }

        if ($mod > 0) {
            array_push($result, substr($string, 0, $mod));
        }

        return $result;
    }
}
?>