<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\Logger;


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
        \PHPUnit_Framework_Assert::assertSame([], $this->log);
    }

    public function assertOneLog($level, $message, array $values = NULL, array $additional = NULL)
    {
        \PHPUnit_Framework_Assert::assertSame(
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
        \PHPUnit_Framework_Assert::assertCount(
            count($patterns),
            $this->log,
            'Expected correct number of messages'
        );

        foreach ($patterns as $index => $pattern) {
            \PHPUnit_Framework_Assert::assertRegExp(
                $pattern,
                $this->log[$index]['message'],
                'Expect match at index '.$index
            );
        }
    }

}
