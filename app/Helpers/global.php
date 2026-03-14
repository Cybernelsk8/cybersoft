<?php

use App\Services\Captcha;
use App\Services\Token;

    if(!function_exists('getNestedValue')) {
        function getNestedValue($array, $key) {
            $keys = explode('.', $key);
            foreach ($keys as $innerKey) {
                if (isset($array[$innerKey])) {
                    $array = $array[$innerKey];
                } else {
                    return null;
                }
            }
            return $array;
        }
    }

    if(!function_exists('hasChanged')) {
        function hasChanged($target, $source): bool {

            if ($target === $source) {
                return false;
            }

            if (!is_array($target) || !is_array($source)) {
                return $target !== $source;
            }

            if (count($target) !== count($source)) {
                return true;
            }

            foreach ($target as $key => $value) {

                if (!array_key_exists($key, $source) || hasChanged($value, $source[$key])) {
                    return true;
                }
            }

            return false;
        }
    }


    if(!function_exists('createToken')) {
        function createToken() {
            $token = new Token();
            return $token->createShortHash(uniqid(), 1);
        }
    }


    if(!function_exists('captchaGenerate')) {
        function captchaGenerate(string $text) {
            $captcha = new Captcha();
            return $captcha->generate($text);
        }
    }