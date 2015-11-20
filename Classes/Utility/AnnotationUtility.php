<?php
namespace FluidTYPO3\Flux\Utility;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\Reflection\ClassReflection;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Fluid\Core\Parser\TemplateParser;

/**
 * Annotation Utility
 */
class AnnotationUtility {

	/**
	 * @param string $className
	 * @param string $annotationName
	 * @param string|boolean $propertyName
	 * @return array|boolean
	 */
	public static function getAnnotationValueFromClass($className, $annotationName, $propertyName = NULL) {
		$reflection = new ClassReflection($className);
		$sample = new $className();
		$annotations = array();
		if (NULL === $propertyName) {
			if (FALSE === $reflection->isTaggedWith($annotationName)) {
				return FALSE;
			}
			$annotations = $reflection->getTagValues($annotationName);
		} elseif (FALSE === $propertyName) {
			$properties = ObjectAccess::getGettablePropertyNames($sample);
			foreach ($properties as $reflectedPropertyName) {
				if (FALSE === property_exists($className, $reflectedPropertyName)) {
					continue;
				}
				$propertyAnnotationValues = self::getPropertyAnnotations($reflection, $reflectedPropertyName, $annotationName);
				if (NULL !== $propertyAnnotationValues) {
					$annotations[$reflectedPropertyName] = $propertyAnnotationValues;
				}
			}
		} else {
			$annotations = self::getPropertyAnnotations($reflection, $propertyName, $annotationName);
		}
		$annotations = self::parseAnnotation($annotations);
		return (NULL !== $propertyName && TRUE === isset($annotations[$propertyName])) ? $annotations[$propertyName] : $annotations;
	}

	/**
	 * @param ClassReflection $reflection
	 * @param string $propertyName
	 * @param string $annotationName
	 * @return array
	 */
	protected static function getPropertyAnnotations(ClassReflection $reflection, $propertyName, $annotationName) {
		$reflectedProperty = $reflection->getProperty($propertyName);
		if (TRUE === $reflectedProperty->isTaggedWith($annotationName)) {
			return $reflectedProperty->getTagValues($annotationName);
		}
		return NULL;
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
		if (TRUE === is_array($annotation)) {
			if (TRUE === empty($annotation)) {
				return TRUE;
			} elseif (TRUE === isset($annotation[0]) && 1 === count($annotation)) {
				return self::parseAnnotation(array_pop($annotation));
			}
			return array_map(array('FluidTYPO3\\Flux\\Utility\\AnnotationUtility', 'parseAnnotation'), $annotation);
		}
		$pattern = TemplateParser::$SPLIT_PATTERN_SHORTHANDSYNTAX_VIEWHELPER;
		$annotation = trim($annotation);
		if (TRUE === empty($annotation)) {
			// simple indication that annotation does exist but has no attributes.
			return TRUE;
		}
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
