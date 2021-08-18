<?php

declare(strict_types=1);

namespace RestfulBundle\Tests\Trait;

use Exception;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use RestfulBundle\Dictionary\Messages;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\Exception\RuntimeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Helper class to assert different conditions on Guzzle responses
 */
trait ResponseAsserterTrait
{
    private PropertyAccessor $accessor;

    /**
     * Asserts the response matches json schema
     *
     * @documentation https://github.com/justinrainbow/json-schema
     *
     * @param Response $response
     * @param array    $schema
     * @param int      $checkMode
     */
    public function assertResponseMatchesJsonType(
        Response $response,
        array $schema,
        int $checkMode = Constraint::CHECK_MODE_TYPE_CAST
    ): void {
        $data = json_decode($response->getContent(), true);

        $this->assertDataMatchesJsonType($data, $schema, $checkMode);
    }

    /**
     * Asserts the data matches json schema
     *
     * @documentation https://github.com/justinrainbow/json-schema
     *
     * @param Response $response
     * @param array    $schema
     * @param int      $checkMode
     */
    public function assertDataMatchesJsonType(
        $data,
        array $schema,
        int $checkMode = Constraint::CHECK_MODE_TYPE_CAST
    ): void {
        if (is_string($data)) {
            $data = json_decode($data, true);
        }
        $schema = json_decode(json_encode($schema), false); //convert to object

        $validator = new Validator();
        $validator->validate($data, $schema, $checkMode);

        $errors = '';
        if (false === $validator->isValid()) {
            foreach ($validator->getErrors() as $error) {
                $errors .= sprintf("[%s] %s\n", $error['property'], $error['message']);
            }
        }

        $this->assertTrue($validator->isValid(), $errors);
    }

    /**
     * Asserts the array of property names are in the JSON response
     *
     * @param Response $response
     * @param array    $expectedProperties
     */
    public function assertResponsePropertiesExist(Response $response, array $expectedProperties): void
    {
        foreach ($expectedProperties as $propertyPath) {
            // this will blow up if the property doesn't exist
            $this->readResponseProperty($response, $propertyPath);
        }
    }

    /**
     * Asserts the specific propertyPath is in the JSON response
     *
     * @param Response $response
     * @param string   $propertyPath e.g. firstName, battles[0].programmer.username
     */
    public function assertResponsePropertyExists(Response $response, string $propertyPath): void
    {
        // this will blow up if the property doesn't exist
        $this->readResponseProperty($response, $propertyPath);
    }

    /**
     * Asserts the given property path does *not* exist
     *
     * @param Response $response
     * @param string   $propertyPath e.g. firstName, battles[0].programmer.username
     */
    public function assertResponsePropertyDoesNotExist(Response $response, string $propertyPath): void
    {
        try {
            // this will blow up if the property doesn't exist
            $this->readResponseProperty($response, $propertyPath);
            static::fail(sprintf('Property "%s" exists, but it should not', $propertyPath));
        } catch (RuntimeException $e) {
            // cool, it blew up
            // this catches all errors (but only errors) from the PropertyAccess component
        }
    }

    /**
     * Asserts the response JSON property equals the given value
     *
     * @param Response $response
     * @param string   $propertyPath e.g. firstName, battles[0].programmer.username
     * @param mixed    $expectedValue
     */
    public function assertResponsePropertyEquals(Response $response, string $propertyPath, mixed $expectedValue): void
    {
        $actual = $this->readResponseProperty($response, $propertyPath);
        $this->assertEquals(
            $expectedValue,
            $actual,
            sprintf(
                'Property "%s": Expected "%s" but response was "%s"',
                $propertyPath,
                $expectedValue,
                var_export($actual, true)
            )
        );
    }

    /**
     * Asserts the response property is an array
     *
     * @param Response $response
     * @param string   $propertyPath e.g. firstName, battles[0].programmer.username
     */
    public function assertResponsePropertyIsArray(Response $response, string $propertyPath): void
    {
        $this->assertIsArray($this->readResponseProperty($response, $propertyPath));
    }

