<?php


namespace Ingenerator\KohanaExtras\Routing;


/**
 * Provides a mechanism to reverse route controllers and params back to URLs
 *
 * @package Ingenerator\KohanaExtras\Routing
 */
interface UrlReverseRouter
{

    /**
     * Generate a URL to a controller/parameters set
     *
     * Parameters may include URLIdentifiableEntity classes, which will be dynamically converted
     * back to the related url string. For example:
     *
     *   // $booking->getUrlId() returns a string id for the content
     *   ->getUrl(ViewBookingController::class, ['id' => $booking]);
     *
     *   // $article->getUrlId() returns a string unique slug for the content
     *   ->getUrl(ViewArticleController::class, ['slug' => $article]);
     *
     * @param string $controller_class The FQCN of the controller to route back to
     * @param array  $params           Parameters to include in the route
     * @param array  $query            Any optional querystring args to encode and append
     *
     * @return string the URL
     */
    public function getUrl(string $controller_class, array $params = [], array $query = []): string;

}
