<?php

/*
 * Common Helper
 *
 * @author	Agus Heriyanto
 *              Meychel Danius F. Sambuari
 * @copyright	Copyright (c) 2012, Sigma Solusi
 */

// -----------------------------------------------------------------------------

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

if (!function_exists('pr')) {

    function pr($arr) {
        echo '<pre>';
        print_r($arr);
        echo '</pre>';
    }

}

if (!function_exists('is_nominal')) {

    function is_nominal($value) {
        if (is_numeric($value)) {
            if (strpos($value, ".") !== false) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}

if (!function_exists('romanic_number')) {
    function romanic_number($integer, $upcase = true) {
        $table = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
        $return = '';
        while ($integer > 0) {
            foreach ($table as $rom => $arb) {
                if ($integer >= $arb) {
                    $integer -= $arb;
                    $return .= $rom;
                    break;
                }
            }
        }
        return $return;
    }
}


if (!function_exists('convertNullToString')) {
    function convertNullToString($v) {
        return (is_null($v)) ? "" : $v;
    }
}


if (!function_exists('getFirstParagrap')) {
    function getFirstParagrap($string) {
        $string = substr($string, 0, strpos($string, "</p>") + 4);
        return $string;
    }
}

if (!function_exists('startsWith')) {

    function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }

}

if (!function_exists('endsWith')) {

    function endsWith($haystack, $needle) {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }

}

if (!function_exists('bulat_rp')) {

    function bulat_rp($uang) {
        $akhir = 0;
        $sisa_angka = substr($uang, -2);
        if ($sisa_angka != '00') {
            if ($sisa_angka < 100) {
                $akhir = $uang + (100 - $sisa_angka);
                return $akhir;
            } else {
                return $uang;
            }
        } else {
            return $uang;
        }
    }

}

if (!function_exists('bulat_rp_bawah')) {
    if (!function_exists('bulat_rp_bawah')) {

        function bulat_rp_bawah($uang) {
            $akhir = 0;
            $sisa_angka = substr($uang, -2);
            if ($sisa_angka != '00') {
                $akhir = $uang - $sisa_angka;
                return $akhir;
            } else {
                return $uang;
            }
        }

    }
}

if (!function_exists('convert_month')) {

    function convert_month($month, $lang = 'en') {
        $month = (int) $month;
        switch ($lang) {
            case 'id':
                $arr_month = array('Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'Nopember', 'Desember');
                break;

            default:
                $arr_month = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
                break;
        }
        $month_converted = $arr_month[$month - 1];

        return $month_converted;
    }

}

if (!function_exists('convert_date')) {

    function convert_date($date, $type = 'num', $format = '.', $lang = 'en') {
        if (!empty($date)) {
            $date = substr($date, 0, 10);
            if ($type == 'num') {
                $date_converted = str_replace('-', $format, $date);
            } else {
                $year = substr($date, 0, 4);
                $month = substr($date, 5, 2);
                $month = convert_month($month, $lang);
                $day = substr($date, 8, 2);

                $date_converted = $day . ' ' . $month . ' ' . $year;
            }
        } else {
            $date_converted = '-';
        }
        return $date_converted;
    }

}

if (!function_exists('convert_datetime')) {

    function convert_datetime($date, $type = 'num', $formatdate = '.', $formattime = ':', $lang = 'en') {

        if (!empty($date)) {
            if ($type == 'num') {
                $date_converted = str_replace('-', $formatdate, str_replace(':', $formattime, $date));
            } else {
                $year = substr($date, 0, 4);
                $month = substr($date, 5, 2);
                $month = convert_month($month, $lang);
                $day = substr($date, 8, 2);
                $time = strlen($date) > 10 ? substr($date, 11, 8) : '';
                $time = str_replace(':', $formattime, $time);

                $date_converted = $day . ' ' . $month . ' ' . $year . ' ' . $time;
            }
        } else {
            $date_converted = '-';
        }
        return $date_converted;
    }

}


if (!function_exists('get_filesize')) {

    function get_filesize($file) {
        $bytes = array("B", "KB", "MB", "GB", "TB", "PB");
        $file_with_path = $file;
        $file_with_path;
        // replace (possible) double slashes with a single one
        $file_with_path = str_replace("///", "/", $file_with_path);
        $file_with_path = str_replace("//", "/", $file_with_path);
        $size = @filesize($file_with_path);
        $i = 0;

        //divide the filesize (in bytes) with 1024 to get "bigger" bytes
        while ($size >= 1024) {
            $size = $size / 1024;
            $i++;
        }

        // you can change this number if you like (for more precision)
        if ($i > 1) {
            return round($size, 1) . " " . $bytes[$i];
        } else {
            return round($size, 0) . " " . $bytes[$i];
        }
    }

}
if (!function_exists('terbilang')) {

    function terbilang($x) {
        $abil = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
        if ($x < 12)
            return " " . $abil[$x];
        elseif ($x < 20)
            return terbilang($x - 10) . "belas";
        elseif ($x < 100)
            return terbilang($x / 10) . " puluh" . terbilang($x % 10);
        elseif ($x < 200)
            return " seratus" . Terbilang($x - 100);
        elseif ($x < 1000)
            return terbilang($x / 100) . " ratus" . terbilang($x % 100);
        elseif ($x < 2000)
            return " seribu" . terbilang($x - 1000);
        elseif ($x < 1000000)
            return terbilang($x / 1000) . " ribu" . terbilang($x % 1000);
        elseif ($x < 1000000000)
            return terbilang($x / 1000000) . " juta" . terbilang($x % 1000000);
    }

}
if (!function_exists('make_thumb_admin')) {

    function make_thumb_admin($src, $dest, $desired_width, $desired_height) {

        /* read the source image */
        $source_image = imagecreatefromjpeg($src);
        $width = imagesx($source_image);
        $height = imagesy($source_image);

        /* create a new, "virtual" image */
        $virtual_image = imagecreatetruecolor($desired_width, $desired_height);

        /* copy source image at a resized size */
        imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);

        /* create the physical thumbnail image to its destination */
        imagejpeg($virtual_image, $dest);
    }

}

if (!function_exists('makeThumbnails')) {

    function makeThumbnails($updir, $img) {
        $thumbnail_width = 80;
        $thumbnail_height = 80;
        $thumb_beforeword = "thumb";
        $arr_image_details = getimagesize("$updir" . "$img"); // pass id to thumb name
        $original_width = $arr_image_details[0];
        $original_height = $arr_image_details[1];
        if ($original_width > $original_height) {
            $new_width = $thumbnail_width;
            $new_height = intval($original_height * $new_width / $original_width);
        } else {
            $new_height = $thumbnail_height;
            $new_width = intval($original_width * $new_height / $original_height);
        }
        $dest_x = intval(($thumbnail_width - $new_width) / 2);
        $dest_y = intval(($thumbnail_height - $new_height) / 2);
        if ($arr_image_details[2] == 1) {
            $imgt = "ImageGIF";
            $imgcreatefrom = "ImageCreateFromGIF";
        }
        if ($arr_image_details[2] == 2) {
            $imgt = "ImageJPEG";
            $imgcreatefrom = "ImageCreateFromJPEG";
        }
        if ($arr_image_details[2] == 3) {
            $imgt = "ImagePNG";
            $imgcreatefrom = "ImageCreateFromPNG";
        }
        if ($imgt) {
            $old_image = $imgcreatefrom("$updir" . "$img");
            $new_image = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
            imagecopyresized($new_image, $old_image, $dest_x, $dest_y, 0, 0, $new_width, $new_height, $original_width, $original_height);
            $imgt($new_image, "$updir" . "$thumb_beforeword" . "$img");
        }
    }

}

if (!function_exists('resize_crop_image')) {

    function resize_crop_image($max_width, $max_height, $source_file, $dst_dir, $quality = 80) {
        $imgsize = getimagesize($source_file);
        $width = $imgsize[0];
        $height = $imgsize[1];
        $mime = $imgsize['mime'];

        switch ($mime) {
            case 'image/gif':
                $image_create = "imagecreatefromgif";
                $image = "imagegif";
                break;

            case 'image/png':
                $image_create = "imagecreatefrompng";
                $image = "imagepng";
                $quality = 7;
                break;

            case 'image/jpeg':
                $image_create = "imagecreatefromjpeg";
                $image = "imagejpeg";
                $quality = 80;
                break;

            default:
                return false;
                break;
        }

        $dst_img = imagecreatetruecolor($max_width, $max_height);
        if ($mime == 'image/png') {
            imagealphablending($dst_img, false);
            imagesavealpha($dst_img, true);
            $transparent = imagecolorallocatealpha($dst_img, 255, 255, 255, 127);
            imagefilledrectangle($dst_img, 0, 0, $max_width, $max_height, $transparent);
        }
        $src_img = $image_create($source_file);

        $width_new = $height * $max_width / $max_height;
        $height_new = $width * $max_height / $max_width;
        //if the new width is greater than the actual width of the image, then the height is too large and the rest cut off, or vice versa
        if ($width_new > $width) {
            //cut point by height
            $h_point = (($height - $height_new) / 2);
            //copy image
            imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $max_width, $max_height, $width, $height_new);
        } else {
            //cut point by width
            $w_point = (($width - $width_new) / 2);
            imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $max_width, $max_height, $width_new, $height);
        }

        $image($dst_img, $dst_dir, $quality);

        if ($dst_img) {
            imagedestroy($dst_img);
        }
            
        if ($src_img) {
            imagedestroy($src_img);
        }
            
    }

}

