<?php

/*
 * This file is part of Slim Token Authentication Middleware
 *
 * Copyright (c) 2016-2018 Dyorg Washington G. Almeida
 *
 * Licensed under the MIT license
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace Dyorg\Middleware\TokenAuthentication;

use Dyorg\Middleware\TokenAuthentication\Exceptions\TokenNotFoundException;
use Psr\Http\Message\ServerRequestInterface;

class TokenSearch
{
    private $options = [];

    public function __construct($options = [])
    {
        $this->options = $options;
    }

    public function getToken(ServerRequestInterface &$request)
    {
        $token = $this->findToken($request);

        if (!empty($this->options['attribute'])) {
            $request = $request->withAttribute($this->options['attribute'], $token);
        }

        return $token;
    }

    private function findToken(ServerRequestInterface &$request)
    {
        /** Check for token on header */
        if (isset($this->options['header'])) {
            if ($request->hasHeader($this->options['header'])) {
                $header = $request->getHeader($this->options['header'])[0];
                if (preg_match($this->options['regex'], $header, $matches)) {
                    return $matches[1];
                }
            }
        }

        /** If nothing on header, try query parameters */
        if (isset($this->options['parameter'])) {
            if (!empty($request->getQueryParams()[$this->options['parameter']]))
                return $request->getQueryParams()[$this->options['parameter']];
        }

        /** If nothing on parameters, try cookies */
        if (isset($this->options['cookie'])) {
            $cookie_params = $request->getCookieParams();
            if (!empty($cookie_params[$this->options['cookie']])) {
                return $cookie_params[$this->options['cookie']];
            };
        }

        /** If nothing until now, check argument as last try */
        if (isset($this->options['argument'])) {
            if ($route = $request->getAttribute('route')) {
                $argument = $route->getArgument($this->options['argument']);
                if (!empty($argument)) {
                    return $argument;
                }
            }
        }

        throw new TokenNotFoundException('Authorization token not found');
    }
}