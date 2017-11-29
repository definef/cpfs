<?php
namespace helper;

abstract class Library
{
    /**
     * @brief 쿠키 네임을 md5로 암호화 해서 생성함
     * @author hheo (hheo@cozmoworks.com)
     * @param string $name 쿠키 네임
     * @param string $value 값
     * @param string $expire strtotime 형식
     * @param string $path 페이지 경로
     * @param string $domain 도메인
     * @param bool $secure HTTPS 연결이라면 true
     * @param bool $httponly http 접속 이외에 접근 불가 쿠키
     * @return bool
     * @see control/user/Login-check.php
     * @see /app/core.php
     * @see setcookie()
     */
    public static function set_cookie($name, $value, $expire = '+1 month', $path = '/', $domain = DOMAIN, $secure = false, $httponly = true)
    {
        $name = md5($name);

        if ($value === null) {
            unset($_COOKIE[$name]);
            return setcookie($name, null, -1, $path, $domain, $secure, $httponly);
        }

        if ($_SERVER['HTTPS']) {
            $secure = true;
        }

        return setcookie($name, base64_encode($value), strtotime($expire), $path, $domain, $secure, $httponly);
    }

    /**
     * @brief md5로 암호화 해서 생성된 쿠키 네임의 값을 찾음
     * @author hheo (hheo@cozmoworks.com)
     * @param string $name 쿠키 네임
     * @return string | null
     * @see control/user/Login-check.php
     * @see /app/core.php
     * @see set_cookie()
     */
    public static function get_cookie($name)
    {
        $name = md5($name);

        if (array_key_exists($name, $_COOKIE)) {
            return base64_decode($_COOKIE[$name]);
        } else {
            return null;
        }
    }

    /**
     * @brief AGENT를 통해 모바일 기기 여부 판단
     * @author hheo (hheo@cozmoworks.com)
     * @return bool
     * @see /app/core.php
     */
    public static function is_mobile()
    {
        return preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * @brief AGENT를 통해 윈도우 여부 판단
     * @author hheo (hheo@cozmoworks.com)
     * @return bool
     * @see /app/core.php
     */
    public static function is_windows()
    {
        return preg_match('/windows/i', $_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * @brief 날짜를 입력받아 해당 날짜의 요일을 반환
     * @author hheo (hheo@cozmoworks.com)
     * @param string $date
     * @return string $yoil
     */
    public static function get_yoil($date)
    {
        $w = date('w', strtotime($date));
        $yoil = ['일','월','화','수','목','금','토'];

        return $yoil[$w];
    }

    /**
     * @brief 날짜와 날짜 사이에 있는 모든 날짜를 반환
     * @author hheo (hheo@cozmoworks.com)
     * @param string $first strtotime 형식 시작일
     * @param string $last strtotime 형식 종료일
     * @param string $step strtotime 형식
     * @param string $output_format 출력 형식
     * @return string $dates
     */
    public static function date_range($first, $last, $step = '+1 day', $output_format = 'Y-m-d')
    {
        $dates = array();
        $current = strtotime($first);
        $last = strtotime($last);
        while ($current <= $last) {
            $dates[] = date($output_format, $current);
            $current = strtotime($step, $current);
        }
        return $dates;
    }

    /**
     * @brief 시간 입력받아 현재로 부터 어떤 시점이었는지 한글로 표현
     * @author hheo (hheo@cozmoworks.com)
     * @param string $datetime
     * @param bool $today
     * @return string
     */
    public static function date_str($datetime, $today = false)
    {
        $diff = TIME - strtotime($datetime);
        if ($diff < 60) {
            if ($today === true) {
                return '오늘';
            }
            return $diff . '초 전';
        } else if (60 <= $diff && $diff < 3600) {
            if ($today === true) {
                return '오늘';
            }
            return round($diff / 60) . '분 전';
        } else if (3600 <= $diff && $diff < 86400) {
            if ($today === true) {
                return '오늘';
            }
            return round($diff / 3600) . '시간 전';
        } else if (86400 <= $diff && $diff < (86400 * 2)) {
            return '어제';
        } else {
            return round($diff / 86400) . '일 전';
        }
    }

    /**
     * @brief 문자열을 원하는 길이만큼 표기하고 뒤는 흐리기 mb_strimwidth 함수 쉽게 래핑
     * @author hheo (hheo@cozmoworks.com)
     * @param string $str
     * @param int $len
     * @param string $suffix
     * @return string
     */
    public static function cut_str($str, $len, $suffix = '…')
    {
        return mb_strimwidth($str, 0, $len, $suffix, 'utf-8');
    }

    /**
     * @brief GET, POST로 받은 변수 자동으로 filter_var_array적용
     * @author hheo (hheo@cozmoworks.com)
     * @param array $filters
     * @return array $vars
     */
    public static function get_vars($filters)
    {
        $args = $vars = [];

        foreach($filters as $key => $value) {
            $args[$key] = isset($_REQUEST[$key]) ? $_REQUEST[$key] : null;
        }

        $vars = filter_var_array($args, $filters);
        return $vars;
    }

    /**
     * @brief 휴대전화 번호 유효성 체크
     * @author hheo (hheo@cozmoworks.com)
     * @param string $hp
     * @return bool
     */
    public static function valid_hp_number($hp)
    {
        return !(preg_match('/^01[0-9]{8,9}$/', preg_replace('/[^0-9]/', '', $hp)));
    }

    /**
     * @brief 휴대전화 번호에 하이픈 자동 부여
     * @author hheo (hheo@cozmoworks.com)
     * @param string $hp
     * @return string
     */
    public static function get_hp_number($hp)
    {
        return preg_replace('/([0-9]{3})([0-9]{3,4})([0-9]{4})$/', '\\1-\\2-\\3', preg_replace('/[^0-9]/', '', $hp));
    }

    /**
     * @brief 파일명에 특수 문자 제거
     * @author hheo (hheo@cozmoworks.com)
     * @param string $name
     * @return string
     */
    public static function get_filename($name)
    {
        return preg_replace('/["\'<>=#&!%\\\\(\)\*\+\?]/', '', $name);
    }

    /**
     * @brief 파일사이즈 문자열로 얻기
     * @author hheo (hheo@cozmoworks.com)
     * @param int $size
     * @return string
     */
    public static function get_filesize($size)
    {
        $kb = round(($size / 1024), 1) .'KB';
        $mb = round(($size / 1048576), 1) .'MB';
        $gb = round(($size / 1073741824), 1) .'GB';

        if ($gb >= 1) {
            return $gb;
        } else if ($mb >= 1) {
            return $mb;
        } else if ($kb >= 1) {
            return $kb;
        } else {
            return $size.'B';
        }
    }

    public static function unique_multidim_array($array, $key)
    {
        $temp_array = [];
        $i = 0;
        $key_array = [];

        foreach($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i += 1;
        }

        return $temp_array;
    }

    public static function setState()
    {
        $_SESSION['state'] = md5(microtime() . mt_rand());

        return $_SESSION['state'] ?? false;
    }
}