<?php


namespace Ingenerator\KohanaExtras\GarbageCollector;


class GarbageCollectionController extends \Controller
{
    const MSG_AUTH_FAILED   = 'URL not found or not authorized';
    const MSG_NO_COLLECTORS = 'No garbage collectors registered';
    private string $expect_token;

    /**
     * @var GarbageCollector[]
     */
    private array $collectors;

    public function __construct(string $expect_token, GarbageCollector ...$collectors)
    {
        parent::__construct();
        $this->expect_token = $expect_token;
        $this->collectors   = $collectors;
    }

    public function action_post()
    {
        $auth = $this->request->headers('Authorization');
        if ($auth !== 'Bearer '.$this->expect_token) {
            throw \HTTP_Exception::factory(404, self::MSG_AUTH_FAILED);
        }

        if (empty($this->collectors)) {
            throw \HTTP_Exception::factory(501, self::MSG_NO_COLLECTORS);
        }

        foreach ($this->collectors as $collector) {
            $collector->execute();
        }

        $this->response->body('GC OK');
    }

}
