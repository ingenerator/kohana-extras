<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\Logger;


use PHPUnit\Framework\Assert;

/**
 * @deprecated Application code that produces logs should be updated to use a PSR/Logger directly
 */
class SpyingLoggerStub extends \Log
{
    protected $log = [];

    /**
     * {@inheritdoc}
     */
    public function add($level, $message, array $values = NULL, array $additional = NULL)
    {
        $this->log[] = [
            'level'      => $level,
            'message'    => $message,
            'values'     => $values,
            'additional' => $additional,
        ];
    }

    public function assertNothingLogged()
    {
        Assert::assertSame([], $this->log);
    }

    public function assertOneLog($level, $message, array $values = NULL, array $additional = NULL)
    {
        Assert::assertSame(
            [
                [
                    'level'      => $level,
                    'message'    => $message,
                    'values'     => $values,
                    'additional' => $additional,
                ],
            ],
            $this->log
        );
    }

    public function assertLogsMatching(array $patterns)
    {
        Assert::assertCount(
            \count($patterns),
            $this->log,
            'Expected correct number of messages'
        );

        foreach ($patterns as $index => $pattern) {
            Assert::assertMatchesRegularExpression(
                $pattern,
                $this->log[$index]['message'],
                'Expect match at index '.$index
            );
        }
    }

}