if (!function_exists('getimagesizefromstring')) {
      function getimagesizefromstring($string_data)
      {
         $uri = 'data://application/octet-stream;base64,'  . base64_encode($string_data);
         return getimagesize($uri);
      }
}

if (!function_exists('resize_crop_image_blob_gif')) {

    function resize_crop_image_blob_gif($max_width, $max_height, $source_file, $dst_dir, $quality = 80) {
        $imgsize = getimagesize($source_file);
        $width = $imgsize[0];
        $height = $imgsize[1];

        $dst_img = imagecreatetruecolor($max_width, $max_height);
        $image = "imagegif";
        $src_img = imagecreatefromgif($source_file);


        $width_new = $height * $max_width / $max_height;
        $height_new = $width * $max_height / $max_width;
        //if the new width is greater than the actual width of the image, then the height is too large and the rest cut off, or vice versa
        if ($width_new > $width) {
            //cut point by height
            $h_point = (($height - $height_new) / 2);
            //copy image
            imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $max_width, $max_height, $width, $height_new);
        } else {
            //cut point by width
            $w_point = (($width - $width_new) / 2);
            imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $max_width, $max_height, $width_new, $height);
        }

        $image($dst_img, $dst_dir, $quality);

        if ($dst_img) {
            imagedestroy($dst_img);
        }
            
        if ($src_img) {
            imagedestroy($src_img);
        }
    }
}

