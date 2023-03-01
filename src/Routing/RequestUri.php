<?php

namespace Gaslawork\Routing;

use Psr\Http\Message\RequestInterface;

class RequestUri
{

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string[]|null
     */
    protected $exploded_url;

    public function __construct(RequestInterface $request)
    {
        $this->url = $request->getUri()->getPath();
    }

    /**
     * Get the URL string.
     *
     * @return string
     */
    public function get(): string
    {
        return $this->url;
    }

    /**
     * Get the URL exploded by "/".
     *
     * @return string[]
     */
    public function getExploded(): array
    {
        if ($this->exploded_url === null) {
            $this->exploded_url = explode(
                "/",
                strtr(
                    trim($this->url, "/\\"),
                    "\\",
                    "/"
                )
            );
        }

        return $this->exploded_url;
    }

}