    /**
     * Asserts the given response property (probably an array) has the expected "count"
     *
     * @param Response $response
     * @param string   $propertyPath e.g. firstName, battles[0].programmer.username
     * @param integer  $expectedCount
     */
    public function assertResponsePropertyCount(Response $response, string $propertyPath, $expectedCount): void
    {
        $this->assertCount((int) $expectedCount, $this->readResponseProperty($response, $propertyPath));
    }

    /**
     * Asserts the specific response property contains the given value
     *
     * e.g. "Hello world!" contains "world"
     *
     * @param Response $response
     * @param string   $propertyPath e.g. firstName, battles[0].programmer.username
     * @param mixed    $expectedValue
     */
    public function assertResponsePropertyContains(Response $response, string $propertyPath, $expectedValue): void
    {
        $actualPropertyValue = $this->readResponseProperty($response, $propertyPath);
        $this->assertContains(
            $expectedValue,
            $actualPropertyValue,
            sprintf(
                'Property "%s": Expected to contain "%s" but response was "%s"',
                $propertyPath,
                $expectedValue,
                var_export($actualPropertyValue, true)
            )
        );
    }

    /**
     * Reads a JSON response property and returns the value
     *
     * This will explode if the value does not exist
     *
     * @param Response $response
     * @param string   $propertyPath e.g. firstName, battles[0].programmer.username
     *
     * @return mixed
     */
    public function readResponseProperty(Response $response, string $propertyPath): mixed
    {
        if ($this->accessor === null) {
            $this->accessor = PropertyAccess::createPropertyAccessor();
        }
        $data = json_decode((string) $response->getContent());
        if ($data === null) {
            throw new Exception(sprintf(
                'Cannot read property "%s" - the response is invalid (is it HTML?)',
                $propertyPath
            ));
        }
        try {
            return $this->accessor->getValue($data, $propertyPath);
        } catch (AccessException $e) {
            // it could be a stdClass or an array of stdClass
            $values = is_array($data) ? $data : get_object_vars($data);
            throw new AccessException(sprintf(
                'Error reading property "%s" from available keys (%s)',
                $propertyPath,
                implode(', ', array_keys($values))
            ), 0, $e);
        }
    }

    public function assertResponseStatusCode(
        Response $response,
        int $responseCode = Response::HTTP_OK
    ): void {
        $this->assertEquals(
            $responseCode,
            $response->getStatusCode(),
            sprintf('Response code %s (expected %s)', $response->getStatusCode(), $responseCode)
        );
    }

    public function assertErrorMessageResponse(
        Response $response,
        string $responseMessage = Messages::VALIDATION__COMMON__ERROR,
        int $responseCode = Response::HTTP_BAD_REQUEST,
        string $key = 'message'
    ): void {
        $this->assertEquals(
            $responseCode,
            $response->getStatusCode(),
            sprintf('Response code %s (expected %s)', $response->getStatusCode(), $responseCode)
        );
        $data = json_decode($response->getContent(), true);
        $this->arrayHasKey($data[$key], sprintf('Response has no key %s in data', $key));
        $this->assertEquals(
            $responseMessage,
            $data[$key],
            sprintf('Response message %s (expected %s)', $data[$key], $responseMessage)
        );
    }

    public function assertValidationErrorResponse(
        Response $response,
        ?array $assert,
        int $responseCode = Response::HTTP_BAD_REQUEST,
        string $key = 'errors'
    ): void {
        $this->assertEquals(
            $responseCode,
            $response->getStatusCode(),
            sprintf('Response code %s (expected %s)', $response->getStatusCode(), $responseCode)
        );
        $data = json_decode($response->getContent(), true);
        $this->arrayHasKey($data[$key], sprintf('Response has no key %s in data', $key));
        if (is_array($assert)) {
            $this->assertEquals(
                $assert,
                $data[$key],
                sprintf('Validation errors %s', json_encode($data[$key] ?? null))
            );
        }
    }
}
