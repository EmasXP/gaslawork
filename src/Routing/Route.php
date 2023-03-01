<?php

namespace Gaslawork\Routing;

use Gaslawork\Exception\UndefinedRouteHandlerParameterException;
use Psr\Http\Message\RequestInterface;

class Route implements RouteInterface
{

    /**
     * @var string
     */
    protected $route;

    /**
     * @var string&class-string
     */
    protected $controller;

    /**
     * @var string|null
     */
    protected $action_handler;

    /**
     * @var string[]|null
     */
    protected $exploded_route;

    /**
     * @var array<string,string>
     */
    protected $defaults = [];

    /**
     * @var string[]
     */
    protected $required;

    /**
     * @var array<string,string[]>|null
     */
    protected $whitelist;

    /**
     * @var array<string,string[]>|null
     */
    protected $blacklist;

    /**
     * @var null|((string|string[])[])
     */
    protected $compiled_action_handler;

    /**
     * @param string $route
     * @param class-string $controller
     * @param null|string $action_handler
     * @return void
     */
    public function __construct(
        string $route,
        string $controller,
        ?string $action_handler = null
    ) {
        $this->route = $route;
        $this->controller = $controller;
        $this->action_handler = $action_handler;
        $this->exploded_route = explode("/", trim($this->route, "/"));
        if ($this->action_handler) {
            $this->compiled_action_handler = $this->compileHandler($this->action_handler);
        }
    }

    /**
     * Set the defaults of the parameters.
     *
     * @param array<string,string> $defaults
     * @return $this
     */
    public function setDefaults(array $defaults)
    {
        $this->defaults = $defaults;
        return $this;
    }

    /**
     * Set the required parameters.
     *
     * @param string[] $required
     * @return $this
     */
    public function setRequired(array $required)
    {
        $this->required = $required;
        return $this;
    }

    /**
     * Set the white list.
     *
     * @param array<string,string[]> $whitelist
     * @return $this
     */
    public function setWhitelist(array $whitelist)
    {
        $this->whitelist = $whitelist;
        return $this;
    }

    /**
     * Set the black list.
     *
     * @param array<string,string[]> $blacklist
     * @return $this
     */
    public function setBlacklist(array $blacklist)
    {
        $this->blacklist = $blacklist;
        return $this;
    }

    public function getMinimumParts(): int
    {
        $min = 0;

        foreach ($this->exploded_route as $i => $piece) {
            if (
                strlen($piece) > 0
                && $piece[0] == ":"
            ) {
                $param_name = substr($piece, 1);

                if (
                    $this->required !== null
                    && in_array($param_name, $this->required)
                    && (
                        $this->defaults === null
                        || !isset($this->defaults[$param_name])
                    )
                ) {
                    $min = $i + 1;
                }
            } else {
                $min = $i + 1;
            }
        }

        if ($min == 0) {
            return 1;
        }

        return $min;
    }

    public function getMaximumParts(): int
    {
        return count($this->exploded_route);
    }

    /**
     * Check if the route matches the passed URL.
     *
     * @param RequestUri $url
     * @param RequestInterface $request
     * @return null|RouteDataInterface
     */
    public function check(
        RequestUri $url,
        RequestInterface $request
    ): ?RouteDataInterface{
        $exploded = $this->exploded_route;
        $url_exploded = $url->getExploded();

        $params = [];

        foreach ($exploded as $i => $piece) {
            if (
                strlen($piece) > 0
                && $piece[0] == ":"
            ) {
                $param_name = substr($piece, 1);
                $param_value = null;

                if (
                    isset($url_exploded[$i])
                    && !empty($url_exploded[$i])
                ) {
                    $param_value = $url_exploded[$i];
                } elseif (
                    $this->defaults !== null
                    && isset($this->defaults[$param_name])
                ) {
                    $param_value = $this->defaults[$param_name];
                } elseif (
                    $this->required !== null
                    && in_array($param_name, $this->required)
                ) {
                    return null;
                }

                if (
                    $this->whitelist !== null
                    && isset($this->whitelist[$param_name])
                    && !in_array($param_value, $this->whitelist[$param_name])
                ) {
                    return null;
                } elseif (
                    $this->blacklist !== null
                    && isset($this->blacklist[$param_name])
                    && in_array($param_value, $this->blacklist[$param_name])
                ) {
                    return null;
                }

                $params[$param_name] = $param_value;
            } elseif (
                !isset($url_exploded[$i])
                || $url_exploded[$i] != $piece
            ) {
                return null;
            }
        }

        $params = $params + $this->defaults;

        return new RouteData(
            $this->controller,
            (
                $this->action_handler === null
                ? null
                : $this->parseHandler($this->compiled_action_handler, $params)
            ),
            $params
        );
    }

    /**
     * @param string $handler
     * @return (string|string[])[]
     */
    protected function compileHandler(string $handler): array
    {
        /*
        You might look at this code and think "well, this is an odd implementation" - and you are
        quite right, but it's the fastest one.

        It first splits the handler by {, and then again by }. Let's take an example:
        \Controller\{+directory}\{+controller}

        The first indent indent in this list is the $parts, and the second indent is the $parts'
        $subparts:
        "\Controller\"
        "\Controller\"
        "+directory}\"
        "+directory"
        "\"
        "+controller}"
        "+controller"
        ""
         */

        $parts = explode("{", $handler);

        $number_of_parts = count($parts);

        if ($number_of_parts == 1) {
            return [$parts[0]];
        }

        $out = [$parts[0]];

        for ($i = 1; $i < $number_of_parts; $i++) {
            $subparts = explode("}", $parts[$i]);

            if (count($subparts) == 1) {
                $out[] = $parts[$i];
                continue;
            }

            if ($subparts[0][0] == "+") {
                $out[] = ["+", substr($subparts[0], 1)];
            } else {
                $out[] = [null, $subparts[0]];
            }

            $out[] = $subparts[1];
        }

        return $out;
    }

    /**
     * @param (string|string[])[] $handler
     * @param array<string,string> $params
     * @return null|string
     * @throws UndefinedRouteHandlerParameterException
     */
    protected function parseHandler(
        array $handler,
        array $params
    ): ?string{
        $getParamForHandler = function (string $name) use ($params) {
            if (isset($params[$name])) {
                if (empty($params[$name])) {
                    throw new UndefinedRouteHandlerParameterException(
                        "The parameter $name is needed by the route's handler but is undefined or empty."
                    );
                }

                return $params[$name];
            }

            throw new UndefinedRouteHandlerParameterException(
                "The parameter $name is needed by the route's handler but is undefined or empty."
            );
        };

        $out = $handler[0];

        $number_of_parts = count($handler);

        for ($i = 1; $i < $number_of_parts; $i++) {
            $part = $handler[$i];

            if (!is_array($part)) {
                $out .= $part;
                continue;
            }

            if ($part[0] == "+") {
                $out .= ucfirst($getParamForHandler($part[1]));
            } else {
                $out .= $getParamForHandler($part[1]);
            }
        }

        return $out;
    }

}
