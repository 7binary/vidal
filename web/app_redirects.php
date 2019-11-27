<?php

# приостановить кэширование, редиректы
header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

function forceRedirect($url) {
    header('HTTP/2 301 Moved Permanently');
    header('Location: ' . $url);
    exit();
}

# перенаправление всего на HTTPS, за исплючением API мобильных приложений
if ((empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off") && strpos($_SERVER['HTTP_HOST'], 'vidal.ru') !== false) {
    $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    if (strpos($redirect, '/api/') === false && strpos($redirect, '/archive') === false) {
        forceRedirect($redirect);
    }
}

# память, необходимая админке Сонаты
if (strpos($_SERVER['REQUEST_URI'], '/admin/vidal/') !== false) {
    ini_set('memory_limit', -1);
}

# все заглавные запросы, базовый URL переводить в нижний регистр
if (preg_match('#[A-Z]#', $_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/admin/') === false) {
    if (strpos($_SERVER['REQUEST_URI'], '/admin/') === false) {
        $url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $urlParsed = parse_url($url);

        if (preg_match('#[A-Z]#', $urlParsed['path'])) {
            $redirect = 'https://' . $_SERVER['HTTP_HOST'] . strtolower($urlParsed['path']) .
                (empty($urlParsed['query']) ? '' : '?' . $urlParsed['query']);
            forceRedirect($redirect);
        }
    }
}

# двойной слеш переводить в одинарный
if (strpos($_SERVER['REQUEST_URI'], '//') !== false) {
    if (preg_match('#admin/#', $_SERVER['REQUEST_URI']) === false) {
        $redirect = 'https://' . $_SERVER['HTTP_HOST'] . str_replace('//', '/', $_SERVER['REQUEST_URI']);
        forceRedirect($redirect);
    }
}

# избавляемся от нежелательных GET-параметров, перенаправляя на базовый URL
if (!empty($_GET)) {
    $wrongGet = array('a', 'clid', 'version', 'tid', 'from', 'source');
    if (strpos($_SERVER['REQUEST_URI'], '/api/') === false) {
        foreach ($_GET as $key => $value) {
            if (in_array($key, $wrongGet)) {
                $url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                $urlParsed = parse_url($url);
                $redirect = 'https://' . $_SERVER['HTTP_HOST'] . strtolower($urlParsed['path']);
                forceRedirect($redirect);
            }
        }
    }
}