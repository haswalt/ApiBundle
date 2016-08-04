<?php

namespace Haswalt\ApiBundle\Controller;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Form;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Validator\ConstraintViolationList;
use JMS\Serializer\SerializationContext;

/**
 * Description of ApiController
 *
 * @author Harry Walter <harry.walter@lqdinternet.com>
 */
abstract class ApiController extends Controller
{
    /**
     * Creates an empty response with Location header
     *
     * @param  string  $route
     * @param  integer  $id
     * @param  integer $statusCode
     * @return Response
     */
    public function locationResponse($route, $params = [], $statusCode = 201)
    {
        $url = $this->generateUrl($route, $params, UrlGeneratorInterface::ABSOLUTE_URL);

        $response = new Response(null, $statusCode);
        $response->headers->set('Location', $url);

        return $response;
    }

    /**
     * Return a new Response containing rendered JSON
     *
     * @param  mixed  $data
     * @param  integer $statusCode
     * @return Response
     */
    public function jsonResponse($data, $statusCode = 200, $groups = ['default'])
    {
        $serializer = $this->get('jms_serializer');
        $context = new SerializationContext();
        $context->setGroups($groups);

        $json = $serializer->serialize($data, 'json', $context);

        return new Response(
            $json,
            $statusCode,
            array(
                'Content-Type' => 'application/json'
            )
        );
    }

    /**
     * Render validation errors into a JSON Response
     *
     * @param  ConstraintViolationList $violations
     * @return Response
     */
    public function errorResponse(ConstraintViolationList $violations)
    {
        $errors = array();

        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()] = $violation->getMessage();
        }

        return $this->jsonResponse($errors, 400);
    }

    /**
     * Return a response containing form errors
     *
     * @param  Form   $form
     * @return Response
     */
    public function formErrorResponse(Form $form)
    {
        $errors = $this->extractErrors($form);

        return $this->jsonResponse($errors, 400);
    }

    /**
     * Extract errors into 1 dimension array
     *
     * @param  Form   $form
     * @param  string $name
     * @return array
     */
    private function extractErrors(Form $form, $name = '')
    {
        $errors = array();

        $name .= ".".$form->getName();

        foreach ($form->getErrors() as $key => $error) {
            $errors[$name] = $error->getMessage();
        }

        foreach ($form->all() as $child) {
            if ($child instanceof Form) {
                $err = $this->extractErrors($child, $form->getName());
                if (count($err) > 0) {
                    $errors = array_merge($errors, $err);
                }
            }
        }

        return $errors;
    }
}
