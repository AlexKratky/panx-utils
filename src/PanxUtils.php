<?php
/**
 * @name PanxUtils.php
 * @link https://alexkratky.com                         Author website
 * @link https://panx.eu/docs/                          Documentation
 * @link https://github.com/AlexKratky/panx-utils/      Github Repository
 * @author Alex Kratky <alex@panx.dev>
 * @copyright Copyright (c) 2020 Alex Kratky
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @description Custom functions. Part of panx-framework.
 */

//declare (strict_types = 1);

namespace AlexKratky;

use AlexKratky\Route;
use AlexKratky\Request;
use AlexKratky\Cache;
use AlexKratky\URL;

class PanxUtils {

    private static $debug = false;
    public static function setDebug($debug = true) {self::$debug = $debug;}

    /**
     * @var string|null $template_directory Absolute path to template directory.
     */
    private static $template_directory = null;
    public static function setTemplateDirectory($template_directory = null) {self::$template_directory = $template_directory ?? $_SERVER['DOCUMENT_ROOT'] . "/../template/";}

    private static $appLanguage = 'en';
    public static function setAppLanguage($appLanguage = 'en') {self::$appLanguage = $appLanguage;}
    private static $appLanguageCacheTime = 60;
    public static function setAppLanguageCacheTime($appLanguageCacheTime = 60) {self::$appLanguageCacheTime = $appLanguageCacheTime;}
    private static $missingTranslations = [];
    private static $translationDirectory = null;
    public static function setTranslationDirectory($translationDirectory = null) {self::$translationDirectory = $translationDirectory ?? $_SERVER['DOCUMENT_ROOT']."/../app/resources/lang/";}


    private static $UA_CODE; // GA
    public static function setUACode($UA_CODE = null) {self::$UA_CODE = $UA_CODE;}

    private static $cors = true;
    public static function setCors($cors = true) {self::$cors = $cors;}
    private static $corsOnlyApi = true;
    public static function setCorsOnlyApi($corsOnlyApi = true) {self::$corsOnlyApi = $corsOnlyApi;}

    /**
     * Search for error's template files, include it and stop executing.
     * @param mixed $code The error code.
     */
    public static function error($code) {
        $template_files = Route::searchError($code);
        if($template_files === null) {
            error(500);
        }
        $template_directory = self::$template_directory ?? $_SERVER['DOCUMENT_ROOT'] . "/../template/";
        if (!is_array($template_files)) {
            if(file_exists($template_directory . $template_files)) {
                require $template_directory . $template_files;
            } else {
                error(500);
            }
        } else {
            for ($i = 0; $i < count($template_files); $i++) {
                require $template_directory . $template_files[$i];
            }
        }
        exit();

    }

    /**
     * Redirects to specified URL.
     * @param string $url The URL where will be the user redirected.
     * @param boolean|string $goto If is equal to TRUE, saves to session the current URL. If is equal to FALSE, it will not saves anything to session. Otherwise, it will save string passed to session.
     */
    public static function redirect($url, $goto = false) {
        if(self::$debug) {
            $_SESSION["__redirect__3"] = $_SESSION["__redirect__2"] ?? null; 
            $_SESSION["__redirect__2"] = $_SESSION["__redirect__1"] ?? null;
            $_SESSION["__redirect__1"] = array(
                "current_url" => $_SERVER['REQUEST_URI'],
                "redirect_to" => $url,
                "post_params" => $_POST
            );
        }
        if($goto != false) {
            if($goto) {
                $_SESSION["REDIRECT_TO"] = $_SERVER['REQUEST_URI'];
            } else {
                $_SESSION["REDIRECT_TO"] = $goto;
            }
        }
        if (headers_sent() === false) {
            header('Location: ' . $url);
        } else {
            echo '  <script type="text/javascript">
                        window.location = "'.$url.'"
                    </script>
                    <noscript>
                        <meta http-equiv="refresh" content="0;url='.$url.'.html">
                    </noscript>';

        }

        exit();
    }

