<?php


namespace Dot\ResponseHeader\Middleware;


use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ResponseHeaderMiddleware implements MiddlewareInterface
{
    private const ALL_ROUTES = '*';

    /** @var array $config */
    private array $config;

    /**
     * ResponseHeaderMiddleware constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if (empty($this->config)) {
            return $response;
        }

        $response = $this->addHeaders($response, self::ALL_ROUTES);
        $routeResult = $request->getAttribute(RouteResult::class);
        if ($routeResult instanceof RouteResult && $routeResult->isSuccess()) {
            $response = $this->addHeaders($response, $routeResult->getMatchedRouteName());
        }
        return $response;
    }

    /**
     * @param ResponseInterface $response
     * @param string $route
     * @return ResponseInterface
     */
    private function addHeaders(ResponseInterface $response, string $route): ResponseInterface
    {
        if (array_key_exists($route, $this->config)) {
            foreach ($this->config[$route] as $header => $data) {
                if (! array_key_exists('value', $data)) {
                    continue;
                }
                $overwrite = (isset($data['overwrite']) && $data['overwrite'] === true) ? true : false;
                if ($response->hasHeader($header) && $overwrite) {
                    $response = $response->withHeader($header, $data['value']);
                } else {
                    $response = $response->withAddedHeader($header, $data['value']);
                }
            }
        }
        return $response;
    }
}