if (!function_exists('resize_crop_image_blob_png')) {

    function resize_crop_image_blob_png($max_width, $max_height, $source_file, $dst_dir, $quality = 80) {
        $imgsize = getimagesize($source_file);
        $width = $imgsize[0];
        $height = $imgsize[1];

        $dst_img = imagecreatetruecolor($max_width, $max_height);
        $image = "imagepng";
        $quality = 7;
        $src_img = imagecreatefrompng($source_file);


        $width_new = $height * $max_width / $max_height;
        $height_new = $width * $max_height / $max_width;
        //if the new width is greater than the actual width of the image, then the height is too large and the rest cut off, or vice versa
        if ($width_new > $width) {
            //cut point by height
            $h_point = (($height - $height_new) / 2);
            //copy image
            imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $max_width, $max_height, $width, $height_new);
        } else {
            //cut point by width
            $w_point = (($width - $width_new) / 2);
            imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $max_width, $max_height, $width_new, $height);
        }

        $image($dst_img, $dst_dir, $quality);

        if ($dst_img) {
            imagedestroy($dst_img);
        }
            
        if ($src_img) {
            imagedestroy($src_img);
        }
    }
}

if (!function_exists('resize_crop_image_blob_jpeg')) {

    function resize_crop_image_blob_jpeg($max_width, $max_height, $source_file, $dst_dir, $quality = 80) {
        $imgsize = getimagesize($source_file);
        $width = $imgsize[0];
        $height = $imgsize[1];

        $dst_img = imagecreatetruecolor($max_width, $max_height);
        $src_img = imagecreatefromjpeg($source_file);
        $image = "imagejpeg";

        $width_new = $height * $max_width / $max_height;
        $height_new = $width * $max_height / $max_width;
        //if the new width is greater than the actual width of the image, then the height is too large and the rest cut off, or vice versa
        if ($width_new > $width) {
            //cut point by height
            $h_point = (($height - $height_new) / 2);
            //copy image
            imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $max_width, $max_height, $width, $height_new);
        } else {
            //cut point by width
            $w_point = (($width - $width_new) / 2);
            imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $max_width, $max_height, $width_new, $height);
        }

        $image($dst_img, $dst_dir, $quality);

        if ($dst_img) {
            imagedestroy($dst_img);
        }
            
        if ($src_img) {
            imagedestroy($src_img);
        }
    }
}