    /**
     * Redirects to Route alias.
     * @param string $alias The Route alias where will be the user redirected.
     * @param string|null $params The string of Route params.
     * @param string|null $get The string of GET params. 
     * @param boolean|string $goto If is equal to TRUE, saves to session the current URL. If is equal to FALSE, it will not saves anything to session. Otherwise, it will save string passed to session.
     */
    public static function aliasredirect($alias, $params = null, $get = null, $goto = false) {
        redirect(Route::alias($alias, $params, $get), $goto);
    }

    /**
     * Go to URL before redirect - Specified by $goto in redirect()
     * @return false|redirect If $_SESSION["REDIRECT_TO"] does not contain any URL, it will return FALSE, otherwise the user will be redirected to that URL.
     */
    public static function goToPrevious() {
        if (!empty($_SESSION["REDIRECT_TO"])) {
            $goto = $_SESSION["REDIRECT_TO"];
            unset($_SESSION["REDIRECT_TO"]);
            redirect($goto);
        } else {
            return false;
        }
    }

    /**
     * Dumps the variable.
     * @param mixed $var The variable to be dumped. If the $var is = 23000, dump all defined vars
     * @param boolean $should_exit If it sets to TRUE, the function will stop executing, otherwise it will not. Default is TRUE.
     */
    public static function d($var = 21300, $should_exit = true, $var_name_manual = null) {
        global $_DUMP_CSS_ALREADY_INCLUDED;
        $varName = $var;
        foreach ($GLOBALS as $var_name => $value) {
            if ($value === $var) {
                $varName = $var_name;
            }
        }
        if($var === 21300) $varName = "dump";
        if(!is_string($varName)) {
            $varName = "dump";
        } else {
            $varName = "\$". $varName;
        }
        $varName = $var_name_manual ?? $varName;

        $id = generateRandomString();
        
        if(self::$debug) {
            if(!$_DUMP_CSS_ALREADY_INCLUDED) {
                echo '<link href="https://fonts.googleapis.com/css?family=Poppins&display=swap" rel="stylesheet">';
                $_DUMP_CSS_ALREADY_INCLUDED = true;
            }
            echo "<div style='border: 1px solid #1a1a1d; padding: 5px; border-radius:5px;font-family: \"Poppins\", sans-serif; background: white; position: relative; z-index: 9999; margin-top:5px;' id='{$id}_main'>";
            echo "<div onclick='document.getElementById(\"$id\").style.display = (document.getElementById(\"$id\").style.display == \"block\" ? \"none\" : \"block\")' style='width: 100%; color:red; cursor: pointer;'>$varName > <span onclick='document.getElementById(\"{$id}_main\").style.display = \"none\"' style='float: right; margin-right: 10px; font-size: 18px;'>&times;</span></div>";
            echo "<div id='$id' style='display:none'>";

            if($var === 21300) {
                foreach (get_defined_vars() as $k => $v) {
                    echo "<pre>";
                    echo $k . ": ";
                    highlight_string("<?php\n" . var_export($v, true) . "\n");
                    echo "</pre>";
                }
            } else {
                echo "<pre>";
                highlight_string("<?php\n" . var_export($var, true) . "\n");
                echo "</pre>";
            }
            $args = array();
            for($i = 0; $i < count(debug_backtrace()[1]['args']); $i++) {
                array_push($args, "\"". debug_backtrace()[1]['args'][$i] . "\"");
            }
            echo "<div style='color:black';>";
            echo "<br><br><b>Source</b>: ".  debug_backtrace()[0]['file']."@" . debug_backtrace()[1]['function'] ."(".implode(", ", $args).")";
            echo "<hr>";
            echo "<pre>";
            print_r(debug_backtrace());
            echo "</pre>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
            if($should_exit) exit();
        }
        
    }


