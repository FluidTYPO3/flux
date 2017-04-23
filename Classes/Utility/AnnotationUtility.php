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
use TYPO3Fluid\Fluid\Core\Parser\Patterns;

/**
 * Annotation Utility
 * @deprecated To be removed in next major release
 */
class AnnotationUtility
{

    /**
     * @param string $className
     * @param string $annotationName
     * @param string|boolean $propertyName
     * @return array|boolean
     */
    public static function getAnnotationValueFromClass($className, $annotationName, $propertyName = null)
    {
        $reflection = new ClassReflection($className);
        $sample = new $className();
        $annotations = [];
        if (null === $propertyName) {
            if (false === $reflection->isTaggedWith($annotationName)) {
                return false;
            }
            $annotations = $reflection->getTagValues($annotationName);
        } elseif (false === $propertyName) {
            $properties = ObjectAccess::getGettablePropertyNames($sample);
            foreach ($properties as $reflectedPropertyName) {
                if (false === property_exists($className, $reflectedPropertyName)) {
                    continue;
                }
                $propertyAnnotationValues = self::getPropertyAnnotations(
                    $reflection,
                    $reflectedPropertyName,
                    $annotationName
                );
                if (null !== $propertyAnnotationValues) {
                    $annotations[$reflectedPropertyName] = $propertyAnnotationValues;
                }
            }
        } else {
            $annotations = self::getPropertyAnnotations($reflection, $propertyName, $annotationName);
        }
        $annotations = self::parseAnnotation($annotations);
        return ($propertyName && isset($annotations[$propertyName])) ? $annotations[$propertyName] : $annotations;
    }

    /**
     * @param ClassReflection $reflection
     * @param string $propertyName
     * @param string $annotationName
     * @return array
     */
    protected static function getPropertyAnnotations(ClassReflection $reflection, $propertyName, $annotationName)
    {
        $reflectedProperty = $reflection->getProperty($propertyName);
        if (true === $reflectedProperty->isTaggedWith($annotationName)) {
            return $reflectedProperty->getTagValues($annotationName);
        }
        return null;
    }

    /**
     * @param string $argumentsAsString
     * @return string
     */
    protected static function parseAnnotationArguments($argumentsAsString)
    {
        if (class_exists(TemplateParser::class)) {
            $pattern = TemplateParser::$SPLIT_PATTERN_SHORTHANDSYNTAX_ARRAY_PARTS;
        } else {
            $pattern = Patterns::$SPLIT_PATTERN_SHORTHANDSYNTAX_ARRAY_PARTS;
        }
        $matches = [];
        preg_match_all($pattern, $argumentsAsString, $matches, PREG_SET_ORDER);
        $arguments = [];
        foreach ($matches as $match) {
            $name = $match['Key'];
            if (true === isset($match['Subarray']) && 0 < strlen($match['Subarray'])) {
                $arguments[$name] = self::parseAnnotationArguments($match['Subarray']);
            } elseif (true === isset($match['Number'])) {
                if (true === ctype_digit($match['Number'])) {
                    $arguments[$name] = intval($match['Number']);
                } elseif (false !== strpos($match['Number'], '.')) {
                    $arguments[$name] = floatval($match['Number']);
                }
            } elseif (true === isset($match['QuotedString'])) {
                $arguments[$name] = trim($match['QuotedString'], '\'');
            }
        }
        return $arguments;
    }

    /**
     * @param mixed $annotation
     * @return string
     */
    public static function parseAnnotation($annotation)
    {
        if (true === is_array($annotation)) {
            if (true === empty($annotation)) {
                return true;
            } elseif (true === isset($annotation[0]) && 1 === count($annotation)) {
                return self::parseAnnotation(array_pop($annotation));
            }
            return array_map([self, 'parseAnnotation'], $annotation);
        }
        if (class_exists(TemplateParser::class)) {
            $pattern = TemplateParser::$SPLIT_PATTERN_SHORTHANDSYNTAX_VIEWHELPER;
        } else {
            $pattern = Patterns::$SPLIT_PATTERN_SHORTHANDSYNTAX_VIEWHELPER;
        }
        $annotation = trim($annotation);
        if (true === empty($annotation)) {
            // simple indication that annotation does exist but has no attributes.
            return true;
        }
        if (false === strpos($annotation, '(') && false === strpos($annotation, ')')) {
            $annotation .= '()';
        }
        if (0 !== strpos($annotation, '{')) {
            $annotation = '{flux:' . $annotation . '}';
        }
        $matches = [];
        preg_match_all($pattern, $annotation, $matches, PREG_SET_ORDER);
        $structure = [
            'type' => $matches[0]['MethodIdentifier'],
            'config' => self::parseAnnotationArguments($matches['0']['ViewHelperArguments'])
        ];
        return $structure;
    }
}
