<?php

/**
 * XML class to encode strings
 * @author maraya
 *
 * This file is part of Cazuela.
 *
 * Cazuela is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Cazuela is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Cazuela. If not, see <http://www.gnu.org/licenses/>.
 */

class XML {
	/**
	 * Holds the SimpleXMLElement object
	 * @var object
	 */
	private $xml;
	
	/**
	 * XML construct
	 * @param string $charset
	 */
	public function __construct($charset) {
		$this->xml = new SimpleXMLElement('<root/>');
	}
	
	private function createXML($input) {
		foreach ($input as $key => $val) {
			//echo $key."=>".$val;
			
			if (is_array($val)) {
				$this->xml->addChild($key, $this->createXML($input));
			}	
		}
		return $this->xml;
	}
	
	/**
	 * Transforms array to XML format
	 * @param array $input
	 * @return string
	 */
	public function encode($input) {
		$this->createXML($input);
		return $this->xml->asXML();
	}
}
?>