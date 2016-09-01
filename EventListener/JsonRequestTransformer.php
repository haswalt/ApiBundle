<?php

namespace Haswalt\ApiBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class JsonRequestTransformer
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $content = $request->getContent();

        if (empty($content)) {
            return;
        }

        if (!$this->transformJsonRequest($request)) {
            $response = Response::create('Could not parse JSON');
            $event->setResponse($response);
        }
    }

    private function transformJsonRequest(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() != JSON_ERROR_NONE) {
            return false;
        }

        if ($data === null) {
            return true;
        }

        $request->request->replace($data);

        return true;
    }
}
