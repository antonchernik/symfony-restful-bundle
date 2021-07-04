<?php

declare(strict_types=1);

namespace RestfulBundle\Controller;

use Doctrine\Common\Collections\Collection;
use DTOBundle\Trait\MapperAwareInterface;
use DTOBundle\Trait\MapperTrait;
use DTOBundle\Trait\SerializerAwareInterface;
use DTOBundle\Trait\SerializerTrait;
use RestfulBundle\Dto\ListDto;
use RestfulBundle\Service\RequestTracker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Component\HttpFoundation\Response;

class RestfulController extends SymfonyAbstractController implements MapperAwareInterface, SerializerAwareInterface
{
    use MapperTrait;
    use SerializerTrait;

    private const CONTENT_TYPE_APPLICATION_JSON = 'application/json';

    protected RequestTracker $requestTracker;

    public function __construct(RequestTracker $requestTracker)
    {
        $this->requestTracker = $requestTracker;
    }

    protected function createEmptyResponse(int $statusCode = Response::HTTP_NO_CONTENT): Response
    {
        return new Response('', $statusCode);
    }

    protected function createResponse($data, $dtoName = null, array $context = [], $statusCode = Response::HTTP_OK): Response
    {
        if (null !== $dtoName) {
            $data = $this->mapper->convert($data, $dtoName, $context);
        }

        //serialize data
        $data = $data instanceof \JsonSerializable
            ? json_encode($data)
            : $this->serializer->serialize($data, 'json');

        //headers
        $headers = [
            'Content-Type' => self::CONTENT_TYPE_APPLICATION_JSON,
            'X-Request-Id' => $this->requestTracker->getRequestId(),
        ];

        return new Response($data, $statusCode, $headers);
    }

    protected function createListDto(int $total, array $collection): ListDto
    {
        return new ListDto($total, $collection);
    }

    protected function createListResponse(int $total, iterable $collection, string $dtoName = null, array $context = [], $statusCode = Response::HTTP_OK): Response
    {
        $items = ($collection instanceof Collection || $collection instanceof \Iterator)
            ? $collection->toArray()
            : $collection;
        $items = empty($dtoName) ? $items : $this->mapper->convertCollection($items, $dtoName, $context);
        $items = array_values($items instanceof \Traversable ? iterator_to_array($items) : $items);

        return $this->createResponse($this->createListDto($total, $items), null, [], $statusCode);
    }

    protected function createCollectionResponse(iterable $collection, string $dtoName = null, array $context = [], $statusCode = Response::HTTP_OK): Response
    {
        return $this->createListResponse(count($collection), $collection, $dtoName, $context, $statusCode);
    }
}
