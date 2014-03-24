<?php
namespace FluidTYPO3\Flux\Utility;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Extbase\Reflection\ClassReflection;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Fluid\Core\Parser\TemplateParser;

/**
 * Annotation Utility
 *
 * @package Flux
 * @subpackage Utility
 */
class AnnotationUtility {

	/**
	 * @var array
	 */
	private static $cache = array(
		'reflections' => array(),
		'annotations' => array()
	);

	/**
	 * @param string $className
	 * @param string $annotationName
	 * @param string|boolean $propertyName
	 * @return string
	 */
	public static function getAnnotationValueFromClass($className, $annotationName, $propertyName = FALSE) {
		if (TRUE === isset(self::$cache['reflections'][$className])) {
			$reflection = self::$cache['reflections'][$className];
		} else {
			$reflection = self::$cache['reflections'][$className] = new ClassReflection($className);
		}
		if (FALSE === isset(self::$cache['annotations'][$className])) {
			self::$cache['annotations'][$className] = array();
		}
		if (TRUE === isset(self::$cache['annotations'][$className][$annotationName])) {
			$annotations = self::$cache['annotations'][$className][$annotationName];
		} else {
			$sample = new $className();
			$annotations = array();
			if (FALSE === $propertyName) {
				if (TRUE === $reflection->isTaggedWith($annotationName)) {
					$annotations = $reflection->getTagValues($annotationName);
				}
			} else {
				$properties = ObjectAccess::getGettablePropertyNames($sample);
				foreach ($properties as $reflectedPropertyName) {
					if (FALSE === property_exists($className, $reflectedPropertyName)) {
						continue;
					}
					$reflectedProperty = $reflection->getProperty($reflectedPropertyName);
					if (TRUE === $reflectedProperty->isTaggedWith($annotationName)) {
						$annotations[$reflectedPropertyName] = $reflectedProperty->getTagValues($annotationName);
					}
				}
			}
			$annotations = self::parseAnnotation($annotations);
		}
		self::$cache['annotations'][$className][$annotationName] = $annotations;
		if (NULL !== $propertyName && TRUE === isset($annotations[$propertyName])) {
			return $annotations[$propertyName];
		}
		return $annotations;
	}

	/**
	 * @param string $argumentsAsString
	 * @return string
	 */
	protected static function parseAnnotationArguments($argumentsAsString) {
		$pattern = TemplateParser::$SPLIT_PATTERN_SHORTHANDSYNTAX_ARRAY_PARTS;
		$matches = array();
		preg_match_all($pattern, $argumentsAsString, $matches, PREG_SET_ORDER);
		$arguments = array();
		foreach ($matches as $match) {
			$name = $match['Key'];
			if (TRUE === isset($match['Subarray']) && 0 < strlen($match['Subarray'])) {
				$arguments[$name] = self::parseAnnotationArguments($match['Subarray']);
			} elseif (TRUE === isset($match['Number'])) {
				if (TRUE === ctype_digit($match['Number'])) {
					$arguments[$name] = intval($match['Number']);
				} elseif (FALSE !== strpos($match['Number'], '.')) {
					$arguments[$name] = floatval($match['Number']);
				}
			} elseif (TRUE === isset($match['QuotedString'])) {
				$arguments[$name] = trim($match['QuotedString'], '\'');
			}
		}
		return $arguments;
	}

	/**
	 * @param mixed $annotation
	 * @return string
	 */
	public static function parseAnnotation($annotation) {
		if (TRUE === empty($annotation)) {
			return TRUE;
		} elseif (TRUE === is_array($annotation)) {
			if (TRUE === isset($annotation[0]) && 1 === count($annotation)) {
				return self::parseAnnotation(array_pop($annotation));
			}
			return array_map(array(self, 'parseAnnotation'), $annotation);
		}
		$pattern = TemplateParser::$SPLIT_PATTERN_SHORTHANDSYNTAX_VIEWHELPER;
		$annotation = trim($annotation);
		if (FALSE === strpos($annotation, '(') && FALSE === strpos($annotation, ')')) {
			$annotation .= '()';
		}
		if (0 !== strpos($annotation, '{')) {
			$annotation = '{flux:' . $annotation . '}';
		}
		$matches = array();
		preg_match_all($pattern, $annotation, $matches, PREG_SET_ORDER);
		$structure = array(
			'type' => $matches[0]['MethodIdentifier'],
			'config' => self::parseAnnotationArguments($matches['0']['ViewHelperArguments'])
		);
		return $structure;
	}

}