    /**
    * Indents a flat JSON string to make it more human-readable.
    * @param string $json The original JSON string to process.
    * @return string Indented version of the original JSON string.
    */
    public static function json($json)
    {
        header('Content-Type: application/json');
        $result = '';
        $pos = 0;
        $strLen = strlen($json);
        $indentStr = "\t";
        $newLine = "\n";

        for ($i = 0; $i < $strLen; $i++) {
            $char = $json[$i];

            if ($char == '"') {
                if (!preg_match('`"(\|"|.)*?"`s', $json, $m, 0, $i)) {
                    return $json;
                }

                $result .= $m[0];
                $i += strLen($m[0]) - 1;
                continue;
            } else if ($char == '}' || $char == ']') {
                $result .= $newLine;
                $pos--;
                $result .= str_repeat($indentStr, $pos);
            }

            $result .= $char;

            if ($char == ',' || $char == '{' || $char == '[') {
                $result .= $newLine;
                if ($char == '{' || $char == '[') {
                    $pos++;
                }

                $result .= str_repeat($indentStr, $pos);
            }
        }

        return $result;
    }


    /**
     * The function will beautify the outputed HTML using https://www.php.net/manual/en/tidy.examples.basic.php
     */
    public static function html() {
        $html = ob_get_clean();

        $config = array(
            'indent' => true,
            'output-xhtml' => true,
            'wrap' => 200);

        // Tidy
        $tidy = new \tidy;
        $tidy->parseString($html, $config, 'utf8');
        $tidy->cleanRepair();

        // Output
        echo $tidy;

    }

    /**
     * Function to obtain translation of key. The language is specified in .config
     * @param string $key The name of key.
     * @param bool $default Determine, if the translation is located in default language files.
     * @param array $replacement Replace %s with the value from replacement.
     * @param string $returnKeyOnFailure Returns the key instead of false, if no data found.
     * @param string $keyFormat The format in which will be key returned if no data found. Use %s for $key. 
     * @return string|false The translation of key or false if the translation does not exists.
     */
    public static function __($key, $default = false, $replacement = array(), $returnKeyOnFailure = true, $keyFormat = "__%s") {
        $lang = strtolower(self::$appLanguage);
        if($lang == "auto") {
            $request = (isset($GLOBALS["request"]) ? $GLOBALS["request"] : new Request());
            $lang = $request->getMostPreferredLanguage();
            if($lang === null) {
                $lang = "en";
            }
            if($lang == "cs") {
                $lang = "cz";
            }
        }
        if(isset($_COOKIE["language"])) {
            $lang = $_COOKIE["language"];
        }
        if($default) {
            $lang = "default_$lang";
        }
        $c = Cache::get("lang_$lang.json", ($default ? 86400 : self::$appLanguageCacheTime));
        if($c !== false) {
            if(strpos($key, ".") !== false) {
                $k = explode(".", $key, 2);
                $t = $c[$k[0]][$k[1]] ?? null;
            } else {
                $t = $c[$key] ?? null;
            }
            if(empty($t)) {
                if(self::$debug) array_push(self::$missingTranslations, array(trim(str_replace("%s", $key, $keyFormat)), debug_backtrace()[count(debug_backtrace())-4]["file"], debug_backtrace()[count(debug_backtrace())-4]["line"]));
                if(!$returnKeyOnFailure)
                    return false;
                return trim(str_replace("%s", $key, $keyFormat));
            } else {
                $x = $t;
                for ($i = 0; $i < count($replacement); $i++) {
                    $x = preg_replace("/%s/", $replacement[$i], $x, 1);
                }
                return trim($x);
            }
        } else {
            self::$translationDirectory = self::$translationDirectory ?? $_SERVER['DOCUMENT_ROOT']."/../app/resources/lang/";
            $translation = array();
            if(!file_exists(self::$translationDirectory . "$lang.lang")) {
                if(!file_exists(self::$translationDirectory . "en.lang")) {
                    if(self::$debug) array_push(self::$missingTranslations, array(trim(str_replace("%s", $key, $keyFormat)), debug_backtrace()[count(debug_backtrace())-4]["file"], debug_backtrace()[count(debug_backtrace())-4]["line"]));
                    if(!$returnKeyOnFailure)
                        return false;
                    return trim(str_replace("%s", $key, $keyFormat));
                } else {
                    $lang = "en";
                }
            }
            $lang_f = self::$translationDirectory . "$lang.lang";
            $lang_f = file_get_contents($lang_f);
            $lang_f = explode(PHP_EOL, $lang_f);
            foreach ($lang_f as $line) {
                if(strpos($line, ":") !== false && strpos($line, "#") !== 0) {
                    $line = explode(": ", $line, 2);
                    if(strpos($line[0], ".") === false) {
                        $translation[$line[0]] = $line[1];
                    } else {
                        //need to use namespace
                        $line[0] = explode(".", $line[0], 2);
                        $translation[$line[0][0]][$line[0][1]] = $line[1];
                    }
                }
            }
            Cache::save("lang_$lang.json", $translation);
            if(strpos($key, ".") !== false) {
                $k = explode(".", $key, 2);
                $t = $translation[$k[0]][$k[1]] ?? null;
            } else {
                $t = $translation[$key] ?? null;
            }
            if(empty($t)) {
                if(self::$debug) array_push(self::$missingTranslations, array(trim(str_replace("%s", $key, $keyFormat)), debug_backtrace()[count(debug_backtrace())-4]["file"], debug_backtrace()[count(debug_backtrace())-4]["line"]));
                if(!$returnKeyOnFailure)
                    return false;
                return trim(str_replace("%s", $key, $keyFormat));
            } else {
                $x = $t;
                for ($i = 0; $i < count($replacement); $i++) {
                    $x = preg_replace("/%s/", $replacement[$i], $x, 1);
                }
                return trim($x);
            }
        }
    }

