<?php

declare(strict_types=1);

namespace MyForksFiles\T3inquisitor\Tests\Unit\Domain\Model;

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case
 *
 * @author myForksFiles <myForksFiles(at)github.com>
 */
class LogTest extends UnitTestCase
{
    /**
     * @var \MyForksFiles\T3inquisitor\Domain\Model\Log|MockObject|AccessibleObjectInterface
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->getAccessibleMock(
            \MyForksFiles\T3inquisitor\Domain\Model\Log::class,
            ['dummy']
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getDataReturnsInitialValueForString(): void
    {
        self::assertSame(
            '',
            $this->subject->getData()
        );
    }

    /**
     * @test
     */
    public function setDataForStringSetsData(): void
    {
        $this->subject->setData('Conceived at T3CON10');

        self::assertEquals('Conceived at T3CON10', $this->subject->_get('data'));
    }
}
