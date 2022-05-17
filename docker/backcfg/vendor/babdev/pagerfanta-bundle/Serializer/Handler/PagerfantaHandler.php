<?php declare(strict_types=1);

namespace BabDev\PagerfantaBundle\Serializer\Handler;

use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use Pagerfanta\Pagerfanta;

final class PagerfantaHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => Pagerfanta::class,
                'method' => 'serializeToJson',
            ],
        ];
    }

    /**
     * @return array|\ArrayObject
     */
    public function serializeToJson(SerializationVisitorInterface $visitor, Pagerfanta $pagerfanta, array $type, SerializationContext $context)
    {
        return $visitor->visitArray(
            [
                'items' => $pagerfanta->getCurrentPageResults(),
                'pagination' => [
                    'current_page' => $pagerfanta->getCurrentPage(),
                    'has_previous_page' => $pagerfanta->hasPreviousPage(),
                    'has_next_page' => $pagerfanta->hasNextPage(),
                    'per_page' => $pagerfanta->getMaxPerPage(),
                    'total_items' => $pagerfanta->getNbResults(),
                    'total_pages' => $pagerfanta->getNbPages(),
                ],
            ],
            $type
        );
    }
}
