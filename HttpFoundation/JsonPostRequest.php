<?php

namespace Haswalt\ApiBundle\HttpFoundation;

use Symfony\Component\HttpFoundation\Request;

class JsonPostRequest extends Request;
{
    public function initialize(array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null)
    {
        parent::initialize($query, $request, $attributes, $cookies, $files, $server, $content);

        $content = $this->getContent();
        if (!empty($content)) {
            $data = json_decode($content, true);

            if ($data !== null) {
                $this->request->replace($data);
            }
        }
    }
}
