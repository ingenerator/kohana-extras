<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */
namespace Ingenerator\KohanaExtras\Validation\Mocks;


class SpyingValidatorStub extends AlwaysOKValidatorStub
{
    /**
     * @var mixed[]
     */
    protected $data;

    public function validate($data)
    {
        $this->data = $data;

        return parent::validate($data);
    }

    public function assertValidated($data)
    {
        \PHPUnit_Framework_Assert::assertSame($data, $this->data);
    }


}