    /**
    * Prints the JS code with GoogleAnalytics. You need specife the UA code in config.
    */
    public static function _ga() {
        //dump();
            if(!empty(self::$UA_CODE)) {
            $UA = self::$UA_CODE;
            echo "<!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src=\"https://www.googletagmanager.com/gtag/js?id=$UA\"></script>
        <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', '$UA');
        </script>
        ";
            }
    }

    /**
    * Shortcut for Route::alias. Prints: href="{ROUTE}".
    * @param string $alias The alias of the route.
    * @param string $parmas The Route parameters. Write like this param1:param2:[1,2,3]:comment=true
    * @param string $get The GET parameters (eg. ?x=x). Write like this x=true:y=false
    */
    public static function href($alias, $params = null, $get = null) {
        echo 'href="'.Route::alias($alias,$params,$get).'"';
    }

    /**
    * Shortcut for Route::alias. Prints: {ROUTE}
    * @param string $alias The alias of the route.
    * @param string $parmas The Route parameters. Write like this param1:param2:[1,2,3]:comment=true
    * @param string $get The GET parameters (eg. ?x=x). Write like this x=true:y=false
    */
    public static function l($alias, $params = null, $get = null) {
        echo Route::alias($alias,$params,$get);
    }

    /**
    * Returns random string with specified $length.
    */
    public static function generateRandomString($length = 10) {
        return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', (int)ceil($length / strlen($x)))), 1, $length);
    }

    /**
    * Sets the CORS headers by config. E.g. accept any origin on /api/xx/xxxxx
    */
    public static function cors()
    {
        if(!self::$cors) return;
        if(self::$corsOnlyApi) {
            $u = new URL();
            if(!isset($u->getLink()[1]) || $u->getLink()[1] != "api" || $u->getLink()[1] != "rest") {
                return;
            }
        }
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400'); // cache for 1 day
        }

        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
            }

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
            }

            exit(0);
        }
    }

    /**
    * Determine if the array associative.
    */
    public static function isAssoc(array $arr): bool
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

}
