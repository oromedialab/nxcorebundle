<?php

namespace OroMediaLab\NxCoreBundle\Utils;

class Common
{
    public static function encodeImageToBase64($path)
    {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }

    public static function generateRandomString($length = 10, $integerOnly = false, $shuffle = false)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if (true == $integerOnly) {
            $characters = '0123456789';
        }
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        if (true === $shuffle) {
            return str_shuffle($randomString);
        }
        return $randomString;
    }

    public static function isJson($string)
    {
        json_decode($string);
        $isValid = json_last_error() == JSON_ERROR_NONE;
        if (!$isValid) {
            return false;
        }
        if (!in_array(substr($string, 0, 1), ['['])) {
            return false;
        }
        return true;
    }

    public static function sanitizeCellnumber($value)
    {
        foreach ([' ', '(', ')', '-', '_'] as $char) {
            $value = str_replace($char, '', $value);
        }
        return $value;
    }

    public static function toCamelCase($string, $separator = '_', $capitalizeFirstCharacter = false) 
    {
        $str = str_replace(' ', '', ucwords(str_replace($separator, ' ', $string)));
        if (!$capitalizeFirstCharacter) {
            $str[0] = strtolower($str[0]);
        }
        return $str;
    }

    public static function days()
    {
        return [
            'sat' => 'Saturday',
            'sun' => 'Sunday',
            'mon' => 'Monday',
            'tue' => 'Tuesday',
            'wed' => 'Wednesday',
            'thu' => 'Thursday',
            'fri' => 'Friday'
        ];
    }

    public static function unsetUriParam($variable, $uri)
    {   
        $parseUri = parse_url($uri);
        $arrayUri = array();
        parse_str($parseUri['query'], $arrayUri);
        unset($arrayUri[$variable]);
        $newUri = http_build_query($arrayUri);
        $newUri = $parseUri['scheme'].'://'.$parseUri['host'].$parseUri['path'].'?'.$newUri;
        return $newUri;
    }

    public static function isSerializedString($string)
    {
        return @unserialize($string) !== false;
    }

    public static function slugify($text, string $divider = '-')
    {
        $text = preg_replace('~[^\pL\d]+~u', $divider, $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, $divider);
        $text = preg_replace('~-+~', $divider, $text);
        $text = strtolower($text);
        if (empty($text)) {
            return null;
        }
        return $text;
    }

    public static function calculateDateTimeDifference(\DateTimeInterface $start, \DateTimeInterface $end)
    {
        $interval = $start->diff($end);
        $days = $interval->d;
        $hours = $interval->h;
        $minutes = $interval->i;
        $result = '';
        if ($days > 2) {
            $result .= $days . ' days';
        } elseif ($days > 0) {
            $result .= $days . ' day';
        }
        if ($days < 2) {
            if ($hours > 0) {
                if ($result !== '') {
                    $result .= ', ';
                }
                $result .= $hours . ' hour' . ($hours > 1 ? 's' : '');
            }
            if ($minutes > 0) {
                if ($result !== '') {
                    $result .= ' ';
                }
                $result .= $minutes . ' minute' . ($minutes > 1 ? 's' : '');
            }
        }
        return $result;
    }
}
