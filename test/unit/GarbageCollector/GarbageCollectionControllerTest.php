<?php


namespace test\unit\KohanaExtras\GarbageCollector;


use Ingenerator\KohanaExtras\GarbageCollector\GarbageCollectionController;
use Ingenerator\KohanaExtras\GarbageCollector\GarbageCollector;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class GarbageCollectionControllerTest extends TestCase
{
    private string    $expect_token = 'sesame';
    private \Request  $request;
    private \Response $response;
    private array     $collectors;

    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf(GarbageCollectionController::class, $this->newSubject());
    }

    /**
     * @testWith [[]]
     *           [{"Authorization": "Bearer badguy"}]
     */
    public function test_it_throws_404_if_token_not_provided_or_does_not_match_config($headers)
    {
        $this->request = $this->stubRequestWithHeaders($headers);
        $subject       = $this->newSubject();
        $this->expectException(\HTTP_Exception_404::class);
        $this->expectExceptionMessage(GarbageCollectionController::MSG_AUTH_FAILED);
        $subject->execute();
    }

    public function test_it_throws_501_if_no_collectors()
    {
        $this->collectors = [];
        $subject          = $this->newSubject();
        $this->expectException(\HTTP_Exception_501::class);
        $this->expectExceptionMessage(GarbageCollectionController::MSG_NO_COLLECTORS);
        $subject->execute();
    }

    public function provider_garbage_collectors()
    {
        return [
            [
                // Single collector
                new GarbageCollectorStub('single'),
            ],
            [
                // Multiple collectors all run
                new GarbageCollectorStub('a'),
                new GarbageCollectorStub('b'),
                new GarbageCollectorStub('c'),
                new GarbageCollectorStub('d'),
            ],
        ];
    }

    /**
     * @dataProvider provider_garbage_collectors
     */
    public function test_it_executes_all_configured_garbage_collectors_and_returns_200_on_success(
        GarbageCollectorStub ...$collectors
    ) {
        $this->collectors = $collectors;
        $subject          = $this->newSubject();
        $subject->execute();

        foreach ($collectors as $collector) {
            $collector->assertExecuted();
        }

        $this->assertSame(200, $this->response->status());
        $this->assertSame('GC OK', $this->response->body());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->request    = $this->stubRequestWithHeaders(['Authorization' => 'Bearer '.$this->expect_token]);
        $this->response   = new \Response;
        $this->collectors = [new GarbageCollectorStub];
    }

    private function newSubject(): GarbageCollectionController
    {
        $controller = new GarbageCollectionController($this->expect_token, ...$this->collectors);
        $controller->setRequestContext($this->request, $this->response);

        return $controller;
    }

    /**
     * @param $headers
     *
     * @return \Request
     */
    protected function stubRequestWithHeaders($headers): \Request
    {
        return \Request::with(
            [
                'action' => 'post',
                'header' => new \HTTP_Header($headers),
                'method' => \Request::POST,
            ]
        );
    }

}

class GarbageCollectorStub implements GarbageCollector
{
    private bool   $has_executed = FALSE;
    private string $id;

    public function __construct(string $id = 'anon')
    {
        $this->id = $id;
    }

    public function execute(): void
    {
        $this->has_executed = TRUE;
    }

    public function assertExecuted(): void
    {
        Assert::assertTrue($this->has_executed, 'Collector '.$this->id.' should have run');
    }

}
