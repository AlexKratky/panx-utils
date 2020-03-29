<?php
/**
 * @name PanxFunctions.php
 * @link https://alexkratky.cz                          Author website
 * @link https://panx.eu/docs/                          Documentation
 * @link https://github.com/AlexKratky/panx-framework/  Github Repository
 * @author Alex Kratky <info@alexkratky.cz>
 * @copyright Copyright (c) 2019 Alex Kratky
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @description Custom functions. Part of panx-framework.
 */


use AlexKratky\PanxUtils;
use AlexKratky\Route;

function error($code) {
    PanxUtils::error($code);
}

function redirect($url, $goto = false) {
    PanxUtils::redirect($url, $goto);
}

function aliasredirect($alias, $params = null, $get = null, $goto = false) {
    PanxUtils::redirect(Route::alias($alias, $params, $get), $goto);
}

function goToPrevious() {
    return PanxUtils::goToPrevious();
}

function d($var = 21300, $should_exit = true, $var_name_manual = null) {
    PanxUtils::d($var, $should_exit, $var_name_manual);
}

function html() {
    PanxUtils::html();
}

function json($json) {
    return PanxUtils::json($json);
}

function __($key, $default = false, $replacement = array(), $returnKeyOnFailure = true, $keyFormat = "__%s") {
    return PanxUtils::__($key, $default, $replacement, $returnKeyOnFailure, $keyFormat);
}

function _ga() {
    PanxUtils::_ga();
}

function href($alias, $params = null, $get = null) {
    PanxUtils::href($alias, $params, $get);
}

function l($alias, $params = null, $get = null) {
    PanxUtils::l($alias, $params, $get);
}

function generateRandomString($length = 10) {
    return PanxUtils::generateRandomString($length);
}

function cors() {
    PanxUtils::cors();
}

function isAssoc(array $arr): bool {
    return PanxUtils::isAssoc($arr);
}