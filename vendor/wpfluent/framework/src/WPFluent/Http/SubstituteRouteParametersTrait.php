<?php

namespace FluentBooking\Framework\Http;

use Closure;
use ReflectionMethod;
use ReflectionFunction;
use FluentBooking\Framework\Container\Util;
use FluentBooking\Framework\Support\Reflector;
use FluentBooking\Framework\Support\UrlRoutable;

trait SubstituteRouteParametersTrait
{
    protected function SubstituteParameters($routeParameters)
    {
        $resolved = [];

        $signatureParameters = $this->filterSignatureParameters(
            $dependencies = $this->getParametersFromRouteAction()
        );

        if ($signatureParameters) {

            $parametersInfo = [];

            foreach ($dependencies as $dependency) {
                $parametersInfo[$dependency->getName()] = $dependency;
            }

            foreach ($signatureParameters as $signatureParameter) {
                
                if (array_key_exists($name = $signatureParameter->getName(), $routeParameters)) {
                    
                    $class = Util::getParameterClassName($parametersInfo[$name]);

                    $resolved[$name] = $this->app->make($class)->findOrFail(
                        $routeParameters[$name]
                    );

                    unset($routeParameters[$name]);
                }
            }
        }
        
        $remainingParams = [];

        foreach (array_reverse($routeParameters) as $param) {
            $remainingParams[($dep = array_pop($dependencies))->getName()] = $param;
        }

        return $resolved + array_reverse($remainingParams);
    }

    protected function getParametersFromRouteAction()
    {
        if ($this->action instanceof Closure) {
            return (new ReflectionFunction($this->action))->getParameters();
        }

        list($class, $method) = explode('@', $this->action);

        return (new ReflectionMethod($class, $method))->getParameters();
    }

    protected function filterSignatureParameters($parameters)
    {
        return array_filter($parameters, function ($param) {
            return Reflector::isParameterSubclassOf(
                $param, UrlRoutable::class
            );
        });
    }
}
