<?php
/*
  Copyright 2011 by ShoutEm, Inc. (www.shoutem.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class ShoutemApiResponse {
	
	function __construct($base_url) {
		$this->headerList = array();
		$this->base_url = $base_url;
	}
	
	function send_json_ok($data) {
		header("Status: 200 OK");
		$this->send_json($data);
	}

	function addHeader($str, $replace = false) {
		$this->headerList[] = $str;
		
		if($replace) {
			header($str, true);
		} else {
			header($str);
		}
	}
	
	function send_json($data) {
		$json_data = "";

		if (is_string($data)) {
			$json_data = $data;	
		} else {
			$json_data = $this->encode_json($data);
		}

		$charset = get_option('blog_charset');

		if (!headers_sent()) {
			$this->addHeader("Content-Type: application/json; charset=$charset", true);
			$this->addHeader("Cache-Control: no-cache");
			$this->addHeader("Pragma: no-cache");

			if (isset($_SERVER['HTTP_ORIGIN'])) {
				$this->addHeader("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
				$this->addHeader("Access-Control-Allow-Credentials: true");
				$this->addHeader("Access-Control-Max-Age: 86400");
			} else {
				$this->addHeader("Access-Control-Allow-Origin: *");
			}

			// Access-Control headers are received during OPTIONS requests
			if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
				if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
					$this->addHeader("Access-Control-Allow-Methods: POST, PUT, GET, DELETE, HEAD, OPTIONS");         
				}

				if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
					$this->addHeader("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
				}

				exit(0);
			}
		}
		echo $json_data;
		exit();
	}
	
	function send_error($err_code, $details = null, $showUserFriendlyMessage = false) {
		$err_codes = array (
			400 => 'Bad Request',
			401 => 'Authorization Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			500 => 'Internal Server Error',
			501 => 'Method Not Implemented'
		);		
		
		$err_resp = $err_code. " ". $err_codes[$err_code];
		header("HTTP/1.1 $err_resp");
		header("Status: $err_resp");
		
		$result = array();
		$result['http_status_code'] = $err_code;
		$result['error'] = $showUserFriendlyMessage ? $details : $err_resp;
		if ($details) {
			$result['details'] = $details;
		}
		$this->send_json($result);
		exit();
	}
	
	function encode_json($data) {
		// Use PEAR's Services_JSON encoder  		
      	$json = new SEServices_JSON();
      	return $json->encode($data);
	}
	
}
?>