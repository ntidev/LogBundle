<?php
namespace NTI\LogBundle\Annotations;

use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;

/**
 * @Annotation
 * @Target("CLASS")
 */
final class ExcludeDoctrineLogging extends AnnotationLoader
{

}