if (!function_exists('validate_date')) {

    function validate_date($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

}

if (!function_exists('pageGenerate')) {

    function page_generate($total, $pagenum, $limit)
    {
        $total_page = ceil($total / $limit);

    //------------- Prev page
        $prev = $pagenum - 1;
        if ($prev < 1) {
            $prev = 0;
        }
    //------------------------

    //------------- Next page
        $next = $pagenum + 1;
        if ($next > $total_page) {
            $next = 0;
        }
    //----------------------

        $from = 1;
        $to = $total_page;

        $to_page = $pagenum - 2;
        if ($to_page > 0) {
            $from = $to_page;
        }

        if ($total_page >= 5) {
            if ($total_page > 0) {
                $to = 5 + $to_page;
                if ($to > $total_page) {
                    $to = $total_page;
                }
            } else {
                $to = 5;
            }
        }

    #looping kotak pagination
        $firstpage_istrue = false;
        $lastpage_istrue = false;
        if ($total_page <= 1) {
            $detail = [];
        } else {
            for ($i = $from; $i <= $to; $i++) {
                $detail[] = $i;
            }
            if ($from != 1) {
                $firstpage_istrue = true;
            }
            if ($to != $total_page) {
                $lastpage_istrue = true;
            }
        }

        $total_display = $limit;
        if ($next == 0) {
            $total_display = $total % $limit;
        }

        $pagination = array(
            'total_data' => $total,
            'total_page' => $total_page,
            'total_display' => $total_display,
            'first_page' => $firstpage_istrue,
            'last_page' => $lastpage_istrue,
            'prev' => $prev,
            'current' => $pagenum,
            'next' => $next,
            'detail' => $detail
            // 'detail' => json_encode($detail)
        );

        return $pagination;
    }

}

if (!function_exists('searchInput')) {
    function search_input($where_filter = array(), $field_allowed = array())
    {
        $sql_search = '';

        if ($where_filter != null) {
            foreach ($where_filter as $row) {
                $type = isset($row['type']) ? $row['type'] : '';
                $field = isset($row['field']) ? $row['field'] : '';
                $value = isset($row['value']) ? $row['value'] : '';
                $comparison = isset($row['comparison']) ? $row['comparison'] : '';

                if (!in_array($field, $field_allowed)) {
                    $field = '';
                }

                if ($field == '' || $value == '') {
                    $type = '';
                }

                switch ($type) {
                    case 'string':
                        $arr_allowed = array('=', '<', '>');
                        if (!in_array($comparison, $arr_allowed)) {
                            $comparison = '=';
                        }
                        switch ($comparison) {
                            case '=':
                                $sql_search .= " AND " . $field . " = '" . $value . "'";
                                break;
                            case '<':
                                $sql_search .= " AND " . $field . " LIKE '" . $value . "%'";
                                break;
                            case '>':
                                $sql_search .= " AND " . $field . " LIKE '%" . $value . "'";
                                break;
                        }
                        break;
                    case 'numeric':
                        if (is_numeric($value)) {
                            $arr_allowed = array('=', '<', '>', '<=', '>=', '<>');
                            if (!in_array($comparison, $arr_allowed)) {
                                $comparison = '=';
                            }
                            $sql_search .= " AND " . $field . " " . $comparison . " " . $value;
                        }
                        break;
                    case 'list':
                        if (strstr($value, '::')) {
                            $arr_allowed = array('yes', 'no', 'bet');
                            if (!in_array($comparison, $arr_allowed)) {
                                $comparison = 'yes';
                            }
                            $fi = explode('::', $value);
                            for ($q = 0; $q < count($fi); $q++) {
                                $fi[$q] = "'" . $fi[$q] . "'";
                            }
                            $value = implode(',', $fi);
                            if ($comparison == 'yes') {
                                $sql_search .= " AND " . $field . " IN (" . $value . ")";
                            }
                            if ($comparison == 'no') {
                                $sql_search .= " AND " . $field . " NOT IN (" . $value . ")";
                            }
                            if ($comparison == 'bet') {
                                $sql_search .= " AND " . $field . " BETWEEN ". $fi[0] . " AND " . $fi[1];
                            }
                        } else {
                            $sql_search .= " AND " . $field . " = '" . $value . "'";
                        }
                        break;
                    case 'date':
                        if (endsWith($field, 'date') || endsWith($field, 'datetime')) {
                            $value1 = '';
                            $value2 = '';
                            if (strstr($value, '::')) {
                                $date_value = explode('::', $value);
                                $value1 = $date_value[0];
                                $value2 = $date_value[1];
                            } else {
                                $value1 = $value;
                            }

                            if (endsWith($field, 'datetime')) {
                                $field = 'date(' . $field . ')';
                            }

                            $arr_allowed = array('=', '<', '>', '<=', '>=', '<>', 'bet');
                            if (!in_array($comparison, $arr_allowed)) {
                                $comparison = '=';
                            }
                            if ($comparison == 'bet') {
                                if (validate_date($value1) && validate_date($value2)) {
                                    $sql_search .= " AND " . $field . " BETWEEN '" . $value1 . "' AND '" . $value2 . "'";
                                }
                            } else {
                                if (validate_date($value1)) {
                                    $sql_search .= " AND " . $field . " " . $comparison . " '" . $value1 . "'";
                                }
                            }
                        }
                        break;
                }
            }
        }

        return $sql_search;
    }
}

if (!function_exists('slug')) {
    function slug($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

        // trim
        $text = trim($text, '-');

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}