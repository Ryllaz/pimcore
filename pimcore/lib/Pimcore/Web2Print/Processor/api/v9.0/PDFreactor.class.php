<?php
/**
 * RealObjects PDFreactor PHP Wrapper version 4
 * http://www.pdfreactor.com
 * 
 * Released under the following license:
 * 
 * The MIT License (MIT)
 * 
 * Copyright (c) 2015-2017 RealObjects GmbH
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

class PDFreactor {
    var $url;
    var $headers;
    var $cookies;
    function __construct($url = "http://localhost:9423/service/rest") {
        $this->url = $url;
        if ($url == null) {
            $this->url = "http://localhost:9423/service/rest";
        }
        $this->headers = array();
        $this->cookies = array();
        $this->apiKey = null;
        $this->stickyMap = array();
    }
    function convert($config) {
        $url = $this->url ."/convert.json";
        if (!is_null($this->apiKey)) {
            $url .= "?apiKey=" . $this->apiKey;
        }
        if (!is_null($config)) {
            $config['clientName'] = "PHP";
            $config['clientVersion'] = PDFreactor::VERSION;
        }
        $headerStr = '';
        $cookieStr = '';
        if (!empty($this->headers)) {
            foreach ($this->headers as $name => $value) {
                $lcName = strtolower($name);
                if ($lcName !== "user-agent" && $lcName !== "content-type" && $lcName !== "range") {
                    $headerStr .= $name . ": " . $value . "\r\n";
                }
            }
        }
        if (!empty($this->cookies)) {
            foreach ($this->cookies as $name => $value) {
                $cookieStr .= $name . "=" . $value . "; ";
            }
        }
        $headerStr .= "Content-Type: application/json\r\n";
        $headerStr .= "User-Agent: PDFreactor PHP API v4\r\n";
        $headerStr .= "X-RO-User-Agent: PDFreactor PHP API v4\r\n";
        if (!empty($this->cookies) || !empty($cookieStr)) {
            $headerStr .= "Cookie: " . substr($cookieStr, 0, -2);
        }
        $options = array(
            'http' => array(
                'header'  => $headerStr,
                'follow_location' => false,
                'max_redirects' => 0,
                'method'  => 'POST',
                'content' => json_encode($config),
                'ignore_errors' => true
            ),
        );
        $context = stream_context_create($options);
        $result = null;
        $errorMode = true;
        $rh = fopen($url, false, false, $context);
        if (!isset($http_response_header)) {
            $lastError = error_get_last();
            throw new \Exception('Error connecting to PDFreactor Web Service at ' . $this->url . '. Please make sure the PDFreactor Web Service is installed and running (Error: ' . $lastError['message'] . ')');
        }
        $status = intval(substr($http_response_header[0], 9, 3));
        if ($status >= 200 && $status <= 204) {
            $errorMode = false;
        }
        $result = stream_get_contents($rh);
        fclose($rh);
        if ($status == 422) {
            throw new \Exception(json_decode($result)->error);
        } else if ($status == 400) {
            throw new \Exception('Invalid client data. '.json_decode($result)->error);
        } else if ($status == 404) {
            throw new \Exception('Error connecting to PDFreactor Web Service at ' . $this->url . '. Please make sure the PDFreactor Web Service is installed and running '.json_decode($result)->error);
        } else if ($status == 403) {
            throw new \Exception('Request rejected. '.json_decode($result)->error);
        } else if ($status == 401) {
            throw new \Exception('Unauthorized. '.json_decode($result)->error);
        } else if ($status == 413) {
            throw new \Exception('The configuration is too large to process.');
        } else if ($status == 500) {
            throw new \Exception(json_decode($result)->error);
        } else if ($status == 503) {
            throw new \Exception('PDFreactor Web Service is unavailable.');
        } else if ($status > 400) {
            throw new \Exception('PDFreactor Web Service error (status: ' . $status . ').');
        }
        return json_decode($result);
    }
    function convertAsBinary($config, $wh = null) {
        $url = $this->url ."/convert.bin";
        if (!is_null($this->apiKey)) {
            $url .= "?apiKey=" . $this->apiKey;
        }
        if (!is_null($config)) {
            $config['clientName'] = "PHP";
            $config['clientVersion'] = PDFreactor::VERSION;
        }
        $headerStr = '';
        $cookieStr = '';
        if (!empty($this->headers)) {
            foreach ($this->headers as $name => $value) {
                $lcName = strtolower($name);
                if ($lcName !== "user-agent" && $lcName !== "content-type" && $lcName !== "range") {
                    $headerStr .= $name . ": " . $value . "\r\n";
                }
            }
        }
        if (!empty($this->cookies)) {
            foreach ($this->cookies as $name => $value) {
                $cookieStr .= $name . "=" . $value . "; ";
            }
        }
        $headerStr .= "Content-Type: application/json\r\n";
        $headerStr .= "User-Agent: PDFreactor PHP API v4\r\n";
        $headerStr .= "X-RO-User-Agent: PDFreactor PHP API v4\r\n";
        if (!empty($this->cookies) || !empty($cookieStr)) {
            $headerStr .= "Cookie: " . substr($cookieStr, 0, -2);
        }
        $options = array(
            'http' => array(
                'header'  => $headerStr,
                'follow_location' => false,
                'max_redirects' => 0,
                'method'  => 'POST',
                'content' => json_encode($config),
                'ignore_errors' => true
            ),
        );
        $context = stream_context_create($options);
        $result = null;
        $errorMode = true;
        $rh = fopen($url, false, false, $context);
        if (!isset($http_response_header)) {
            $lastError = error_get_last();
            throw new \Exception('Error connecting to PDFreactor Web Service at ' . $this->url . '. Please make sure the PDFreactor Web Service is installed and running (Error: ' . $lastError['message'] . ')');
        }
        $status = intval(substr($http_response_header[0], 9, 3));
        if ($status >= 200 && $status <= 204) {
            $errorMode = false;
        }
        if ($errorMode || $wh == null) {
            $result = stream_get_contents($rh);
            fclose($rh);
        }
        if ($status == 422) {
            throw new \Exception($result);
        } else if ($status == 400) {
            throw new \Exception('Invalid client data. '.$result);
        } else if ($status == 404) {
            throw new \Exception('Error connecting to PDFreactor Web Service at ' . $this->url . '. Please make sure the PDFreactor Web Service is installed and running '.$result);
        } else if ($status == 403) {
            throw new \Exception('Request rejected. '.$result);
        } else if ($status == 401) {
            throw new \Exception('Unauthorized. '.$result);
        } else if ($status == 413) {
            throw new \Exception('The configuration is too large to process.');
        } else if ($status == 500) {
            throw new \Exception($result);
        } else if ($status == 503) {
            throw new \Exception('PDFreactor Web Service is unavailable.');
        } else if ($status > 400) {
            throw new \Exception('PDFreactor Web Service error (status: ' . $status . ').');
        }
        if (!$errorMode && $wh != null) {
            while (!feof($rh)) {
                if (fwrite($wh, fread($rh, 1024)) === FALSE) {
                    return null;
                }
            }
            fclose($rh);
            fclose($wh);
        }
        return $result;
    }
    function convertAsync($config) {
        $documentId = null;
        $url = $this->url ."/convert/async.json";
        if (!is_null($this->apiKey)) {
            $url .= "?apiKey=" . $this->apiKey;
        }
        if (!is_null($config)) {
            $config['clientName'] = "PHP";
            $config['clientVersion'] = PDFreactor::VERSION;
        }
        $headerStr = '';
        $cookieStr = '';
        if (!empty($this->headers)) {
            foreach ($this->headers as $name => $value) {
                $lcName = strtolower($name);
                if ($lcName !== "user-agent" && $lcName !== "content-type" && $lcName !== "range") {
                    $headerStr .= $name . ": " . $value . "\r\n";
                }
            }
        }
        if (!empty($this->cookies)) {
            foreach ($this->cookies as $name => $value) {
                $cookieStr .= $name . "=" . $value . "; ";
            }
        }
        $headerStr .= "Content-Type: application/json\r\n";
        $headerStr .= "User-Agent: PDFreactor PHP API v4\r\n";
        $headerStr .= "X-RO-User-Agent: PDFreactor PHP API v4\r\n";
        if (!empty($this->cookies) || !empty($cookieStr)) {
            $headerStr .= "Cookie: " . substr($cookieStr, 0, -2);
        }
        $options = array(
            'http' => array(
                'header'  => $headerStr,
                'follow_location' => false,
                'max_redirects' => 0,
                'method'  => 'POST',
                'content' => json_encode($config),
                'ignore_errors' => true
            ),
        );
        $context = stream_context_create($options);
        $result = null;
        $errorMode = true;
        $rh = fopen($url, false, false, $context);
        if (!isset($http_response_header)) {
            $lastError = error_get_last();
            throw new \Exception('Error connecting to PDFreactor Web Service at ' . $this->url . '. Please make sure the PDFreactor Web Service is installed and running (Error: ' . $lastError['message'] . ')');
        }
        $status = intval(substr($http_response_header[0], 9, 3));
        if ($status >= 200 && $status <= 204) {
            $errorMode = false;
        }
        $result = stream_get_contents($rh);
        fclose($rh);
        if ($status == 422) {
            throw new \Exception(json_decode($result)->error);
        } else if ($status == 400) {
            throw new \Exception('Invalid client data. '.json_decode($result)->error);
        } else if ($status == 404) {
            throw new \Exception('Error connecting to PDFreactor Web Service at ' . $this->url . '. Please make sure the PDFreactor Web Service is installed and running '.json_decode($result)->error);
        } else if ($status == 403) {
            throw new \Exception('Request rejected. '.json_decode($result)->error);
        } else if ($status == 401) {
            throw new \Exception('Unauthorized. '.json_decode($result)->error);
        } else if ($status == 413) {
            throw new \Exception('The configuration is too large to process.');
        } else if ($status == 500) {
            throw new \Exception(json_decode($result)->error);
        } else if ($status == 503) {
            throw new \Exception('PDFreactor Web Service is unavailable.');
        } else if ($status > 400) {
            throw new \Exception('PDFreactor Web Service error (status: ' . $status . ').');
        }
        foreach ($http_response_header as $header) {
            $t = explode(':', $header, 2);
            if (isset($t[1])) {
                $headerName = trim($t[0]);
                if ($headerName == "Location") {
                    $documentId = trim(substr($t[1], strrpos($t[1], "/") + 1));
                }
            }
            if (preg_match('/^Set-Cookie:\s*([^;]+)/', $header, $matches)) {;
                parse_str($matches[1], $tmp);
                $keepDocument = false;
                if (isset($config->{'keepDocument'})) {
                    $keepDocument = $config->{'keepDocument'};
                }
                if (!isset($this->stickyMap[$documentId])) {
                    $this->stickyMap[$documentId] = array("cookies"=>array(), "keepDocument"=>$keepDocument);
                }
                foreach ($tmp as $name => $value) {
                    $this->stickyMap[$documentId]['cookies'][$name] = $value;
                }
            }
        }
        return $documentId;
    }
    function getProgress($documentId) {
        if (is_null($documentId)) {
            throw new \Exception("No conversion was triggered.");
        }
        $url = $this->url ."/progress/" . $documentId . ".json";
        if (!is_null($this->apiKey)) {
            $url .= "?apiKey=" . $this->apiKey;
        }
        $headerStr = '';
        $cookieStr = '';
        if (!empty($this->headers)) {
            foreach ($this->headers as $name => $value) {
                $lcName = strtolower($name);
                if ($lcName !== "user-agent" && $lcName !== "content-type" && $lcName !== "range") {
                    $headerStr .= $name . ": " . $value . "\r\n";
                }
            }
        }
        if (!empty($this->cookies)) {
            foreach ($this->cookies as $name => $value) {
                $cookieStr .= $name . "=" . $value . "; ";
            }
        }
        if (!empty($this->stickyMap[$documentId])) {
            foreach ($this->stickyMap[$documentId]['cookies'] as $name => $value) {
                $cookieStr .= $name . "=" . $value . "; ";
            }
        }
        $headerStr .= "Content-Type: application/json\r\n";
        $headerStr .= "User-Agent: PDFreactor PHP API v4\r\n";
        $headerStr .= "X-RO-User-Agent: PDFreactor PHP API v4\r\n";
        if (!empty($this->cookies) || !empty($cookieStr)) {
            $headerStr .= "Cookie: " . substr($cookieStr, 0, -2);
        }
        $options = array(
            'http' => array(
                'header'  => $headerStr,
                'follow_location' => false,
                'max_redirects' => 0,
                'method'  => 'GET',
                'ignore_errors' => true
            ),
        );
        $context = stream_context_create($options);
        $result = null;
        $errorMode = true;
        $rh = fopen($url, false, false, $context);
        if (!isset($http_response_header)) {
            $lastError = error_get_last();
            throw new \Exception('Error connecting to PDFreactor Web Service at ' . $this->url . '. Please make sure the PDFreactor Web Service is installed and running (Error: ' . $lastError['message'] . ')');
        }
        $status = intval(substr($http_response_header[0], 9, 3));
        if ($status >= 200 && $status <= 204) {
            $errorMode = false;
        }
        $result = stream_get_contents($rh);
        fclose($rh);
        if ($status == 422) {
            throw new \Exception(json_decode($result)->error);
        } else if ($status == 400) {
            throw new \Exception('Invalid client data. '.json_decode($result)->error);
        } else if ($status == 404) {
            throw new \Exception('Document with the given ID was not found. '.json_decode($result)->error);
        } else if ($status == 403) {
            throw new \Exception('Request rejected. '.json_decode($result)->error);
        } else if ($status == 401) {
            throw new \Exception('Unauthorized. '.json_decode($result)->error);
        } else if ($status == 413) {
            throw new \Exception('The configuration is too large to process.');
        } else if ($status == 500) {
            throw new \Exception(json_decode($result)->error);
        } else if ($status == 503) {
            throw new \Exception('PDFreactor Web Service is unavailable.');
        } else if ($status > 400) {
            throw new \Exception('PDFreactor Web Service error (status: ' . $status . ').');
        }
        return json_decode($result);
    }
    function getDocument($documentId) {
        if (is_null($documentId)) {
            throw new \Exception("No conversion was triggered.");
        }
        $url = $this->url ."/document/" . $documentId . ".json";
        if (!is_null($this->apiKey)) {
            $url .= "?apiKey=" . $this->apiKey;
        }
        $headerStr = '';
        $cookieStr = '';
        if (!empty($this->headers)) {
            foreach ($this->headers as $name => $value) {
                $lcName = strtolower($name);
                if ($lcName !== "user-agent" && $lcName !== "content-type" && $lcName !== "range") {
                    $headerStr .= $name . ": " . $value . "\r\n";
                }
            }
        }
        if (!empty($this->cookies)) {
            foreach ($this->cookies as $name => $value) {
                $cookieStr .= $name . "=" . $value . "; ";
            }
        }
        if (!empty($this->stickyMap[$documentId])) {
            foreach ($this->stickyMap[$documentId]['cookies'] as $name => $value) {
                $cookieStr .= $name . "=" . $value . "; ";
            }
        }
        if (!empty($this->stickyMap[$documentId]) && $this->stickyMap[$documentId]['keepDocument'] != true) {
            unset($this->stickyMap[$documentId]);
        }
        $headerStr .= "Content-Type: application/json\r\n";
        $headerStr .= "User-Agent: PDFreactor PHP API v4\r\n";
        $headerStr .= "X-RO-User-Agent: PDFreactor PHP API v4\r\n";
        if (!empty($this->cookies) || !empty($cookieStr)) {
            $headerStr .= "Cookie: " . substr($cookieStr, 0, -2);
        }
        $options = array(
            'http' => array(
                'header'  => $headerStr,
                'follow_location' => false,
                'max_redirects' => 0,
                'method'  => 'GET',
                'ignore_errors' => true
            ),
        );
        $context = stream_context_create($options);
        $result = null;
        $errorMode = true;
        $rh = fopen($url, false, false, $context);
        if (!isset($http_response_header)) {
            $lastError = error_get_last();
            throw new \Exception('Error connecting to PDFreactor Web Service at ' . $this->url . '. Please make sure the PDFreactor Web Service is installed and running (Error: ' . $lastError['message'] . ')');
        }
        $status = intval(substr($http_response_header[0], 9, 3));
        if ($status >= 200 && $status <= 204) {
            $errorMode = false;
        }
        $result = stream_get_contents($rh);
        fclose($rh);
        if ($status == 422) {
            throw new \Exception(json_decode($result)->error);
        } else if ($status == 400) {
            throw new \Exception('Invalid client data. '.json_decode($result)->error);
        } else if ($status == 404) {
            throw new \Exception('Document with the given ID was not found. '.json_decode($result)->error);
        } else if ($status == 403) {
            throw new \Exception('Request rejected. '.json_decode($result)->error);
        } else if ($status == 401) {
            throw new \Exception('Unauthorized. '.json_decode($result)->error);
        } else if ($status == 413) {
            throw new \Exception('The configuration is too large to process.');
        } else if ($status == 500) {
            throw new \Exception(json_decode($result)->error);
        } else if ($status == 503) {
            throw new \Exception('PDFreactor Web Service is unavailable.');
        } else if ($status > 400) {
            throw new \Exception('PDFreactor Web Service error (status: ' . $status . ').');
        }
        return json_decode($result);
    }
    function getDocumentAsBinary($documentId, $wh = null) {
        if (is_null($documentId)) {
            throw new \Exception("No conversion was triggered.");
        }
        $url = $this->url ."/document/" . $documentId . ".bin";
        if (!is_null($this->apiKey)) {
            $url .= "?apiKey=" . $this->apiKey;
        }
        $headerStr = '';
        $cookieStr = '';
        if (!empty($this->headers)) {
            foreach ($this->headers as $name => $value) {
                $lcName = strtolower($name);
                if ($lcName !== "user-agent" && $lcName !== "content-type" && $lcName !== "range") {
                    $headerStr .= $name . ": " . $value . "\r\n";
                }
            }
        }
        if (!empty($this->cookies)) {
            foreach ($this->cookies as $name => $value) {
                $cookieStr .= $name . "=" . $value . "; ";
            }
        }
        if (!empty($this->stickyMap[$documentId])) {
            foreach ($this->stickyMap[$documentId]['cookies'] as $name => $value) {
                $cookieStr .= $name . "=" . $value . "; ";
            }
        }
        if (!empty($this->stickyMap[$documentId]) && $this->stickyMap[$documentId]['keepDocument'] != true) {
            unset($this->stickyMap[$documentId]);
        }
        $headerStr .= "Content-Type: application/json\r\n";
        $headerStr .= "User-Agent: PDFreactor PHP API v4\r\n";
        $headerStr .= "X-RO-User-Agent: PDFreactor PHP API v4\r\n";
        if (!empty($this->cookies) || !empty($cookieStr)) {
            $headerStr .= "Cookie: " . substr($cookieStr, 0, -2);
        }
        $options = array(
            'http' => array(
                'header'  => $headerStr,
                'follow_location' => false,
                'max_redirects' => 0,
                'method'  => 'GET',
                'ignore_errors' => true
            ),
        );
        $context = stream_context_create($options);
        $result = null;
        $errorMode = true;
        $rh = fopen($url, false, false, $context);
        if (!isset($http_response_header)) {
            $lastError = error_get_last();
            throw new \Exception('Error connecting to PDFreactor Web Service at ' . $this->url . '. Please make sure the PDFreactor Web Service is installed and running (Error: ' . $lastError['message'] . ')');
        }
        $status = intval(substr($http_response_header[0], 9, 3));
        if ($status >= 200 && $status <= 204) {
            $errorMode = false;
        }
        if ($errorMode || $wh == null) {
            $result = stream_get_contents($rh);
            fclose($rh);
        }
        if ($status == 422) {
            throw new \Exception($result);
        } else if ($status == 400) {
            throw new \Exception('Invalid client data. '.$result);
        } else if ($status == 404) {
            throw new \Exception('Document with the given ID was not found. '.$result);
        } else if ($status == 403) {
            throw new \Exception('Request rejected. '.$result);
        } else if ($status == 401) {
            throw new \Exception('Unauthorized. '.$result);
        } else if ($status == 413) {
            throw new \Exception('The configuration is too large to process.');
        } else if ($status == 500) {
            throw new \Exception($result);
        } else if ($status == 503) {
            throw new \Exception('PDFreactor Web Service is unavailable.');
        } else if ($status > 400) {
            throw new \Exception('PDFreactor Web Service error (status: ' . $status . ').');
        }
        if (!$errorMode && $wh != null) {
            while (!feof($rh)) {
                if (fwrite($wh, fread($rh, 1024)) === FALSE) {
                    return null;
                }
            }
            fclose($rh);
            fclose($wh);
        }
        return $result;
    }
    function deleteDocument($documentId) {
        if (is_null($documentId)) {
            throw new \Exception("No conversion was triggered.");
        }
        $url = $this->url ."/document/" . $documentId . ".json";
        if (!is_null($this->apiKey)) {
            $url .= "?apiKey=" . $this->apiKey;
        }
        $headerStr = '';
        $cookieStr = '';
        if (!empty($this->headers)) {
            foreach ($this->headers as $name => $value) {
                $lcName = strtolower($name);
                if ($lcName !== "user-agent" && $lcName !== "content-type" && $lcName !== "range") {
                    $headerStr .= $name . ": " . $value . "\r\n";
                }
            }
        }
        if (!empty($this->cookies)) {
            foreach ($this->cookies as $name => $value) {
                $cookieStr .= $name . "=" . $value . "; ";
            }
        }
        if (!empty($this->stickyMap[$documentId])) {
            foreach ($this->stickyMap[$documentId]['cookies'] as $name => $value) {
                $cookieStr .= $name . "=" . $value . "; ";
            }
        }
        if (!empty($this->stickyMap[$documentId])) {
            unset($this->stickyMap[$documentId]);
        }
        $headerStr .= "Content-Type: application/json\r\n";
        $headerStr .= "User-Agent: PDFreactor PHP API v4\r\n";
        $headerStr .= "X-RO-User-Agent: PDFreactor PHP API v4\r\n";
        if (!empty($this->cookies) || !empty($cookieStr)) {
            $headerStr .= "Cookie: " . substr($cookieStr, 0, -2);
        }
        $options = array(
            'http' => array(
                'header'  => $headerStr,
                'follow_location' => false,
                'max_redirects' => 0,
                'method'  => 'DELETE',
                'ignore_errors' => true
            ),
        );
        $context = stream_context_create($options);
        $result = null;
        $errorMode = true;
        $rh = fopen($url, false, false, $context);
        if (!isset($http_response_header)) {
            $lastError = error_get_last();
            throw new \Exception('Error connecting to PDFreactor Web Service at ' . $this->url . '. Please make sure the PDFreactor Web Service is installed and running (Error: ' . $lastError['message'] . ')');
        }
        $status = intval(substr($http_response_header[0], 9, 3));
        if ($status >= 200 && $status <= 204) {
            $errorMode = false;
        }
        $result = stream_get_contents($rh);
        fclose($rh);
        if ($status == 422) {
            throw new \Exception(json_decode($result)->error);
        } else if ($status == 400) {
            throw new \Exception('Invalid client data. '.json_decode($result)->error);
        } else if ($status == 404) {
            throw new \Exception('Document with the given ID was not found. '.json_decode($result)->error);
        } else if ($status == 403) {
            throw new \Exception('Request rejected. '.json_decode($result)->error);
        } else if ($status == 401) {
            throw new \Exception('Unauthorized. '.json_decode($result)->error);
        } else if ($status == 413) {
            throw new \Exception('The configuration is too large to process.');
        } else if ($status == 500) {
            throw new \Exception(json_decode($result)->error);
        } else if ($status == 503) {
            throw new \Exception('PDFreactor Web Service is unavailable.');
        } else if ($status > 400) {
            throw new \Exception('PDFreactor Web Service error (status: ' . $status . ').');
        }
    }
    function getVersion() {
        $url = $this->url ."/version.json";
        if (!is_null($this->apiKey)) {
            $url .= "?apiKey=" . $this->apiKey;
        }
        $headerStr = '';
        $cookieStr = '';
        if (!empty($this->headers)) {
            foreach ($this->headers as $name => $value) {
                $lcName = strtolower($name);
                if ($lcName !== "user-agent" && $lcName !== "content-type" && $lcName !== "range") {
                    $headerStr .= $name . ": " . $value . "\r\n";
                }
            }
        }
        if (!empty($this->cookies)) {
            foreach ($this->cookies as $name => $value) {
                $cookieStr .= $name . "=" . $value . "; ";
            }
        }
        $headerStr .= "Content-Type: application/json\r\n";
        $headerStr .= "User-Agent: PDFreactor PHP API v4\r\n";
        $headerStr .= "X-RO-User-Agent: PDFreactor PHP API v4\r\n";
        if (!empty($this->cookies) || !empty($cookieStr)) {
            $headerStr .= "Cookie: " . substr($cookieStr, 0, -2);
        }
        $options = array(
            'http' => array(
                'header'  => $headerStr,
                'follow_location' => false,
                'max_redirects' => 0,
                'method'  => 'GET',
                'ignore_errors' => true
            ),
        );
        $context = stream_context_create($options);
        $result = null;
        $errorMode = true;
        $rh = fopen($url, false, false, $context);
        if (!isset($http_response_header)) {
            $lastError = error_get_last();
            throw new \Exception('Error connecting to PDFreactor Web Service at ' . $this->url . '. Please make sure the PDFreactor Web Service is installed and running (Error: ' . $lastError['message'] . ')');
        }
        $status = intval(substr($http_response_header[0], 9, 3));
        if ($status >= 200 && $status <= 204) {
            $errorMode = false;
        }
        $result = stream_get_contents($rh);
        fclose($rh);
        if ($status == 422) {
            throw new \Exception(json_decode($result)->error);
        } else if ($status == 400) {
            throw new \Exception('Invalid client data. '.json_decode($result)->error);
        } else if ($status == 404) {
            throw new \Exception('Document with the given ID was not found. '.json_decode($result)->error);
        } else if ($status == 403) {
            throw new \Exception('Request rejected. '.json_decode($result)->error);
        } else if ($status == 401) {
            throw new \Exception('Unauthorized. '.json_decode($result)->error);
        } else if ($status == 413) {
            throw new \Exception('The configuration is too large to process.');
        } else if ($status == 500) {
            throw new \Exception(json_decode($result)->error);
        } else if ($status == 503) {
            throw new \Exception('PDFreactor Web Service is unavailable.');
        } else if ($status > 400) {
            throw new \Exception('PDFreactor Web Service error (status: ' . $status . ').');
        }
        return json_decode($result);
    }
    function getStatus() {
        $url = $this->url ."/document.json";
        if (!is_null($this->apiKey)) {
            $url .= "?apiKey=" . $this->apiKey;
        }
        $headerStr = '';
        $cookieStr = '';
        if (!empty($this->headers)) {
            foreach ($this->headers as $name => $value) {
                $lcName = strtolower($name);
                if ($lcName !== "user-agent" && $lcName !== "content-type" && $lcName !== "range") {
                    $headerStr .= $name . ": " . $value . "\r\n";
                }
            }
        }
        if (!empty($this->cookies)) {
            foreach ($this->cookies as $name => $value) {
                $cookieStr .= $name . "=" . $value . "; ";
            }
        }
        $headerStr .= "Content-Type: application/json\r\n";
        $headerStr .= "User-Agent: PDFreactor PHP API v4\r\n";
        $headerStr .= "X-RO-User-Agent: PDFreactor PHP API v4\r\n";
        if (!empty($this->cookies) || !empty($cookieStr)) {
            $headerStr .= "Cookie: " . substr($cookieStr, 0, -2);
        }
        $options = array(
            'http' => array(
                'header'  => $headerStr,
                'follow_location' => false,
                'max_redirects' => 0,
                'method'  => 'GET',
                'ignore_errors' => true
            ),
        );
        $context = stream_context_create($options);
        $result = null;
        $errorMode = true;
        $rh = fopen($url, false, false, $context);
        if (!isset($http_response_header)) {
            $lastError = error_get_last();
            throw new \Exception('Error connecting to PDFreactor Web Service at ' . $this->url . '. Please make sure the PDFreactor Web Service is installed and running (Error: ' . $lastError['message'] . ')');
        }
        $status = intval(substr($http_response_header[0], 9, 3));
        if ($status >= 200 && $status <= 204) {
            $errorMode = false;
        }
        $result = stream_get_contents($rh);
        fclose($rh);
        if ($status == 422) {
            throw new \Exception(json_decode($result)->error);
        } else if ($status == 400) {
            throw new \Exception('Invalid client data. '.json_decode($result)->error);
        } else if ($status == 404) {
            throw new \Exception('Document with the given ID was not found. '.json_decode($result)->error);
        } else if ($status == 403) {
            throw new \Exception('Request rejected. '.json_decode($result)->error);
        } else if ($status == 401) {
            throw new \Exception('Unauthorized. '.json_decode($result)->error);
        } else if ($status == 413) {
            throw new \Exception('The configuration is too large to process.');
        } else if ($status == 500) {
            throw new \Exception(json_decode($result)->error);
        } else if ($status == 503) {
            throw new \Exception('PDFreactor Web Service is unavailable.');
        } else if ($status > 400) {
            throw new \Exception('PDFreactor Web Service error (status: ' . $status . ').');
        }
    }
    function getDocumentUrl($documentId) {
        if (!is_null($documentId)) {
            return $this->url . "/document/" . $documentId;
        }
        return null;
    }
    function getProgressUrl($documentId) {
        if (!is_null($documentId)) {
            return $this->url . "/progress/" . $documentId;
        }
        return null;
    }
    const VERSION = 4;
    public function __get($name) {
        if ($name == "headers" || $name == "cookies" || $name == "apiKey") {
            return isset($this->$name) ? $this->$name : null;
        }
    }
}
abstract class CallbackType {
    const FINISH = "FINISH";
    const PROGRESS = "PROGRESS";
    const START = "START";
}
abstract class Cleanup {
    const CYBERNEKO = "CYBERNEKO";
    const JTIDY = "JTIDY";
    const NONE = "NONE";
    const TAGSOUP = "TAGSOUP";
}
abstract class ColorSpace {
    const CMYK = "CMYK";
    const RGB = "RGB";
}
abstract class Conformance {
    const PDF = "PDF";
    const PDFA1A = "PDFA1A";
    const PDFA1A_PDFUA1 = "PDFA1A_PDFUA1";
    const PDFA1B = "PDFA1B";
    const PDFA2A = "PDFA2A";
    const PDFA2A_PDFUA1 = "PDFA2A_PDFUA1";
    const PDFA2B = "PDFA2B";
    const PDFA2U = "PDFA2U";
    const PDFA3A = "PDFA3A";
    const PDFA3A_PDFUA1 = "PDFA3A_PDFUA1";
    const PDFA3B = "PDFA3B";
    const PDFA3U = "PDFA3U";
    const PDFUA1 = "PDFUA1";
    const PDFX1A_2001 = "PDFX1A_2001";
    const PDFX1A_2003 = "PDFX1A_2003";
    const PDFX3_2002 = "PDFX3_2002";
    const PDFX3_2003 = "PDFX3_2003";
    const PDFX4 = "PDFX4";
    const PDFX4P = "PDFX4P";
}
abstract class ContentType {
    const BINARY = "BINARY";
    const BMP = "BMP";
    const GIF = "GIF";
    const HTML = "HTML";
    const JPEG = "JPEG";
    const JSON = "JSON";
    const NONE = "NONE";
    const PDF = "PDF";
    const PNG = "PNG";
    const TEXT = "TEXT";
    const TIFF = "TIFF";
    const XML = "XML";
}
abstract class Doctype {
    const AUTODETECT = "AUTODETECT";
    const HTML5 = "HTML5";
    const XHTML = "XHTML";
    const XML = "XML";
}
abstract class Encryption {
    const NONE = "NONE";
    const TYPE_128 = "TYPE_128";
    const TYPE_40 = "TYPE_40";
}
abstract class ErrorPolicy {
    const LICENSE = "LICENSE";
    const MISSING_RESOURCE = "MISSING_RESOURCE";
}
abstract class ExceedingContentAgainst {
    const NONE = "NONE";
    const PAGE_BORDERS = "PAGE_BORDERS";
    const PAGE_CONTENT = "PAGE_CONTENT";
    const PARENT = "PARENT";
}
abstract class ExceedingContentAnalyze {
    const CONTENT = "CONTENT";
    const CONTENT_AND_BOXES = "CONTENT_AND_BOXES";
    const CONTENT_AND_STATIC_BOXES = "CONTENT_AND_STATIC_BOXES";
    const NONE = "NONE";
}
abstract class HttpsMode {
    const LENIENT = "LENIENT";
    const STRICT = "STRICT";
}
abstract class JavaScriptMode {
    const DISABLED = "DISABLED";
    const ENABLED = "ENABLED";
    const ENABLED_NO_LAYOUT = "ENABLED_NO_LAYOUT";
    const ENABLED_REAL_TIME = "ENABLED_REAL_TIME";
    const ENABLED_TIME_LAPSE = "ENABLED_TIME_LAPSE";
}
abstract class KeystoreType {
    const JKS = "JKS";
    const PKCS12 = "PKCS12";
}
abstract class LogLevel {
    const DEBUG = "DEBUG";
    const FATAL = "FATAL";
    const INFO = "INFO";
    const NONE = "NONE";
    const PERFORMANCE = "PERFORMANCE";
    const WARN = "WARN";
}
abstract class MediaFeature {
    const ASPECT_RATIO = "ASPECT_RATIO";
    const COLOR = "COLOR";
    const COLOR_INDEX = "COLOR_INDEX";
    const DEVICE_ASPECT_RATIO = "DEVICE_ASPECT_RATIO";
    const DEVICE_HEIGHT = "DEVICE_HEIGHT";
    const DEVICE_WIDTH = "DEVICE_WIDTH";
    const GRID = "GRID";
    const HEIGHT = "HEIGHT";
    const MONOCHROME = "MONOCHROME";
    const ORIENTATION = "ORIENTATION";
    const RESOLUTION = "RESOLUTION";
    const WIDTH = "WIDTH";
}
abstract class MergeMode {
    const APPEND = "APPEND";
    const ARRANGE = "ARRANGE";
    const OVERLAY = "OVERLAY";
    const OVERLAY_BELOW = "OVERLAY_BELOW";
    const PREPEND = "PREPEND";
}
abstract class OutputIntentDefaultProfile {
    const FOGRA39 = "Coated FOGRA39";
    const GRACOL = "Coated GRACoL 2006";
    const IFRA = "ISO News print 26% (IFRA)";
    const JAPAN = "Japan Color 2001 Coated";
    const JAPAN_NEWSPAPER = "Japan Color 2001 Newspaper";
    const JAPAN_UNCOATED = "Japan Color 2001 Uncoated";
    const JAPAN_WEB = "Japan Web Coated (Ad)";
    const SWOP = "US Web Coated (SWOP) v2";
    const SWOP_3 = "Web Coated SWOP 2006 Grade 3 Paper";
}
abstract class OutputType {
    const BMP = "BMP";
    const GIF = "GIF";
    const JPEG = "JPEG";
    const PDF = "PDF";
    const PNG = "PNG";
    const PNG_AI = "PNG_AI";
    const PNG_TRANSPARENT = "PNG_TRANSPARENT";
    const PNG_TRANSPARENT_AI = "PNG_TRANSPARENT_AI";
    const TIFF_CCITT_1D = "TIFF_CCITT_1D";
    const TIFF_CCITT_GROUP_3 = "TIFF_CCITT_GROUP_3";
    const TIFF_CCITT_GROUP_4 = "TIFF_CCITT_GROUP_4";
    const TIFF_LZW = "TIFF_LZW";
    const TIFF_PACKBITS = "TIFF_PACKBITS";
    const TIFF_UNCOMPRESSED = "TIFF_UNCOMPRESSED";
}
abstract class OverlayRepeat {
    const ALL_PAGES = "ALL_PAGES";
    const LAST_PAGE = "LAST_PAGE";
    const NONE = "NONE";
    const TRIM = "TRIM";
}
abstract class PageOrder {
    const BOOKLET = "BOOKLET";
    const BOOKLET_RTL = "BOOKLET_RTL";
    const EVEN = "EVEN";
    const ODD = "ODD";
    const REVERSE = "REVERSE";
}
abstract class PagesPerSheetDirection {
    const DOWN_LEFT = "DOWN_LEFT";
    const DOWN_RIGHT = "DOWN_RIGHT";
    const LEFT_DOWN = "LEFT_DOWN";
    const LEFT_UP = "LEFT_UP";
    const RIGHT_DOWN = "RIGHT_DOWN";
    const RIGHT_UP = "RIGHT_UP";
    const UP_LEFT = "UP_LEFT";
    const UP_RIGHT = "UP_RIGHT";
}
abstract class PdfScriptTriggerEvent {
    const AFTER_PRINT = "AFTER_PRINT";
    const AFTER_SAVE = "AFTER_SAVE";
    const BEFORE_PRINT = "BEFORE_PRINT";
    const BEFORE_SAVE = "BEFORE_SAVE";
    const CLOSE = "CLOSE";
    const OPEN = "OPEN";
}
abstract class ProcessingPreferences {
    const SAVE_MEMORY_IMAGES = "SAVE_MEMORY_IMAGES";
}
abstract class ResourceType {
    const FONT = "FONT";
    const IFRAME = "IFRAME";
    const IMAGE = "IMAGE";
    const OBJECT = "OBJECT";
    const RUNNING_DOCUMENT = "RUNNING_DOCUMENT";
    const SCRIPT = "SCRIPT";
    const STYLESHEET = "STYLESHEET";
    const UNKNOWN = "UNKNOWN";
}
abstract class SigningMode {
    const SELF_SIGNED = "SELF_SIGNED";
    const VERISIGN_SIGNED = "VERISIGN_SIGNED";
    const WINCER_SIGNED = "WINCER_SIGNED";
}
abstract class ViewerPreferences {
    const CENTER_WINDOW = "CENTER_WINDOW";
    const DIRECTION_L2R = "DIRECTION_L2R";
    const DIRECTION_R2L = "DIRECTION_R2L";
    const DISPLAY_DOC_TITLE = "DISPLAY_DOC_TITLE";
    const DUPLEX_FLIP_LONG_EDGE = "DUPLEX_FLIP_LONG_EDGE";
    const DUPLEX_FLIP_SHORT_EDGE = "DUPLEX_FLIP_SHORT_EDGE";
    const DUPLEX_SIMPLEX = "DUPLEX_SIMPLEX";
    const FIT_WINDOW = "FIT_WINDOW";
    const HIDE_MENUBAR = "HIDE_MENUBAR";
    const HIDE_TOOLBAR = "HIDE_TOOLBAR";
    const HIDE_WINDOW_UI = "HIDE_WINDOW_UI";
    const NON_FULLSCREEN_PAGE_MODE_USE_NONE = "NON_FULLSCREEN_PAGE_MODE_USE_NONE";
    const NON_FULLSCREEN_PAGE_MODE_USE_OC = "NON_FULLSCREEN_PAGE_MODE_USE_OC";
    const NON_FULLSCREEN_PAGE_MODE_USE_OUTLINES = "NON_FULLSCREEN_PAGE_MODE_USE_OUTLINES";
    const NON_FULLSCREEN_PAGE_MODE_USE_THUMBS = "NON_FULLSCREEN_PAGE_MODE_USE_THUMBS";
    const PAGE_LAYOUT_ONE_COLUMN = "PAGE_LAYOUT_ONE_COLUMN";
    const PAGE_LAYOUT_SINGLE_PAGE = "PAGE_LAYOUT_SINGLE_PAGE";
    const PAGE_LAYOUT_TWO_COLUMN_LEFT = "PAGE_LAYOUT_TWO_COLUMN_LEFT";
    const PAGE_LAYOUT_TWO_COLUMN_RIGHT = "PAGE_LAYOUT_TWO_COLUMN_RIGHT";
    const PAGE_LAYOUT_TWO_PAGE_LEFT = "PAGE_LAYOUT_TWO_PAGE_LEFT";
    const PAGE_LAYOUT_TWO_PAGE_RIGHT = "PAGE_LAYOUT_TWO_PAGE_RIGHT";
    const PAGE_MODE_FULLSCREEN = "PAGE_MODE_FULLSCREEN";
    const PAGE_MODE_USE_ATTACHMENTS = "PAGE_MODE_USE_ATTACHMENTS";
    const PAGE_MODE_USE_NONE = "PAGE_MODE_USE_NONE";
    const PAGE_MODE_USE_OC = "PAGE_MODE_USE_OC";
    const PAGE_MODE_USE_OUTLINES = "PAGE_MODE_USE_OUTLINES";
    const PAGE_MODE_USE_THUMBS = "PAGE_MODE_USE_THUMBS";
    const PICKTRAYBYPDFSIZE_FALSE = "PICKTRAYBYPDFSIZE_FALSE";
    const PICKTRAYBYPDFSIZE_TRUE = "PICKTRAYBYPDFSIZE_TRUE";
    const PRINTSCALING_APPDEFAULT = "PRINTSCALING_APPDEFAULT";
    const PRINTSCALING_NONE = "PRINTSCALING_NONE";
}
abstract class XmpPriority {
    const HIGH = "HIGH";
    const LOW = "LOW";
    const NONE = "NONE";
}
?>
